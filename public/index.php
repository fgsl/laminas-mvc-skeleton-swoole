<?php
declare(strict_types = 1);

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;
use Fgsl\Swoole\SwooleHelper;

/**
 * This makes our life easier when dealing with paths.
 * Everything is relative
 * to the application root now.
 */
define('APP_ROOT', dirname(__DIR__));

chdir(APP_ROOT);

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (is_string($path) && __FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (! class_exists(Application::class)) {
    throw new RuntimeException("Unable to load application.\n" . "- Type `composer install` if you are developing locally.\n" . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n" . "- Type `docker-compose run laminas composer install` if you are using Docker.\n");
}

// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}
$swoolePort = $appConfig['swoole_port'] ?? '9501';

$http = new Swoole\HTTP\Server("0.0.0.0", $swoolePort);

$http->on('start', function ($server) use ($swoolePort) {
    echo "Laminas application on Swoole http server is started at http://127.0.0.1:$swoolePort\n";
});

$http->on('request', function ($request, $response) use ($appConfig) {
    $swooleHelper = new SwooleHelper($request, $response);
    if ($swooleHelper->requestIsFile()) {
        $content = file_get_contents(APP_ROOT . '/public/' . $swooleHelper->getFolder() . '/' .  $swooleHelper->getFile());
    } else {
        $app = Application::init($appConfig);
        // Run the application!
        $app->run();
        $content = $app->getResponse()
            ->getContent();
        $json = json_decode($content);
        if (json_last_error() == JSON_ERROR_NONE){
            $response->header("Content-Type", "application/json");
        }
    }
    $response->end($content);
});

$http->start();