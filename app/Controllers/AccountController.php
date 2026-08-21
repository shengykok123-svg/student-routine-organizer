<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\User;
use App\Models\UserSettings;
use App\Services\ProfileImageUploadService;

final class AccountController extends Controller
{
    public function __construct(private readonly Auth $auth, private readonly User $users, private readonly UserSettings $settings, private readonly ProfileImageUploadService $profileImages) {}

    public function profile(): void
    {
        $id = $this->auth->requireLogin();
        $this->view('account/profile', ['pageTitle' => 'Profile', 'user' => $this->users->findById($id), 'errors' => []]);
    }

    public function updateProfile(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $id = $this->auth->requireLogin();
        $existing = $this->users->findById($id);
        if (!$existing) { $this->auth->logout(); $this->redirect('login'); }

        $name = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $errors = $this->profileErrors($id, $name, $username, $email);
        if ($errors) {
            $this->renderProfileInput($existing, $name, $username, $email, $errors);
            return;
        }

        try {
            $newImage = $this->profileImages->upload($_FILES['profile_image'] ?? []);
            $removeImage = isset($_POST['remove_profile_image']);
            $imagePath = $newImage ?: ($removeImage ? null : $existing['profile_image_path']);
            $this->users->updateProfile($id, $name, $username, $email);
            if ($imagePath !== $existing['profile_image_path']) $this->users->updateProfileImage($id, $imagePath);
            if (($newImage !== null || $removeImage) && $existing['profile_image_path']) $this->profileImages->remove($existing['profile_image_path']);
            $this->auth->login($this->users->findById($id));
            Flash::add('success', 'Profile updated.');
            $this->redirect('profile');
        } catch (\RuntimeException $e) {
            $this->renderProfileInput($existing, $name, $username, $email, [$e->getMessage()]);
        }
    }

    public function profileImage(): void
    {
        $user = $this->users->findById($this->auth->requireLogin());
        $path = $user ? $this->profileImages->path($user['profile_image_path'] ?? null) : null;
        if ($path === null || !is_file($path)) { http_response_code(404); exit('Profile picture not found.'); }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) { http_response_code(404); exit('Profile picture not found.'); }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public function settings(): void
    {
        $id = $this->auth->requireLogin();
        $this->view('account/settings', ['pageTitle' => 'Settings', 'settings' => $this->settings->get($id), 'errors' => []]);
    }

    public function updateSettings(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $id = $this->auth->requireLogin();
        $current = (string) ($_POST['current_password'] ?? '');
        $password = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        $errors = [];
        if ($password !== '' && (strlen($password) < 6 || $password !== $confirm)) $errors[] = 'New password must be at least 6 characters and match confirmation.';
        if ($password !== '' && !password_verify($current, (string) $this->users->findById($id)['password'])) $errors[] = 'Current password is incorrect.';
        $time = (string) ($_POST['reminder_time'] ?? '');
        if ($time !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) $errors[] = 'Choose a valid reminder time.';
        if ($errors) { $this->view('account/settings', ['pageTitle' => 'Settings', 'settings' => ['in_app_notifications' => isset($_POST['in_app_notifications']), 'email_notifications' => isset($_POST['email_notifications']), 'reminder_time' => $time], 'errors' => $errors]); return; }
        $this->settings->update($id, isset($_POST['in_app_notifications']), isset($_POST['email_notifications']), $time ?: null);
        if ($password !== '') $this->users->updatePassword($id, $password);
        Flash::add('success', 'Settings updated.');
        $this->redirect('settings');
    }

    private function profileErrors(int $id, string $name, string $username, string $email): array
    {
        $errors = [];
        if ($name !== '' && mb_strlen($name) > 100) $errors[] = 'Full name must be 100 characters or fewer.';
        if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $username)) $errors[] = 'Username must be 4-30 letters, numbers, or underscores.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (!$errors && $this->users->usernameOrEmailExists($username, $email, $id)) $errors[] = 'That username or email is already registered.';
        return $errors;
    }

    private function renderProfileInput(array $existing, string $name, string $username, string $email, array $errors): void
    {
        $user = array_merge($existing, ['full_name' => $name, 'username' => $username, 'email' => $email]);
        $this->view('account/profile', ['pageTitle' => 'Profile', 'user' => $user, 'errors' => $errors]);
    }
}
