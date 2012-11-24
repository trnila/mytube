ALTER TABLE `videoSearch`
ADD `tags` text COLLATE 'utf8_czech_ci' NOT NULL,
COMMENT='';

ALTER TABLE `videoSearch`
CHANGE `tags` `tags` text COLLATE 'utf8_czech_ci' NULL AFTER `description`,
COMMENT='';

DELIMITER ;;
CREATE TRIGGER `video_tags_ai` AFTER INSERT ON `video_tags` FOR EACH ROW
UPDATE videoSearch SET tags = (SELECT GROUP_CONCAT(tag) FROM video_tags WHERE video_tags.video_id = NEW.video_id ORDER BY video_tags.position) WHERE id = NEW.video_id;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `video_tags_au` AFTER UPDATE ON `video_tags` FOR EACH ROW
UPDATE videoSearch SET tags = (SELECT GROUP_CONCAT(tag) FROM video_tags WHERE video_tags.video_id = NEW.video_id ORDER BY video_tags.position) WHERE id = NEW.video_id;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `video_tags_ad` AFTER DELETE ON `video_tags` FOR EACH ROW
UPDATE videoSearch SET tags = (SELECT GROUP_CONCAT(tag) FROM video_tags WHERE video_tags.video_id = OLD.video_id ORDER BY video_tags.position) WHERE id = OLD.video_id;;
DELIMITER ;

ALTER TABLE `videoSearch`
ADD FULLTEXT `title_description_tags` (`title`, `description`, `tags`),
ADD FULLTEXT `tags` (`tags`),
DROP INDEX `title_description`;