<?php
/**
 * Model
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

abstract class AsynchronousEmailsAbstractModel
{
    /**
     * Databse instance
     * 
     * @var int
     */
    protected $db;

    /**
     * Table name
     * 
     * @var string
     */
    protected $table;

    /**
     * Last inserted ID
     * 
     * @var int
     */
    protected $last_inserted_id = 0;

    /**
     * Last inserted ID
     * 
     * @access protected
     * @var int
     */
    protected $perPage = 20;

    /**
     * Create a new instance.
     * 
     * @return void
     */
    public function __construct()
    {
        
        global $wpdb;
        $table = $wpdb->prefix . $this->table;

        $this->db = $wpdb;
        $this->table = $table;
    }

    /**
     * First
     *
     * @param  int $id
     * @return object
     */
    public function first(int $id)
    {
        return $this->db->get_row( 
            $this->db->prepare("
                SELECT * FROM $this->table WHERE `id` = %d
            ", $id ) );
    }

    /**
     * Update data
     * 
     * @param  array $data
     * @param  array $where
     * @return boolean
     */
    public function update(array $data, array $where=[])
    {
        return $this->db->update($this->table, $data, $where);
    }

    /**
     * Insert data
     * 
     * @param  array $data
     * @return boolean
     */
    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        $last_inserted_id = $this->db->insert_id;
        $this->last_inserted_id = $last_inserted_id;
        return $last_inserted_id > 0;
    }

    /**
     * Get last inserted ID
     * 
     * @return int
     */
    public function get_last_inserted_id($data)
    {
        return $this->last_inserted_id;
    }

    /**
     * Delete data
     * 
     * @param  array $where
     * @return boolean
     */
    public function delete(array $where=[])
    {
        return $this->db->delete($this->table, $where);
    }
}