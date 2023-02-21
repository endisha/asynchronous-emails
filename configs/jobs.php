<?php
/**
 * Jobs config
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

return apply_filters( 'asynchronous_emails_jobs_config', [
    AsynchronousEmailsSchedulingEmailsJob::class,
    AsynchronousEmailsSendEmailJob::class,
    AsynchronousEmailsSchedulingDeleteOldRecordsJob::class,
] );