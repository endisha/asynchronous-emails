<?php
/**
 * Queue file
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

$app_file = basename(__FILE__, '.php');

$scripts = ['jquery-ui-datepicker', 'vuejs', 'vue-router', 'vue-pagination', 'vue-asynchronous-emails-queue'];

$styles = ['jquery-ui', 'asynchronous-emails-css'];

$enqueue_media = true;

require realpath( __DIR__ . '/app.php' );