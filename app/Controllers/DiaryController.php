<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\DiaryEntry;
use App\Services\DiaryUploadService;

/** Handles diary entry pages and attachments. */
final class DiaryController extends Controller
{
    private const MOODS = [
        "Happy",
        "Sad",
        "Excited",
        "Anxious",
        "Calm",
        "Angry",
        "Grateful",
        "Tired",
        "Stressed",
        "Neutral",
    ];
    public function __construct(
        private readonly Auth $auth,
        private readonly DiaryEntry $entries,
        private readonly DiaryUploadService $uploads,
    ) {
    }
    public function index(): void
    {
        $user = $this->auth->requireLogin();
        $f = [
            "search" => trim((string) ($_GET["search"] ?? "")),
            "mood" => (string) ($_GET["mood"] ?? ""),
            "favorite" => ($_GET["favorite"] ?? "") === "1",
            "sort" => (string) ($_GET["sort"] ?? "newest"),
        ];
        if (!in_array($f["mood"], self::MOODS, true)) {
            $f["mood"] = "";
        }
        if (!in_array($f["sort"], ["newest", "oldest", "title"], true)) {
            $f["sort"] = "newest";
        }
        $this->view("diary/index", [
            "pageTitle" => "Diary Journal",
            "entries" => $this->entries->list($user, $f),
            "filters" => $f,
            "moods" => self::MOODS,
            "insights" => $this->entries->insights($user),
        ]);
    }
    public function createForm(): void
    {
        $this->auth->requireLogin();
        $this->view("diary/form", [
            "pageTitle" => "New Reflection",
            "entry" => null,
            "errors" => [],
            "moods" => self::MOODS,
        ]);
    }
    public function store(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $result = $this->validated($_POST);
        if ($result["errors"]) {
            $this->view("diary/form", [
                "pageTitle" => "New Reflection",
                "entry" => $_POST,
                "errors" => $result["errors"],
                "moods" => self::MOODS,
            ]);
            return;
        }
        try {
            $result["data"]["image_path"] = $this->uploads->upload(
                $_FILES["image"] ?? [],
            );
            $id = $this->entries->create($user, $result["data"]);
            Flash::add("success", "Reflection saved.");
            $this->redirect("diary/view?id=" . $id);
        } catch (\RuntimeException $e) {
            $this->view("diary/form", [
                "pageTitle" => "New Reflection",
                "entry" => $_POST,
                "errors" => [$e->getMessage()],
                "moods" => self::MOODS,
            ]);
        }
    }
    public function viewEntry(): void
    {
        $user = $this->auth->requireLogin();
        $entry = $this->entries->findOwned((int) ($_GET["id"] ?? 0), $user);
        if (!$entry) {
            Flash::add("error", "That reflection was not found.");
            $this->redirect("diary");
        }
        $this->view("diary/view", [
            "pageTitle" => $entry["title"],
            "entry" => $entry,
        ]);
    }
    public function editForm(): void
    {
        $user = $this->auth->requireLogin();
        $entry = $this->entries->findOwned((int) ($_GET["id"] ?? 0), $user);
        if (!$entry) {
            Flash::add("error", "That reflection was not found.");
            $this->redirect("diary");
        }
        $this->view("diary/form", [
            "pageTitle" => "Edit Reflection",
            "entry" => $entry,
            "errors" => [],
            "moods" => self::MOODS,
        ]);
    }
    public function update(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $id = (int) ($_POST["entry_id"] ?? 0);
        $old = $this->entries->findOwned($id, $user);
        if (!$old) {
            Flash::add("error", "That reflection was not found.");
            $this->redirect("diary");
        }
        $result = $this->validated($_POST);
        if ($result["errors"]) {
            $_POST["entry_id"] = $id;
            $this->view("diary/form", [
                "pageTitle" => "Edit Reflection",
                "entry" => $_POST,
                "errors" => $result["errors"],
                "moods" => self::MOODS,
            ]);
            return;
        }
        try {
            $new = $this->uploads->upload($_FILES["image"] ?? []);
            $remove = isset($_POST["remove_image"]);
            $result["data"]["image_path"] =
                $new ?: ($remove ? null : $old["image_path"]);
            $this->entries->update($id, $user, $result["data"]);
            if (($new || $remove) && $old["image_path"]) {
                $this->uploads->remove($old["image_path"]);
            }
            Flash::add("success", "Reflection updated.");
            $this->redirect("diary/view?id=" . $id);
        } catch (\RuntimeException $e) {
            $_POST["entry_id"] = $id;
            $this->view("diary/form", [
                "pageTitle" => "Edit Reflection",
                "entry" => $_POST,
                "errors" => [$e->getMessage()],
                "moods" => self::MOODS,
            ]);
        }
    }
    public function delete(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $user = $this->auth->requireLogin();
        $entry = $this->entries->delete((int) ($_POST["entry_id"] ?? 0), $user);
        if ($entry) {
            $this->uploads->remove($entry["image_path"]);
            Flash::add("success", "Reflection deleted.");
        } else {
            Flash::add("error", "Reflection not found.");
        }
        $this->redirect("diary");
    }
    public function calendar(): void
    {
        $user = $this->auth->requireLogin();
        $month = max(1, min(12, (int) ($_GET["month"] ?? date("n"))));
        $year = max(2020, min(2100, (int) ($_GET["year"] ?? date("Y"))));
        $first = new \DateTimeImmutable(sprintf("%04d-%02d-01", $year, $month));
        $byDate = [];
        foreach (
            $this->entries->calendar(
                $user,
                $first->format("Y-m-01"),
                $first->format("Y-m-t"),
            ) as $entry
        ) {
            $byDate[$entry["entry_date"]][] = $entry;
        }
        $this->view("diary/calendar", [
            "pageTitle" => "Journal Calendar",
            "first" => $first,
            "entries" => $byDate,
        ]);
    }
    private function validated(array $in): array
    {
        $d = [
            "title" => trim((string) ($in["title"] ?? "")),
            "content" => trim((string) ($in["content"] ?? "")),
            "mood" => (string) ($in["mood"] ?? ""),
            "mood_score" => (int) ($in["mood_score"] ?? 0),
            "is_favorite" => isset($in["is_favorite"]) ? 1 : 0,
            "entry_date" => (string) ($in["entry_date"] ?? ""),
        ];
        $e = [];
        $date = \DateTimeImmutable::createFromFormat(
            "!Y-m-d",
            $d["entry_date"],
        );
        if ($d["title"] === "" || mb_strlen($d["title"]) > 150) {
            $e[] = "Title is required and must be 150 characters or fewer.";
        }
        if ($d["content"] === "" || mb_strlen($d["content"]) > 10000) {
            $e[] =
                "Journal content is required and must be 10,000 characters or fewer.";
        }
        if (!in_array($d["mood"], self::MOODS, true)) {
            $e[] = "Select a valid mood.";
        }
        if ($d["mood_score"] < 1 || $d["mood_score"] > 10) {
            $e[] = "Mood score must be between 1 and 10.";
        }
        if (
            !$date ||
            $date->format("Y-m-d") !== $d["entry_date"] ||
            $date > new\DateTimeImmutable("today")
        ) {
            $e[] = "Choose a valid date that is not in the future.";
        }
        return ["data" => $d, "errors" => $e];
    }
}
