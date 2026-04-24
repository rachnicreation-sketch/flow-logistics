<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): string
    {
        $viewFile = project_path('app/Views/' . $view . '.php');
        if (!is_file($viewFile)) {
            throw new \RuntimeException('Vue introuvable: ' . $view);
        }

        $flash = Flash::all();
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean() ?: '';

        if ($layout === 'empty') {
            return $content;
        }

        $layoutFile = project_path('app/Views/layouts/' . $layout . '.php');
        if (!is_file($layoutFile)) {
            throw new \RuntimeException('Layout introuvable: ' . $layout);
        }

        ob_start();
        require $layoutFile;
        return ob_get_clean() ?: '';
    }
}

