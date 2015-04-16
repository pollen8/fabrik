CREATE TABLE IF NOT EXISTS `#__fabrik_comments` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`user_id` INT( 11 ) NOT NULL ,
	`ipaddress` CHAR( 14 ) NOT NULL ,
	`reply_to` INT( 11 ) NOT NULL ,
	`comment` MEDIUMTEXT NOT NULL ,
	`approved` TINYINT( 1 ) NOT NULL ,
	`time_date` TIMESTAMP NOT NULL ,
	`url` varchar( 255 ) NOT NULL ,
	`name` VARCHAR( 150 ) NOT NULL ,
	`email` VARCHAR( 100 ) NOT NULL ,
	`formid` INT( 6 ) NOT NULL,
	`row_id` INT( 6 ) NOT NULL,
	`rating` CHAR(2) NOT NULL,
	`annonymous` TINYINT(1) NOT NULL,
	`notify` TINYINT(1) NOT NULL,
	`diggs` INT( 6 ) NOT NULL
);