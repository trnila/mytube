CREATE TABLE `users` (
  `email` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `password` char(128) COLLATE utf8_czech_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `fbId` int(11) DEFAULT NULL,
  PRIMARY KEY (`email`),
  UNIQUE KEY `fbId` (`fbId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `users` (`email`, `password`, `active`, `fbId`) VALUES
('admin@example.com',	'e92c7e7d8724925b00b22862a173ae740e62d15abe54d3d47f59b2c7b28b8b21ebb350766f87c9ca5b613b778f609bbb91fe05421b558d17dc339a94c67f9a11',	1,	NULL);