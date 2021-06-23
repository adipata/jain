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

create table buffer
(
    id int auto_increment,
    content blob not null,
    user_id int not null,
    constraint buffer_pk
        primary key (id),
    constraint buffer_users_id_fk
        foreign key (user_id) references users (id)
);

alter table buffer
    add name varchar(1000) not null;


CREATE USER 'jain'@'localhost' IDENTIFIED BY 'jain';
GRANT USAGE ON *.* TO 'jain'@'localhost';
GRANT EXECUTE, SELECT, SHOW VIEW, ALTER, ALTER ROUTINE, CREATE, CREATE ROUTINE, CREATE TEMPORARY TABLES, CREATE VIEW, DELETE, DROP, EVENT, INDEX, INSERT, REFERENCES, TRIGGER, UPDATE, LOCK TABLES  ON `jain`.* TO 'jain'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;