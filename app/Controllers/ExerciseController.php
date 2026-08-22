<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\Exercise;
use App\Models\ExerciseAttachment;
use App\Services\ExerciseAttachmentService;
use App\Services\ExerciseDuplicateConfirmationService;
use App\Services\ExerciseValidationService;

/** Handles exercise records, validation, and attachments. */
final class ExerciseController extends Controller
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Exercise $exercises,
        private readonly ExerciseAttachment $attachments,
        private readonly ExerciseValidationService $validator,
        private readonly ExerciseDuplicateConfirmationService $duplicates,
        private readonly ExerciseAttachmentService $files,
    ) {
    }
    public function index(): void
    {
        $user = $this->auth->requireLogin();
        $filters = [
            "search" => trim((string) ($_GET["search"] ?? "")),
            "activity" => trim((string) ($_GET["activity"] ?? "")),
            "sort" => (string) ($_GET["sort"] ?? "newest"),
        ];
        if (!isset(ExerciseValidationService::SORTS[$filters["sort"]])) {
            $filters["sort"] = "newest";
        }
        $result = $this->exercises->list($user, $filters);
        $this->view("exercise/index", [
            "pageTitle" => "Exercise Tracker",
            "result" => $result,
            "filters" => $filters,
            "analytics" => $this->exercises->analytics($user),
            "activities" => ExerciseValidationService::ACTIVITIES,
        ]);
    }
    public function createForm(): void
    {
        $this->auth->requireLogin();
        $this->view("exercise/form", [
            "pageTitle" => "Add Exercise",
            "record" => null,
            "errors" => [],
        ]);
    }
    public function store(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $validated = $this->validator->validate($_POST);
        if ($validated["errors"]) {
            $this->view("exercise/form", [
                "pageTitle" => "Add Exercise",
                "record" => $_POST,
                "errors" => $validated["errors"],
            ]);
            return;
        }
        $duplicate = $this->exercises->duplicate($user, $validated["data"]);
        if (
            $duplicate &&
            !$this->duplicates->confirm(
                $_POST["duplicate_token"] ?? null,
                $validated["data"],
            )
        ) {
            $this->view("exercise/form", [
                "pageTitle" => "Confirm duplicate",
                "record" => $_POST,
                "errors" => [
                    "A matching exercise already exists. Submit again to confirm this duplicate.",
                ],
                "duplicateToken" => $this->duplicates->issue(
                    $validated["data"],
                ),
            ]);
            return;
        }
        $id = $this->exercises->create($user, $validated["data"]);
        Flash::add("success", "Exercise record added.");
        $this->redirect("exercise/view?id=" . $id);
    }
    public function viewRecord(): void
    {
        $user = $this->auth->requireLogin();
        $record = $this->exercises->findOwned((int) ($_GET["id"] ?? 0), $user);
        if (!$record) {
            Flash::add("error", "That exercise record was not found.");
            $this->redirect("exercise");
        }
        $this->view("exercise/view", [
            "pageTitle" => "Exercise Details",
            "record" => $record,
            "attachment" => $this->attachments->findForExercise(
                (int) $record["exercise_id"],
                $user,
            ),
        ]);
    }
    public function editForm(): void
    {
        $user = $this->auth->requireLogin();
        $record = $this->exercises->findOwned((int) ($_GET["id"] ?? 0), $user);
        if (!$record) {
            Flash::add("error", "That exercise record was not found.");
            $this->redirect("exercise");
        }
        $this->view("exercise/form", [
            "pageTitle" => "Edit Exercise",
            "record" => $record,
            "errors" => [],
        ]);
    }
    public function update(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $id = (int) ($_POST["exercise_id"] ?? 0);
        if (!$this->exercises->findOwned($id, $user)) {
            Flash::add("error", "That exercise record was not found.");
            $this->redirect("exercise");
        }
        $validated = $this->validator->validate($_POST);
        if ($validated["errors"]) {
            $record = $_POST;
            $record["exercise_id"] = $id;
            $this->view("exercise/form", [
                "pageTitle" => "Edit Exercise",
                "record" => $record,
                "errors" => $validated["errors"],
            ]);
            return;
        }
        $duplicate = $this->exercises->duplicate(
            $user,
            $validated["data"],
            $id,
        );
        if (
            $duplicate &&
            !$this->duplicates->confirm(
                $_POST["duplicate_token"] ?? null,
                $validated["data"] + ["id" => $id],
            )
        ) {
            $record = $_POST;
            $record["exercise_id"] = $id;
            $this->view("exercise/form", [
                "pageTitle" => "Confirm duplicate",
                "record" => $record,
                "errors" => [
                    "A matching exercise already exists. Submit again to confirm this duplicate.",
                ],
                "duplicateToken" => $this->duplicates->issue(
                    $validated["data"] + ["id" => $id],
                ),
            ]);
            return;
        }
        $this->exercises->update($id, $user, $validated["data"]);
        Flash::add("success", "Exercise updated.");
        $this->redirect("exercise/view?id=" . $id);
    }
    public function delete(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $id = (int) ($_POST["exercise_id"] ?? 0);
        $attachment = $this->attachments->findForExercise($id, $user);
        if ($this->exercises->delete($id, $user)) {
            if ($attachment) {
                $this->files->remove($attachment["stored_name"]);
            }
            Flash::add("success", "Exercise record deleted.");
        } else {
            Flash::add("error", "Exercise record not found.");
        }
        $this->redirect("exercise");
    }
    public function upload(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $id = (int) ($_POST["exercise_id"] ?? 0);
        if (!$this->exercises->findOwned($id, $user)) {
            Flash::add("error", "Exercise record not found.");
            $this->redirect("exercise");
        }
        try {
            $file = $this->files->upload($_FILES["evidence"] ?? []);
            if ($file === null) {
                throw new \RuntimeException("Choose evidence to upload.");
            }
            $old = $this->attachments->save($id, $user, $file);
            if ($old) {
                $this->files->remove($old["stored_name"]);
            }
            Flash::add("success", "Evidence uploaded.");
        } catch (\RuntimeException $e) {
            Flash::add("error", $e->getMessage());
        }
        $this->redirect("exercise/view?id=" . $id);
    }
    public function deleteAttachment(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $attachment = $this->attachments->delete(
            (int) ($_POST["attachment_id"] ?? 0),
            $user,
        );
        if ($attachment) {
            $this->files->remove($attachment["stored_name"]);
            Flash::add("success", "Evidence removed.");
            $this->redirect(
                "exercise/view?id=" . (int) $attachment["exercise_id"],
            );
        }
        Flash::add("error", "Evidence not found.");
        $this->redirect("exercise");
    }
    public function export(): void
    {
        $user = $this->auth->requireLogin();
        $filters = [
            "search" => trim((string) ($_GET["search"] ?? "")),
            "activity" => trim((string) ($_GET["activity"] ?? "")),
            "sort" => (string) ($_GET["sort"] ?? "newest"),
        ];
        header("Content-Type: text/csv; charset=UTF-8");
        header(
            'Content-Disposition: attachment; filename="exercise-records.csv"',
        );
        $out = fopen("php://output", "w");
        fputcsv($out, [
            "Activity",
            "Duration (minutes)",
            "Calories",
            "Date",
            "Notes",
        ]);
        foreach ($this->exercises->export($user, $filters) as $row) {
            $safe = static fn ($v) => is_string($v) &&
            $v !== "" &&
            in_array($v[0], ["=", "+", "-", "@"], true)
                ? "'" . $v
                : $v;
            fputcsv($out, [
                $safe($row["activity_type"]),
                $row["duration_minutes"],
                $row["calories_burned"],
                $row["exercise_date"],
                $safe($row["notes"] ?? ""),
            ]);
        }
        fclose($out);
        exit();
    }
}
