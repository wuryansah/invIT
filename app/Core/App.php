<?php

namespace App\Core;

use RuntimeException;

class App
{
    private static ?App $instance = null;
    public array $config;
    public Request $request;
    public Response $response;
    public Router $router;

    private function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/config.php';
    }

    public static function instance(): App
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function path(string $key): string
    {
        return $this->config['paths'][$key] ?? '';
    }

    public function boot(): void
    {
        date_default_timezone_set($this->config['app']['timezone']);

        // Register the PSR-4 style autoloader for App namespace.
        spl_autoload_register(function (string $class): void {
            if (str_starts_with($class, 'App\\')) {
                $relative = substr($class, 4);
                $file = $this->path('app') . '/' . str_replace('\\', '/', $relative) . '.php';
                if (is_file($file)) {
                    require_once $file;
                }
            }
        });

        $this->startSession();
        require_once $this->path('app') . '/Core/helpers.php';
        require_once $this->path('app') . '/Core/icons.php';
        $this->registerGlobalAliases();
    }

    /**
     * Views run in the global namespace, so expose framework classes under
     * short names (Auth, Database, Setting, ...) without polluting views
     * with imports.
     */
    private function registerGlobalAliases(): void
    {
        $aliases = [
            'App\\Core\\App'       => 'App',
            'App\\Core\\Auth'      => 'Auth',
            'App\\Core\\Csrf'      => 'Csrf',
            'App\\Core\\Database'  => 'Database',
            'App\\Core\\Excel'     => 'Excel',
            'App\\Core\\Request'   => 'Request',
            'App\\Core\\Response'  => 'Response',
            'App\\Core\\Router'    => 'Router',
            'App\\Core\\Validator' => 'Validator',
            'App\\Core\\View'      => 'View',
            'App\\Models\\Adjustment'   => 'Adjustment',
            'App\\Models\\Asset'        => 'Asset',
            'App\\Models\\Assignment'   => 'Assignment',
            'App\\Models\\AuditLog'     => 'AuditLog',
            'App\\Models\\Category'     => 'Category',
            'App\\Models\\Department'   => 'Department',
            'App\\Models\\Employee'     => 'Employee',
            'App\\Models\\Loan'         => 'Loan',
            'App\\Models\\Maintenance'  => 'Maintenance',
            'App\\Models\\Notification' => 'Notification',
            'App\\Models\\Setting'      => 'Setting',
            'App\\Models\\Transaction'  => 'Transaction',
            'App\\Models\\Transfer'     => 'Transfer',
            'App\\Models\\User'         => 'User',
        ];
        foreach ($aliases as $original => $alias) {
            if (!class_exists($alias, false)) {
                class_alias($original, $alias);
            }
        }
    }

    private function startSession(): void
    {
        $sess = $this->config['session'];
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name($sess['name']);
        session_set_cookie_params([
            'lifetime' => $sess['lifetime'],
            // Root-scope the session cookie. On Windows sub-directory installs the
            // on-disk casing of the base path (/invIT/public) never matches the
            // casing a user types in the URL (/invit/public); cookie paths are
            // matched case-sensitively (RFC 6265), so the cookie would be withheld
            // on POST and CSRF would fail. '/' matches every request.
            'path'     => '/',
            'secure'   => $sess['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function basePath(): string
    {
        // Prefer SCRIPT_NAME when the server reports the true script path
        // (direct requests to index.php, PHP built-in server).
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (basename($script) === 'index.php') {
            $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
            if ($dir !== '' && $dir !== '/') {
                return $dir;
            }
        }

        // Fallback: derive the mount point from the executed front controller
        // relative to the document root. Covers php-cgi + mod_rewrite setups
        // (e.g. Laragon) where SCRIPT_NAME collapses to bare '/index.php' even
        // though the app actually lives under a sub-path like /invIT/public.
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $scriptFile = $_SERVER['SCRIPT_FILENAME'] ?? '';
        if ($docRoot !== '' && $scriptFile !== '') {
            $root = str_replace('\\', '/', realpath($docRoot));
            $file = str_replace('\\', '/', realpath($scriptFile));
            if ($root && $file && str_starts_with($file, $root)) {
                $dir = rtrim(substr($file, strlen($root)), '/');
                return $dir === '' ? '' : $dir;
            }
        }

        return '';
    }

    public static function baseUrl(): string
    {
        $config = self::instance()->config;
        if (!empty($config['url']['base'])) {
            return '/'. trim($config['url']['base'], '/');
        }
        return self::basePath();
    }

    public function handle(): void
    {
        // Never let browsers serve stale HTML: the app can be mounted under a
        // sub-path (e.g. /invIT/public) and cached pages would keep old links.
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
        }

        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request);
        $router = $this->router;

        require $this->path('base') . '/routes/web.php';

        $this->router->dispatch();
    }
}