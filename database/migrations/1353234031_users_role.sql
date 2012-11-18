ALTER TABLE `users`
ADD `role` enum('user','admin') COLLATE 'utf8_czech_ci' NOT NULL DEFAULT 'user' AFTER `password`,
COMMENT='';