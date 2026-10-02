<?php

namespace App\Controllers;

use App\Core\App;

/**
 * Shared controller helpers.
 */
abstract class BaseController
{
    protected function page(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        \App\Core\View::render($template, $data, $layout);
    }

    protected function uploadPhoto(?array $file, ?string $current = null): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $current;
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed[$mime])) {
            flash('error', 'Photo must be a JPG, PNG, GIF or WebP image.');
            return null;
        }

        $maxBytes = 4 * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            flash('error', 'Photo must be smaller than 4MB.');
            return null;
        }

        $name = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
        $dir = App::instance()->path('uploads') . '/photos';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $target = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            flash('error', 'Unable to store the uploaded photo.');
            return null;
        }

        if ($current && $current !== '') {
            $old = $dir . '/' . basename($current);
            if (is_file($old)) {
                @unlink($old);
            }
        }

        return 'uploads/photos/' . $name;
    }

    protected function redirectBackWithError(string $message): never
    {
        redirect_with('error', $message, $_SERVER['HTTP_REFERER'] ?? '/');
    }
}