<?php
/**
 * Settings file
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit; 

$app_file = basename(__FILE__, '.php');

$scripts = ['vuejs', 'vue-router', 'vue-asynchronous-emails-settings'];

$styles = ['jquery-ui', 'asynchronous-emails-css'];

$enqueue_media = true;

require realpath( __DIR__ . '/app.php' );