CREATE TABLE `{prefix}asynchronous_email_queue` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `process_id` varchar(191) DEFAULT NULL,
  `to` text,
  `subject` text,
  `data` longtext,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `response` text NOT NULL,
  `attempts` int(11) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;