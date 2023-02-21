<?php
/**
 * Plugin Helper
 * This class provides various helper functions for the plugin.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

class AsynchronousEmailsPluginHelper
{
    
    /**
     * Generates a unique identifier for the email task.
     * 
     * @static
     * @return string Returns a unique identifier for the email task.
     */
    public static function createTaskMailUuid()
    {
        return wp_generate_uuid4();
    }

    /**
     * Loads the configuration file.
     * 
     * @static
     * @param string $config The name of the configuration file to be loaded.
     * @return array Returns an array containing the configuration data.
     */
    public static function config(string $config)
    {
        $file = ASYNCHRONOUS_EMAILS_CONFIGS_DIR . '/' . $config . '.php';
        if( file_exists($file) ){
            return require_once realpath($file);
        }
        return [];

    }

    /**
     * Retrieve a specific setting or all settings for the plugin.
     *
     * @static
     * @param string|null $key Optional. The name of the setting to retrieve. If not provided, all settings will be returned.
     * @return string|array The value of the requested setting or an array of all settings.
     */
    public static function get_option(string $key = null)
    {
        $settings = get_option('asynchronous_emails_settings', []);

        if (!is_null($key)) {
            return $settings[$key] ?? '';
        }

        return $settings;
    }

    /**
     * Update the settings.
     *
     * @static
     * @param array $data The data to be updated. This can be a single setting or an array of all settings.
     */
    public static function update_option(array $data = [])
    {
        update_option('asynchronous_emails_settings', $data);
    }

    /**
     * Verifies the nonce.
     * 
     * @static
     * @param string $nonce The nonce to be verified.
     * @return bool Returns `true` if the nonce is verified, `false` otherwise.
     */
    public static function verify_nonce(string $nonce)
    {
        return wp_verify_nonce( sanitize_text_field($nonce), ASYNCHRONOUS_EMAILS_NONCE );
    }

    /**
     * Localized queue status.
     * 
     * @static
     * @param string $status The status of queue.
     * @return string
     */
    public static function localized_queue_status(string $status)
    {
        $statuses = self::get_statuses();
        return array_key_exists($status, $statuses)? $statuses[$status] : $status;
    }

    /**
     * Get statuses
     * 
     * @static
     * @return array
     */
    public static function get_statuses()
    {
        return apply_filters( 'asynchronous_emails_queue_statuses', [
            'new' => __('New', 'asynchronous-emails'),
            'pending' => __('Pending', 'asynchronous-emails'),
            'processing' => __('Processing', 'asynchronous-emails'),
            'failed' => __('Failed', 'asynchronous-emails'),
            'cancelled' => __('Cancelled', 'asynchronous-emails'),
            'completed' => __('Completed', 'asynchronous-emails'),
        ]);
    }

}