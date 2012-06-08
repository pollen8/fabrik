CREATE TABLE IF NOT EXISTS `#__fabrik_notification` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`reference` VARCHAR( 50 ) NOT NULL COMMENT 'tableid.formid.rowid reference',
	`user_id` INT( 6 ) NOT NULL ,
	`reason` VARCHAR( 40 ) NOT NULL,
	`message` TEXT NOT NULL,
	`label` VARCHAR( 200 ) NOT NULL,
	 UNIQUE `uniquereason` ( `user_id` , `reason` ( 20 ) , `reference` )
);
 
CREATE TABLE IF NOT EXISTS `#__fabrik_notification_event` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`reference` VARCHAR( 50 ) NOT NULL COMMENT 'tableid.formid.rowid reference',
	`event` VARCHAR( 255 ) NOT NULL ,
	`user_id` INT (6) NOT NULL,
	`date_time` DATETIME NOT NULL 
);
 
 CREATE TABLE  IF NOT EXISTS `#__fabrik_notification_event_sent` (
	`id` INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`notification_event_id` INT( 6 ) NOT NULL ,
	`user_id` INT( 6 ) NOT NULL ,
	`date_sent` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`sent` TINYINT( 1 ) NOT NULL DEFAULT '0',
	UNIQUE `user_notified` ( `notification_event_id` , `user_id` )
);