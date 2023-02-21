<?php
/**
 * Autoloader 
 * 
 * This file is responsible for including all necessary files for the plugin.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

$files = [];
$skip_load = ASYNCHRONOUS_EMAILS_SKIP_LOAD;
$directories = glob(ASYNCHRONOUS_EMAILS_DIR . '/*', GLOB_ONLYDIR);
foreach ($directories as $directory) {
    $dir_name = basename($directory);
    if (!in_array($dir_name, $skip_load)) {
        $dir_files = glob($directory . '/*.php');
        $files = array_merge($dir_files, $files);
    }
}

foreach ($files as $file) {
    if (file_exists($file)) {
        require_once realpath($file);
    }
}