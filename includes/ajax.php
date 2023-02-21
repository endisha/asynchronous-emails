<?php
/**
 * This class handles the AJAX requests for the plugin.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

class AsynchronousEmailsAjax
{
    /**
     * Load requests
     * 
     * This method adds the necessary action hooks to listen for AJAX requests.
     * 
     * @return void
     */
    public function load_requests()
    {
        add_action( 'wp_ajax_asynchronous_emails_ajax_list', [$this, 'list']);
        add_action( 'wp_ajax_asynchronous_emails_ajax_delete_task_record', [$this, 'delete_task_record']);
        add_action( 'wp_ajax_asynchronous_emails_ajax_resend_task_record', [$this, 'resend_task_record']);
        add_action( 'wp_ajax_asynchronous_emails_ajax_get_settings', [$this, 'get_settings']);
        add_action( 'wp_ajax_asynchronous_emails_ajax_update_settings', [$this, 'update_settings']);
    }

    /**
     * Retrieve and return the records from the queue.
     * 
     * @return json format
     */
    public function list()
    {
        if ( !AsynchronousEmailsPluginHelper::verify_nonce($_POST['nonce']?? '') ) {
            wp_send_json_error([ 
                'missing_nonce' => true, 
                'message' => __('Token mismatch, please refresh the page', 'asynchronous-emails') 
            ]);
        }

        $model = new AsynchronousEmailsQueueModel;
        $records = $model->get_all_paginate(20, $_POST['filters'] ?? []);
        
        array_map( function($record) { 
                $record->localized_status = AsynchronousEmailsPluginHelper::localized_queue_status($record->status);
            return $record;
        }, $records['records']?? []);

        $records['active'] = AsynchronousEmailsPluginHelper::get_option('active') == 1;

        wp_send_json_success($records);
    }

    /**
     * Delete task record.
     * 
     * @return json format
     */
    public function delete_task_record()
    {
        if ( !AsynchronousEmailsPluginHelper::verify_nonce($_POST['nonce']?? '') ) {
            wp_send_json_error([ 
                'missing_nonce' => true, 
                'message' => __('Token mismatch, please refresh the page', 'asynchronous-emails') 
            ]);
        }

        $id = intval($_POST['id'] ?? 0);

        $model = new AsynchronousEmailsQueueModel;
        $record = $model->first($id);
        if( !is_null($record) ){
            $deleted = $model->delete(['id' => $id]);
            if ($deleted) {
                wp_send_json_success([
                    'message' => __('The record deleted successfully', 'asynchronous-emails'),
                ]);
            }
        }

        wp_send_json_error([ 
            'message' => __('The record cannot be deleted', 'asynchronous-emails') 
        ]);
    }

    /**
     * Resend task record.
     * 
     * @return json format
     */
    public function resend_task_record()
    {
        if ( !AsynchronousEmailsPluginHelper::verify_nonce($_POST['nonce']?? '') ) {
            wp_send_json_error([ 
                'missing_nonce' => true, 
                'message' => __('Token mismatch, please refresh the page', 'asynchronous-emails') 
            ]);
        }

        $id = intval($_POST['id'] ?? 0);

        $model = new AsynchronousEmailsQueueModel;
        $record = $model->first($id);
        if( !is_null($record) ){
            if($record->status == 'cancelled'){
                $resend = $model->new_task($id);
                if( $resend ){
                    wp_send_json_success([ 
                        'message' => __('The record marked as new to resend again', 'asynchronous-emails') 
                    ]);
                }
            }
        }

        wp_send_json_error([ 
            'message' => __('A record cannot be marked as new', 'asynchronous-emails') 
        ]);
    }
    /**
     * Get settings.
     * 
     * @return json format
     */
    public function get_settings()
    {
        if ( !AsynchronousEmailsPluginHelper::verify_nonce($_POST['nonce']?? '') ) {
            wp_send_json_error([ 
                'missing_nonce' => true, 
                'message' => __('Token mismatch, please refresh the page', 'asynchronous-emails') 
            ]);
        }

        $settings = AsynchronousEmailsPluginHelper::get_option();
        
        wp_send_json_success(['data' => $settings]);
    }

    /**
     * Update settings
     * 
     * @return json format
     */
    public function update_settings()
    {

        if ( !AsynchronousEmailsPluginHelper::verify_nonce($_POST['nonce'] ?? '') ) {
            wp_send_json_error([ 
                'missing_nonce' => true, 
                'message' => __('Token mismatch, please refresh the page', 'asynchronous-emails') 
            ]);
        }

        $active = intval($_POST['settings']['active'] ?? 1);
        $max_attempts = intval($_POST['settings']['max_attempts'] ?? 3);
        $max_queue_record_age = intval($_POST['settings']['max_queue_record_age'] ?? 7);

        if( !is_numeric($max_attempts) ||  $max_attempts <= 0){
            wp_send_json_error([ 
                'message' => __('Maximum attempts must be equal to or greater than 1', 'asynchronous-emails') 
            ]);
        }

        // Periods
        $periods = apply_filters( 'asynchronous_emails_max_queue_record_periods', [1, 2, 7, 15, 30] );

        if( !is_numeric($max_queue_record_age) || !in_array($max_queue_record_age, $periods) ){
            wp_send_json_error([ 
                'message' => __('The maximum age of the queue record must be in a valid period', 'asynchronous-emails') 
            ]);
        }

        AsynchronousEmailsPluginHelper::update_option([
            'active' => $active,
            'max_attempts' => $max_attempts,
            'max_queue_record_age' => $max_queue_record_age
        ]);

        $settings = AsynchronousEmailsPluginHelper::get_option();

        wp_send_json_success([
            'message' => __('Settings updated successfully', 'asynchronous-emails'),
            'data' => $settings
        ]);

    }

}