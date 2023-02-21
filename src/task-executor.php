<?php
/**
 * Task executor class for Asynchronous Emails
 *
 * Executes the scheduled tasks registered in the system.
 * This class is responsible for executing the scheduled tasks for the Asynchronous Emails plugin.
 * It provides a way to set the jobs for the plugin and execute them one by one.
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined( 'ABSPATH' )or exit;

class AsynchronousEmailsTaskExecutor
{
    /**
     * Array of registered tasks
     * 
     * @var array
     */
    protected $jobs = [];

    /**
     * Set the jobs to be executed.
     * 
     * @param array $jobs
     * @return void
     */
    public function setJobs(array $jobs = [])
    {
        $this->jobs = apply_filters('asynchronous_emails_jobs', $jobs);
    }

    /**
     * Executes the registered tasks.
     * 
     * @return void
     */
    public function execute()
    {
        $jobs = $this->get_jobs_classes();
        foreach ($jobs as $job) {
            $instance = new $job;
            if ($instance instanceof AsynchronousEmailsAbstractSchedulingManager) {
                if (method_exists($instance, 'boot')) {
                    $instance->boot();
                }
            }
        }
    }

    /**
     * Get job hooks
     * 
     * @return array $hooks An array of all the hooks associated with the registered jobs.
     */
    public function get_job_hooks()
    {
        $hooks = [];
        $jobs = $this->get_jobs_classes();
        foreach ($jobs as $job) {
            $instance = new $job;
            $ref = new \ReflectionClass($job);
            $prop = $ref->getProperty('hook');
            $prop->setAccessible(true);
            $hook = $prop->getValue($instance);
            add_filter('asynchronous_emails_job_hooks', function($hook) use($hooks){
                return array_merge( $hook, $hooks );
            });
            $hooks[] = $hook;
        }


        return $hooks;
    }

    /**
     * Get job classes
     *
     * @return array $jobs The array of job classes.
     */
    private function get_jobs_classes()
    {
        $jobs = [];
        foreach ($this->jobs as $job) {
            if (class_exists($job)) {
                $jobs[] = $job;
            }
        }
        return $jobs;
    }

}