<?php

namespace App\Core;

class View
{
    /** Render a view using the default layout (auth pages use the auth layout). */
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/app'): never
    {
        $content = self::capture($template, $data);

        if ($layout === null) {
            echo $content;
            exit;
        }

        $pageTitle = $data['pageTitle'] ?? config('app.name');
        $pageModule = $data['pageModule'] ?? '';
        $currentUser = Auth::user();
        $flashMessages = flash_take();

        extract($data, EXTR_SKIP);
        $viewFile = config('paths.views') . '/' . $layout . '.php';
        if (!is_file($viewFile)) {
            app_response()->abort(500, "Layout [$layout] not found.");
        }
        require $viewFile;
        exit;
    }

    /** Render a view fragment without a layout. Useful for JSON/print pages. */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data);
    }

    private static function capture(string $template, array $data): string
    {
        $viewPath = config('paths.views') . '/' . $template . '.php';
        if (!is_file($viewPath)) {
            app_response()->abort(500, "View [$template] not found.");
        }
        ob_start();
        extract($data, EXTR_SKIP);
        require $viewPath;
        return ob_get_clean();
    }
}