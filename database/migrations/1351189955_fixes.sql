ALTER TABLE `comments`
DROP FOREIGN KEY `comments_ibfk_5`;
ALTER TABLE `comments`
CHANGE `user_id` `user_email` varchar(60) COLLATE 'utf8_czech_ci' NOT NULL AFTER `video_id`,
ADD FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `history`
DROP FOREIGN KEY `history_ibfk_4`;
ALTER TABLE `history`
CHANGE `user_id` `user_email` varchar(60) COLLATE 'utf8_czech_ci' NOT NULL FIRST,
ADD FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `identities`
DROP FOREIGN KEY `identities_ibfk_1`;
ALTER TABLE `identities`
CHANGE `user_id` `user_email` varchar(60) COLLATE 'utf8_czech_ci' NOT NULL FIRST,
ADD FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `ratings`
DROP FOREIGN KEY `ratings_ibfk_6`;
ALTER TABLE `ratings`
CHANGE `user_id` `user_email` varchar(60) COLLATE 'utf8_czech_ci' NOT NULL AFTER `video_id`,
ADD FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE,
COMMENT='';

ALTER TABLE `thumbnails`
DROP `id`,
CHANGE `path` `path` varchar(255) COLLATE 'utf8_czech_ci' NOT NULL FIRST,
CHANGE `video_id` `video_id` varchar(20) COLLATE 'utf8_czech_ci' NOT NULL AFTER `path`,
COMMENT='';
ALTER TABLE `thumbnails`
ADD PRIMARY KEY `path` (`path`);

ALTER TABLE `videos`
DROP FOREIGN KEY `videos_ibfk_1`;
ALTER TABLE `videos`
CHANGE `user_id` `user_email` varchar(60) COLLATE 'utf8_czech_ci' NOT NULL AFTER `created`,
ADD FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE,
COMMENT='';