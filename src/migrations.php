<?php
/**
 * Migration database
 * This class handles the creation of database tables for the plugin.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

class AsynchronousEmailsMigrations
{
    /**
     * Create DB tables
     * 
     * This method creates the database tables required by the plugin.
     * 
     * @return void
     */
    public function booting($path)
    {
        $file = $this->pluginFile();
        if(!is_null($file)){
            $plugin = dirname($file);
            $this->execute( $this->prepare( $plugin ) );
        }
    }

    /**
     * Prepare SQL query
     * 
     * This method prepares the SQL query to be executed to create the database tables.
     * 
     * @param  \string  $plugin The directory path of the plugin.
     * @return string Returns the prepared SQL query.
     */
    public function prepare($plugin)
    {
        global $table_prefix, $wpdb;

        $sql = '';
        $dir = glob("{$plugin}/database/*.sql"); 
        foreach ($dir as $file) {
            $filename = basename($file, ".sql");
            if($wpdb->get_var( "show tables like '{$table_prefix}{$filename}'" ) != $table_prefix.$filename){
                $sql .= file_get_contents($file);
            }
        }
        return $sql;

    }

    /**
     * Execute SQL query
     * 
     * This method executes the SQL query to create the database tables.
     * 
     * @param  string  $query The SQL query to be executed.
     * @return void
     */
    public function execute($query)
    {
        global $table_prefix;
        if(!empty($query)){
            $query = str_replace('{prefix}', $table_prefix, $query);
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($query);
        }
    }

    /**
     * Get plugin file
     * 
     * This method retrieves the plugin file from the call stack.
     * 
     * @return mixed  Returns a string containing the plugin file path or null if the file could not be found.
     */
    public function pluginFile()
    {
        $file = null;
        $backTrace = debug_backtrace();
        foreach ($backTrace as $entry) {
            if ($entry['function'] == 'activate_plugin') {
                if(isset($entry['args'][0])){
                    $plugin = WP_PLUGIN_DIR . '/' .$entry['args'][0];
                    if(file_exists($plugin)){
                        $file = $plugin;
                    }
                    break;
                }
            }
        }
        return $file;
    }
}