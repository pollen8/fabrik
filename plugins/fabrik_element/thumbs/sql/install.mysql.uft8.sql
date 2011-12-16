CREATE TABLE IF NOT EXISTS  `#__fabrik_thumbs` (
	`user_id` VARCHAR( 255 ) NOT NULL ,
	`tableid` INT( 6 ) NOT NULL ,
	`formid` INT( 6 ) NOT NULL ,
	`row_id` INT( 6 ) NOT NULL ,
	`thumb` VARCHAR( 255 ) NOT NULL,
	`date_created` DATETIME NOT NULL,
	`element_id` INT( 6 ) NOT NULL,
	 PRIMARY KEY ( `user_id` , `tableid` , `formid` , `row_id`, `element_id` )
);