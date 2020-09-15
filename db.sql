CREATE TABLE `users` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL ,
	`password` VARCHAR(50) NOT NULL ,
	`token` VARCHAR(50) NOT NULL ,
	PRIMARY KEY (`id`) USING BTREE
);

CREATE TABLE `keys` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`name` VARCHAR(50) NOT NULL DEFAULT '0' ,
	`data` VARBINARY(10000) NOT NULL DEFAULT '0',
	`alg` VARCHAR(20) NULL DEFAULT NULL ,
	`type` VARCHAR(20) NULL DEFAULT NULL ,
	`size` INT(11) NULL DEFAULT NULL,
	`cdate` DATETIME NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `name` (`name`) USING BTREE,
	INDEX `FK_keys_users` (`user_id`) USING BTREE,
	CONSTRAINT `FK_keys_users` FOREIGN KEY (`user_id`) REFERENCES `jain`.`users` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);
