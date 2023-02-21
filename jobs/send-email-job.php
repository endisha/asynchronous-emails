<?php
/**
 * Send Email Job
 *
 * This class represents the task of sending emails asynchronously, using the WP Mail function.
 *
 * @package AsynchronousEmails
 * @version 1.0.0
 */
class AsynchronousEmailsSendEmailJob extends AsynchronousEmailsAbstractSchedulingManager
{
    /**
     * The action hook to use when scheduling this job.
     *
     * @var string $hook
     */
    protected $hook = 'send_email_job';

    /**
     * The interval at which this job should be run.
     *
     * @var int $interval
     */
    protected $interval = 60;

    /**
     * Display
     *
     * @var string $display
     */
    protected $display = 'every minute';

    /**
     * Register Schedule Event
     *
     * @var bool $register_schedule_event
     */
    protected $register_schedule_event = false;

    /**
     * Process
     *
     * This method is responsible for processing the email entry and sending the email. 
     * The method retrieves the email data from the AsynchronousEmailsQueueModel, 
     * sets the task as processing and triggers the `wp_mail` function. 
     * If the email is sent successfully, the task is marked as completed. 
     * If the email fails to send, the task is marked as failed and the attempts counter is incremented.
     *
     * @param array $args
     * @return void
     */
    public function process(array $args = []): void
    {
        if (!empty($args) && isset($args[0])) {

            $entryId = $args[0];

            $model = new AsynchronousEmailsQueueModel;
            $entry = $model->first($entryId);

            if ($entry) {
                $data = !empty($entry->data)? maybe_unserialize($entry->data) : [];
                if (!empty($data)) {
                    $model->processing_task($entry->id);
                    $send = call_user_func_array('wp_mail', $data);
                    if ($send) {
                        $model->completed_task($entry->id);
                    } else {
                        $model->failed_task($entry->id, ++$entry->attempts);
                    }
                }
            }

        }
    }
}