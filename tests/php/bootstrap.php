<?php # -*- coding: utf-8 -*-
$vendor = dirname(__DIR__, 2) . '/vendor/';
if (!file_exists($vendor . 'autoload.php')) {
    die("Please install via Composer before running tests.");
}

require_once $vendor . 'brain/monkey/inc/patchwork-loader.php';
require_once $vendor . 'autoload.php';
unset($vendor);

putenv('TESTS_PATH=' . __DIR__);
