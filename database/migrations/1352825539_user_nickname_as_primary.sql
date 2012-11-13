ALTER TABLE `users`
ADD `nickname` varchar(30) NOT NULL FIRST,
COMMENT='';

UPDATE users SET nickname = email;

ALTER TABLE `users`
ADD PRIMARY KEY `nickname` (`nickname`),
ADD UNIQUE `email` (`email`),
DROP INDEX `PRIMARY`;

DELETE FROM `identities`;

ALTER TABLE `identities`
DROP FOREIGN KEY `identities_ibfk_1`;

ALTER TABLE `identities`
CHANGE `user_id` `user_nickname` varchar(30) COLLATE 'utf8_czech_ci' NOT NULL FIRST,
ADD FOREIGN KEY (`user_nickname`) REFERENCES `users` (`nickname`) ON DELETE CASCADE,
COMMENT='';