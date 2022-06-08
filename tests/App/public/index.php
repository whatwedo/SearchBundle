<?php

declare(strict_types=1);

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use whatwedo\SearchBundle\Tests\App\Kernel;

require dirname(__DIR__) . '/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}
$trustedProxies = $_SERVER['TRUSTED_PROXIES'];
if ($trustedProxies ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO ^ Request::HEADER_X_FORWARDED_HOST);
}

$trustedHosts = $_SERVER['TRUSTED_HOSTS'];
if ($trustedHosts ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
