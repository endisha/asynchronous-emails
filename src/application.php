<?php
/**
 * Class AsynchronousEmailsApplication
 *
 * This class is responsible for executing the plugin, setting up actions and filters, registering migrations, 
 * and handling email queue execution.
 *
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

class AsynchronousEmailsApplication {

    /**
     * Boot the plugin.
     *
     * Sets up migrations, actions, and filters, and handles email queue execution.
     *
     * @return void
     */
    public function boot()
    {

        // Register migrations
        register_activation_hook(ASYNCHRONOUS_EMAILS_FILE, [new AsynchronousEmailsMigrations, 'booting']);

        // Register the default data on plugin activation
        register_activation_hook(ASYNCHRONOUS_EMAILS_FILE, [$this, 'register_default_data']);

        // Unregister the jobs
        register_deactivation_hook(ASYNCHRONOUS_EMAILS_FILE, [$this, 'unregister_jobs']);

        // Load email queue execution
        $this->email_queue_execution();
        
        // Load actions and filters
        add_action( 'plugins_loaded', [$this, 'load_actions_filters']);

        // Load languages
        add_action( 'plugins_loaded', [$this, 'load_i18n']);

    }

    /**
     * Load actions and filters for the plugin.
     *
     * @return void
     */
    public function load_actions_filters()
    {
        ( new AsynchronousEmailsAjax )->load_requests();
        ( new AsynchronousEmailsAdminarea )->boot();
    }

    /**
     * Load internationalization (i18n) languages for the plugin.
     *
     * @return void
     */
    public function load_i18n()
    {
        load_plugin_textdomain('asynchronous-emails', false, ASYNCHRONOUS_EMAILS_LANGUAGES_DIR);
    }

    /**
     * Register default data for the plugin's settings.
     * 
     * Updates the 'asynchronous_emails_settings' option with default values.
     * 
     * @return void
     */
    public function register_default_data()
    {
        update_option('asynchronous_emails_settings', [
            'active' => 1,
            'max_attempts' => 3,
            'max_queue_record_age' => 7,
        ]); 
    }

    /**
     * Deregistration of jobs
     * 
     * @return void
     */
    public function unregister_jobs()
    {
        $job_hooks = apply_filters('asynchronous_emails_job_hooks', []);
        foreach ($job_hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Handle email queue execution for the plugin.
     *
     * @return void
     */
    public function email_queue_execution()
    {

        // Handle Email Queue Execution
        $jobs = new AsynchronousEmailsTaskExecutor;
        $jobs->setJobs(AsynchronousEmailsPluginHelper::config('jobs'));
        $jobs->get_job_hooks();

        if ( wp_doing_cron() ) {

            $jobs->execute();

            add_action( 'wp_mail_failed', function ( $wp_error ) {
                return ( new AsynchronousEmailsQueueModel )->failed_task_response($wp_error);
            }, 10, 1 );

        } else {
            if( AsynchronousEmailsPluginHelper::get_option('active') && ! function_exists('wp_mail') ){
                function wp_mail() {
                    return (new AsynchronousEmailsQueueModel)->to_queue(func_get_args());
                }
            } 
        }
    }
    
}