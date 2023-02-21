<?php
/**
 * This Adminarea class manages the admin area of the plugin.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

class AsynchronousEmailsAdminarea
{

    /**
     * Registers the menus and scripts for the admin area.
     * 
     * @return void
     */
    public function boot()
    {
        add_action('admin_menu', [$this, 'menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts'], 10);
    }

    /**
     * Enqueues the required scripts and styles for specific pages.
     * 
     * @param string $hook The current admin page hook.
     * @return void
     */
    public function enqueue_scripts($hook)
    {
        $resources = [
            'asynchronous-emails-queue' => [
                'vue-asynchronous-emails-queue' =>  ASYNCHRONOUS_EMAILS_ASSETS_URL . '/js/queue.js'
            ],
            'asynchronous-emails-settings' => [
                'vue-asynchronous-emails-settings' =>  ASYNCHRONOUS_EMAILS_ASSETS_URL . '/js/settings.js'
            ],
        ];

        $found = false;
        foreach(array_keys($resources) as $key){
            $found = strpos($hook, $key) !== false;
            if($found) break;
        }

        // Load the page and include the required scripts.
        if($found){
            // Load common vuejs
            $scripts = [
                'vuejs' => ASYNCHRONOUS_EMAILS_ASSETS_URL . '/js/vue.js',
                'vue-router' => ASYNCHRONOUS_EMAILS_ASSETS_URL . '/js/vue-router.js',
                'vue-pagination' => ASYNCHRONOUS_EMAILS_ASSETS_URL . '/js/pagination.js',
                'jquery-ui' => ASYNCHRONOUS_EMAILS_ASSETS_URL . '/css/jquery-ui.css',
                'asynchronous-emails-css' => ASYNCHRONOUS_EMAILS_ASSETS_URL . '/css/style.css',
            ];
            // Include resources
            if(!empty($resources[$key])){
                $scripts = array_merge($scripts, $resources[$key]);
            }
            // Register scripts.
            foreach($scripts as $handle => $source){
                $extension = substr(strrchr($source, "."), 1);
                if ($extension == 'js') {
                    wp_register_script ( $handle , $source );
                }elseif ($extension == 'css') {
                    wp_register_style ( $handle , $source );
                }
            }
            // Localizes a script, only if the script has already been added
            wp_localize_script ( 'vuejs', 'ASYNCHRONOUS_EMAILS_PARAMS', array(
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(ASYNCHRONOUS_EMAILS_NONCE),
                'messages' => [
                    'delete_message' => __( 'Are you sure you want to delete the record?', 'asynchronous-emails'),
                    'resend_message' => __( 'Are you sure you want to send the email again?', 'asynchronous-emails'),
                ],
                'statuses' => AsynchronousEmailsPluginHelper::get_statuses(),
            ) );
        }
    }

    /**
     * Admin menus
     * 
     * @return void
     */
    public function menus()
    {

        $title = __( 'Asynchronous emails', 'asynchronous-emails');
        $items = [
            'asynchronous-emails-queue' => [
                'title' => __( 'Queues', 'asynchronous-emails' ),
                'callback' => function(){
                    include ASYNCHRONOUS_EMAILS_RESOURCES_VIEWS_DIR . '/queue.php';
                },
            ],
            'asynchronous-emails-settings' => [
                'title' => __( 'Settings', 'asynchronous-emails' ),
                'callback' => function(){
                    include ASYNCHRONOUS_EMAILS_RESOURCES_VIEWS_DIR . '/settings.php';
                },
            ],
        ];
        add_menu_page($title, $title, 'activate_plugins', 'asynchronous-emails', '', 'dashicons-email-alt2', 53);
        foreach ($items as $key => $item) {
            add_submenu_page('asynchronous-emails', $item['title'], $item['title'], 'manage_options', $key, $item['callback']);
        }
        remove_submenu_page('asynchronous-emails', 'asynchronous-emails');

    }

}