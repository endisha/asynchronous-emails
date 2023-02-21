<?php
/**
 * Delete Old Queue Records Job
 *
 * @package AsynchronousEmails
 * @version 1.0.0
 */
class AsynchronousEmailsSchedulingDeleteOldRecordsJob extends AsynchronousEmailsAbstractSchedulingManager
{
    /**
     * The action hook to use when scheduling this job.
     *
     * @var string
     */
    protected $hook = 'scheduling_delete_old_queue_recrods';

    /**
     * The recurrence of this job.
     *
     * @var string
     */
    protected $recurrence = 'scheduling_delete_old_queue_recrods_every_12_hour';

    /**
     * The interval at which this job should be run.
     *
     * @var int
     */
    protected $interval = 43200;

    /**
     * The display string for the interval at which this job should be run.
     *
     * @var string
     */
    protected $display = 'every 12 hours';


    /**
     * Create a new instance
     * 
     * @return void
     */
    public function __construct()
    {

        $date = new \DateTime('today midnight', wp_timezone());
        $this->time = $date->format('U');
    }

    /**
     * Processes the scheduling of deletion of old records.
     *
     * @param array $args Array of arguments to be passed to the task when it is executed.
     * @return void
     */
    public function process(array $args = []): void
    {

        $max_queue_record_age = intval(AsynchronousEmailsPluginHelper::get_option('max_queue_record_age'));
        $model = new AsynchronousEmailsQueueModel;
        $model->delete_old_records($max_queue_record_age);

    }
}