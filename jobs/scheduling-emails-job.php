<?php
/**
 * Scheduling Emails Job
 *
 * Class responsible for scheduling emails that are ready to be sent
 *
 * @package AsynchronousEmails
 * @version 1.0.0
 */
class AsynchronousEmailsSchedulingEmailsJob extends AsynchronousEmailsAbstractSchedulingManager
{
    /**
     * The action hook to use when scheduling this job.
     *
     * @var string
     */
    protected $hook = 'scheduling_emails_job';

    /**
     * The recurrence of this job.
     *
     * @var string
     */
    protected $recurrence = 'scheduling_emails_job_every_minute';

    /**
     * The interval at which this job should be run.
     *
     * @var int
     */
    protected $interval = 60;

    /**
     * The display string for the interval at which this job should be run.
     *
     * @var string
     */
    protected $display = 'every minute';

    /**
     * Processes the scheduling of emails.
     *
     * This method will retrieve records from the email queue that are ready to be sent,
     * update their status to pending, and schedule individual tasks to send the emails.
     *
     * @param array $args Array of arguments to be passed to the task when it is executed.
     * @return void
     */
    public function process(array $args = []): void
    {
        $max_attempts = AsynchronousEmailsPluginHelper::get_option('max_attempts');
        $entriesIds = [];

        $model = new AsynchronousEmailsQueueModel;
        $items = $model->from_queue();

        if (!empty($items)) {
            foreach ($items as $entry) {
                if ($max_attempts > $entry->attempts) {
                    $entriesIds[] = $entry->id;
                }
            }
            $model->update_tasks_bulk_pending($entriesIds);
        }

        if (!empty($entriesIds)) {
            foreach ($entriesIds as $entryId) {
                $args = array( $entryId );
                wp_schedule_single_event(time(), 'send_email_job', [$args]);
            }
        }
    }
}