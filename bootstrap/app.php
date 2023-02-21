<?php
/**
 * Application File
 * 
 * This file is executed first thing loaded into the global scope.
 * It defining the autoloader, registering migrations, and setting up plugin actions and filters.
 * 
 * @package
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

// Define autoloader
require_once ASYNCHRONOUS_EMAILS_BOOTSTRAP_DIR . '/autoload.php';

$app = new AsynchronousEmailsApplication;
$app->boot();