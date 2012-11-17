CREATE TABLE `videoSearch` (
  `id` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL
) COMMENT='' ENGINE='MyISAM';

ALTER TABLE `videoSearch`
ADD FULLTEXT `title` (`title`),
ADD FULLTEXT `description` (`description`),
ADD FULLTEXT `title_description` (`title`, `description`);