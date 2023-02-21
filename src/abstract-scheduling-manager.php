<?php
/**
 * Scheduling Manager
 *
 * This abstract class serves as a base for scheduling tasks in WordPress using the WP Cron system.
 * Subclasses can extend this class to create custom scheduling logic for their tasks.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

abstract class AsynchronousEmailsAbstractSchedulingManager
{
    /**
     * Task scheduling event flag
     * 
     * @var bool
     */    
    protected $register_schedule_event = true;

    /**
     * Task scheduling hook
     * 
     * @var string
     */ 
    protected $hook = '';

    /**
     * Task scheduling recurrence
     * 
     * @var string
     */ 
    protected $recurrence = '';

    /**
     * Task scheduling interval in seconds
     * 
     * @var int
     */ 
    protected $interval = 60;


    /**
     * Task scheduling time
     * 
     * @var int
     */ 
    protected $time = null;

    /**
     * Task scheduling arguments
     * 
     * @var array
     */ 
    protected $args = [];

    /**
     * The internal display name of the task
     * 
     * @var string
     */ 
    protected $display = '';


    /**
     * Boot.
     * 
     * @return void
     */
    public function boot()
    {
        add_filter('cron_schedules', [$this, 'cron_schedules']);

        $this->register();

        if (method_exists($this, 'process')) {
            add_action($this->hook, [$this, 'process']);
        }
    }

    /**
     * Set custom time
     * 
     * @return void
     */
    public function setTime(){
        $this->time = time();
    }

    /**
     * Register schedule event
     * 
     * Adds the current task to the WordPress cron schedule if it is not already scheduled.
     * 
     * @return void
     */
    protected function register(): void
    {
        if ($this->register_schedule_event && !wp_next_scheduled($this->hook, $this->args)) {
            $time = is_null($this->time)? time() : $this->time;
            wp_schedule_event($time, $this->recurrence, $this->hook, $this->args);
        }
    }

    /**
     * Define custom cron schedules
     * 
     * This method adds custom intervals to the list of available cron schedules in WordPress.
     * 
     * @param array $schedules
     * @return array
     */
    public function cron_schedules(array $schedules): array
    {
        if ( $this->recurrence ) {
            $schedules[$this->recurrence] = [
                'interval' => $this->interval,
                'display' => $this->display
            ];
        }
        return $schedules;
    }

    /**
     * Process task
     * 
     * This abstract method should be defined in subclasses to perform the task logic.
     * 
     * @param array $args
     * @return void
     */
    abstract public function process(array $args = []): void;
}