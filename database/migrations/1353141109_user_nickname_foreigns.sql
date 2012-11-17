ALTER TABLE `videos`
CHANGE `user_email` `user_nickname` varchar(30) COLLATE 'utf8_czech_ci' NOT NULL AFTER `created`,
ADD FOREIGN KEY (`user_nickname`) REFERENCES `users` (`nickname`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `ratings`
CHANGE `user_email` `user_nickname` varchar(30) COLLATE 'utf8_czech_ci' NOT NULL AFTER `video_id`,
ADD FOREIGN KEY (`user_nickname`) REFERENCES `users` (`nickname`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `identities`
CHANGE `user_email` `user_nickname` varchar(30) COLLATE 'utf8_czech_ci' NOT NULL FIRST,
ADD FOREIGN KEY (`user_nickname`) REFERENCES `users` (`nickname`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `history`
DROP FOREIGN KEY `history_ibfk_6`;

ALTER TABLE `history`
CHANGE `user_email` `user_nickname` varchar(30) COLLATE 'utf8_czech_ci' NOT NULL FIRST,
ADD FOREIGN KEY (`user_nickname`) REFERENCES `users` (`nickname`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `comments`
DROP FOREIGN KEY `comments_ibfk_7`;

ALTER TABLE `comments`
CHANGE `user_email` `user_nickname` varchar(30) COLLATE 'utf8_czech_ci' NOT NULL AFTER `video_id`,
ADD FOREIGN KEY (`user_nickname`) REFERENCES `users` (`nickname`) ON DELETE CASCADE,
COMMENT='';
