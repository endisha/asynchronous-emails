<?php
/**
 * Queue model
 * It handles the database interactions for the email queue.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

class AsynchronousEmailsQueueModel extends AsynchronousEmailsAbstractModel
{

    protected $table = 'asynchronous_email_queue';

    /**
     * Add an email to the queue
     * 
     * This method adds an email to the queue, with the given parameters. It creates a unique
     * process ID, and stores the email data, along with its status and other relevant information.
     * 
     * @param  array  $args     The arguments for sending the email, as expected by the `wp_mail` function
     * @param  string $status   The initial status of the email in the queue, defaults to `new`
     * @return boolean          Returns true if the email was successfully added to the queue, false otherwise
     */
    public function to_queue(array $args, string $status = 'new')
    {

        $processId = AsynchronousEmailsPluginHelper::createTaskMailUuid();
        $to = '';
        if(isset($args[0])){
            $to = is_array($args[0])? implode(',', $args[0]) : $args[0];
        }
        $subject = $args[1]?? '';
        if(isset($args[3])){
            $args[3] .= sprintf('Process-Id: %s', $processId);
        }

        $data = [
            'id' => null, 
            'process_id' => $processId, 
            'subject' => $subject,
            'to' => $to,
            'data' => !empty($args)? maybe_serialize($args) : '',
            'status' => $status,
            'response' => '',
            'attempts' => 0,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]; 

        return $this->insert($data);

    }

    /**
     * Retrieve pending emails from the queue
     * 
     * This method retrieves a specified number of emails from the queue, which have the status of `new` or `failed`.
     * 
     * @param  int $limit  The maximum number of emails to retrieve from the queue, defaults to 10
     * @return array       Returns an array of email data, or an empty array if no emails are found
     */
    public function from_queue($limit = 10)
    {
        $records = $this->db->get_results(
            $this->db->prepare(
                "
                    SELECT * FROM " . $this->table . "
                    WHERE `status` IN (%s, %s)
                    ORDER BY `id` ASC
                    LIMIT %d
                ", 'new', 'failed', $limit
            )
        );
        array_map( function($record) { 
            if(!empty($record)){
                $record->id = intval($record->id);
                $record->attempts = intval($record->attempts);
                $record->data = maybe_unserialize($record->data);
            }
            return $record;
        }, $records);

        return $records;
    }

    /**
     * Set the status of a task to 'new'
     * 
     * @param  int $id The id of the task to set as 'new'
     * @return boolean True if the update was successful, false otherwise
     */
    public function new_task(int $id)
    {
        $data = [
            'status' => 'new',
            'attempts' => 0,
            'response' => '',
            'updated_at' => current_time('mysql')
        ]; 
        return $this->update($data, ['id' => $id]);
    }

    /**
     * Set the status of a task to 'processing'
     * 
     * @param  int $id The id of the task to set as 'processing'
     * @return boolean True if the update was successful, false otherwise
     */
    public function processing_task(int $id)
    {
        $data = [
            'status' => 'processing',
            'updated_at' => current_time('mysql')
        ]; 
        return $this->update($data, ['id' => $id]);
    }

    /**
     * Set the status of a task to 'completed'
     * 
     * @param  int $id The id of the task to set as 'completed'
     * @return boolean True if the update was successful, false otherwise
     */
    public function completed_task(int $id)
    {
        $data = [
            'status' => 'completed',
            'updated_at' => current_time('mysql')
        ]; 
        return $this->update($data, ['id' => $id]);
    }

    /**
     * Set the status of a task to 'failed' or 'cancelled' depending on the number of attempts.
     * 
     * @param  int $id The id of the task to set as 'failed' or 'cancelled'
     * @param  int $attempts The number of attempts made for the task
     * @return boolean True if the update was successful, false otherwise
     */
    public function failed_task(int $id, int $attempts=1)
    {
        $max_attempts = AsynchronousEmailsPluginHelper::get_option('max_attempts');
        $status = $attempts >= $max_attempts? 'cancelled' : 'failed';

        $data = [
            'status' => $status,
            'attempts' => $attempts,
            'updated_at' => current_time('mysql')
        ]; 
        return $this->update($data, ['id' => $id]);
    }

    /**
     * Store the response of a failed email task.
     * 
     * @param  WP_Error $wp_error The WP_Error object with the error data for the failed task.
     * @return boolean True if the update was successful, false otherwise
     */
    public function failed_task_response(WP_Error $wp_error)
    {
        $processId = null;
        $data = $wp_error->get_error_data('wp_mail_failed');
        $response = $wp_error->get_error_message();
        if(is_array($data)){
            $processId = $data['headers']['Process-Id']?? null;
        }

        if( is_null($processId) ) return; 

        return $this->update([
            'response' => $response,
            'updated_at' => current_time('mysql')
        ], ['process_id' => $processId]);
    }

    /**
     * Update bulk 
     * 
     * @param  int $id
     * @return void
     */
    public function update_tasks_bulk_pending(array $ids)
    {
        return $this->update_tasks_bulk($ids, 'pending');
    }

    /**
     * Update bulk 
     * 
     * @param  array $id
     * @param  string $status
     * @return void
     */
    protected function update_tasks_bulk(array $ids, string $status)
    {
        if( !empty($ids) ){
            $chunks = array_chunk($ids, 250);
            foreach( $chunks as $chunk ) {
                $chunkIds = implode( ',', $chunk );
                $this->db->query(
                    $this->db->prepare(
                        "
                            UPDATE `{$this->table}` 
                            SET `status` = %s, `updated_at` = %s 
                            WHERE `id` IN (" . $chunkIds . ")

                        ", $status, current_time('mysql')
                    )
                );
            }
        }
    }

    /**
     * Get records with pagination
     * 
     * @param  int $per_page
     * @param  array $filters
     * @return array
     */
    public function get_all_paginate(int $per_page=10, array $filters=[])
    {

        $perPage = $per_page > 0? $per_page : $this->perPage;

        $page = isset($_REQUEST['page'])? intval($_REQUEST['page']) : 1;
        $queryParams = [];

        $query = [];
        $query['params'] = [];
        // Count
        $query['sql']['count'][] = "SELECT count(*) as count FROM `{$this->table}`";
        // Query
        $query['sql']['select'][] = "SELECT * FROM `{$this->table}`";
        // Filters
        if (isset($filters)) {
            if (isset($filters['status'])) {
                $status = trim(sanitize_text_field($filters['status'] ?? ''));
                if ( !empty($status) ) {
                    $query['sql']['where'][] = 'status = %s';
                    $query['params'][] = $status;
                }
            }
            if (isset($filters['created_at'])) {
                $created_at = trim(sanitize_text_field($filters['created_at'] ?? ''));
                if ( !empty($created_at) ) {
                    $query['sql']['where'][] = 'created_at >= %s AND created_at < %s + INTERVAL 1 DAY';
                    $query['params'][] = $created_at;
                    $query['params'][] = $created_at;
                }
            }
        }
        // Where Query
        $whereQuery = '';
        if( isset($query['sql']['where']) && !empty($query['sql']['where']) ){
            foreach($query['sql']['where'] as $c => $whereSql){
                $whereQuery .= $c > 0? ' AND ' : ' WHERE ';
                $whereQuery .= $whereSql;
            }
        }
        // Count Query
        $countQuery = implode(" ", $query['sql']['count']);
        $countQuery .= $whereQuery;
        $countParams = $query['params'];
        if(!empty($countParams)){
            $countQuery = $this->db->prepare($countQuery, $countParams);
        }
        $count = $this->db->get_var($countQuery);
        $pages = ceil($count / $perPage);

        $query['sql']['order'][] = ' ORDER BY id DESC ';
        $query['sql']['limit'][] = ' LIMIT %d ';
        $query['sql']['offset'][] = 'OFFSET %d';
        $query['params'][] = $perPage;
        $query['params'][] = $perPage * ($page - 1);
        
        $sql = implode(" ", $query['sql']['select']);
        $sql .= $whereQuery;
        $sql .= implode(" ", $query['sql']['order']);
        $sql .= implode(" ", $query['sql']['limit']);
        $sql .= implode(" ", $query['sql']['offset']);

        $params = $query['params'];

        $records = $this->db->get_results(
            $this->db->prepare($sql, $params)
        );

        return [
            'records' => (array) $records, 
            'count' => intval($count), 
            'current' => intval($page), 
            'pages' => intval($pages)
        ];
    }

    /**
     * Delete old records
     * 
     * @return bool Boolean true on success or false on error.
     */
    public function delete_old_records(int $days)
    {
        return $this->db->query(
            $this->db->prepare('
                    DELETE FROM ' . $this->table . ' 
                    WHERE `status` IN (%s, %s) 
                    AND datediff(now(), `created_at`) >= %d
                ', 
                'cancelled', 'completed', $days)
        );
    }

}