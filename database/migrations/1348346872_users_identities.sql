CREATE TABLE `identities` (
  `user_id` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `identity` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`user_id`,`identity`),
  CONSTRAINT `identities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
