<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
$_SERVER['KERNEL_CLASS'] = $_SERVER['KERNEL_CLASS'] ?? $_ENV['KERNEL_CLASS'] ?? \App\Kernel::class;
$_ENV['KERNEL_CLASS']    = $_SERVER['KERNEL_CLASS'];
