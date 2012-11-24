CREATE TABLE `video_tags` (
  `video_id` varchar(20) COLLATE 'utf8_czech_ci' NOT NULL,
  `tag` varchar(20) NOT NULL,
  FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE
) COMMENT='' ENGINE='InnoDB';

ALTER TABLE `video_tags`
ADD PRIMARY KEY `video_id_tag` (`video_id`, `tag`);