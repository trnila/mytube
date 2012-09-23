CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `user_id` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `video_id` (`video_id`),
  CONSTRAINT `comments_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_6` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `history` (
  `user_id` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `video_id` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `created` datetime NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `video_id` (`video_id`),
  CONSTRAINT `history_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE,
  CONSTRAINT `history_ibfk_5` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `ratings` (
  `video_id` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `user_id` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `positive` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  KEY `video_id` (`video_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ratings_ibfk_5` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_6` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `thumbnails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `video_id` (`video_id`),
  CONSTRAINT `thumbnails_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `videos` (
  `id` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `created` datetime NOT NULL,
  `user_id` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `path` varchar(60) COLLATE utf8_czech_ci DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT 'in seconds',
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `processed` tinyint(4) NOT NULL COMMENT 'if video has thumbnails and is in webm format',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;