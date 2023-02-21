<?php
/**
 * Plugin Name: Asynchronous Emails
 * Plugin URI: https://endisha.ly/
 * Description: Send Wordpress emails asynchronously.
 * Author: Mohamed Endisha
 * Author URI: https://endisha.ly
 * Version: 1.0.0
 * Text Domain: asynchronous-emails
 * Domain Path: /i18n/languages/
 * Requires at least: 5.9
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' )or exit;

// Define constants
if ( ! defined( 'ASYNCHRONOUS_EMAILS_DIR' ) ) {

    define( 'ASYNCHRONOUS_EMAILS_FILE', __FILE__ );
    define( 'ASYNCHRONOUS_EMAILS_DIR', __DIR__ );
    define( 'ASYNCHRONOUS_EMAILS_CONFIGS_DIR', __DIR__ . '/configs' );
    define( 'ASYNCHRONOUS_EMAILS_BOOTSTRAP_DIR', __DIR__ . '/bootstrap' );
    define( 'ASYNCHRONOUS_EMAILS_INCLUDES_DIR', __DIR__ . '/includes' );
    define( 'ASYNCHRONOUS_EMAILS_RESOURCES_VIEWS_DIR', __DIR__ . '/resources/views' );
    define( 'ASYNCHRONOUS_EMAILS_LANGUAGES_DIR', basename(__DIR__) . '/i18n/languages/' );
    define( 'ASYNCHRONOUS_EMAILS_SKIP_LOAD', ['bootstrap', 'configs'] );
    define( 'ASYNCHRONOUS_EMAILS_ASSETS_URL', plugin_dir_url(__FILE__) . 'resources/assets' );
    define( 'ASYNCHRONOUS_EMAILS_NONCE', 'asynchronous-emails' );

}

// Include the application file
include ASYNCHRONOUS_EMAILS_BOOTSTRAP_DIR . '/app.php';
