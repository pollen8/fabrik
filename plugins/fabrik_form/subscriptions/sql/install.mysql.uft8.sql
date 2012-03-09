CREATE TABLE IF NOT EXISTS `#__fabrik_subs_invoices` (
  `id` int(6) NOT NULL auto_increment,
  `subscr_id` int(6) default NULL,
  `invoice_number` varchar(255) default NULL,
  `created_date` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  `transaction_date` datetime default '0000-00-00 00:00:00',
  `gateway_id` int(6) default NULL,
  `amount` varchar(40) default NULL,
  `currency` varchar(10) default NULL,
  `paid` INT(1),
  `pp_txn_id` varchar(255) default NULL,
  `pp_payment_amount` varchar(20) default NULL,
  `pp_payment_status` varchar(20) default NULL,
  `pp_txn_type` varchar(20) default NULL,
  `pp_fee` varchar(255) default NULL,
  `pp_payer_email` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `fb_prefilter_method_INDEX` (`gateway_id`),
  KEY `fb_filter_invoice_number_INDEX` (`invoice_number`(10)),
  KEY `fb_filter_subscr_id_INDEX` (`subscr_id`),
  KEY `fb_filter_pp_payer_email_INDEX` (`pp_payer_email`(10)),
  KEY `fb_filter_pp_txn_id_INDEX` (`pp_txn_id`(10))
);


CREATE TABLE IF NOT EXISTS `#__fabrik_subs_plans` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(1) default NULL,
  `visible` tinyint(1) default NULL,
  `ordering` int(11) NOT NULL default '999999',
  `plan_name` varchar(255) default NULL,
  `desc` text,
  `usergroup` int(3) default NULL,
  `free` text,
  `strapline` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `fb_prefilter_active_INDEX` (`active`),
  KEY `fb_prefilter_visible_INDEX` (`visible`)
);

CREATE TABLE IF NOT EXISTS `#__fabrik_subs_cron_emails` (
  `id` int(6) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `event_type` varchar(20) NOT NULL,
  `timeunit` varchar(2) NOT NULL,
  `time_value` int(3) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fb_filter_event_type_INDEX` (`event_type`(10))
);

CREATE TABLE IF NOT EXISTS `#__fabrik_subs_payment_gateways` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `active` text,
  `description` text,
  `subscription` text,
  PRIMARY KEY  (`id`)
);


CREATE TABLE IF NOT EXISTS `#__fabrik_subs_plan_billing_cycle` (
  `id` int(11) NOT NULL auto_increment,
  `plan_id` int(6) NOT NULL,
  `duration` int(6) NOT NULL,
  `period_unit` char(1) NOT NULL,
  `cost` int(6) NOT NULL,
  `currency` char(4) NOT NULL,
  `description` varchar(255) NOT NULL,
  `label` varchar(255) default NULL,
  `plan_name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `#__fabrik_subs_subscriptions` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) default NULL,
  `primary` int(1) NOT NULL default '0',
  `type` int(6) default NULL,
  `status` varchar(10) default NULL,
  `signup_date` datetime default '0000-00-00 00:00:00',
  `lastpay_date` datetime default '0000-00-00 00:00:00',
  `cancel_date` datetime default '0000-00-00 00:00:00',
  `eot_date` datetime default '0000-00-00 00:00:00',
  `plan` int(6) default NULL,
  `recurring` int(1) NOT NULL default '0',
  `lifetime` int(1) NOT NULL default '0',
  `expiration` datetime default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `fb_filter_status_INDEX` (`status`),
  KEY `fb_order_userid_INDEX` (`userid`),
  KEY `fb_filter_plan_INDEX` (`plan`),
  KEY `fb_order_lastpay_date_INDEX` (`lastpay_date`),
  KEY `fb_filter_lastpay_date_INDEX` (`lastpay_date`),
  KEY `fb_filter_type_INDEX` (`type`),
  KEY `fb_order_signup_date_INDEX` (`signup_date`)
);

CREATE TABLE IF NOT EXISTS `#__fabrik_subs_users` (
  `id` int(6) NOT NULL auto_increment,
  `time_date` datetime default NULL,
  `userid` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `username` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `plan_id` int(11) default NULL,
  `terms` text,
  `termstext` text,
  `gateway` int(6) default NULL,
  `pp_txn_id` varchar(255) default NULL,
  `pp_payment_amount` varchar(255) default NULL,
  `pp_payment_status` varchar(255) default NULL,
  `billing_cycle` int(11) default NULL,
  `billing_duration` varchar(255) default NULL,
  `billing_period` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `fb_filter_userid_INDEX` (`userid`(10))
)
