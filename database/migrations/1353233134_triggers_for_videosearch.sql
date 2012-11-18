DELIMITER ;;
CREATE TRIGGER `videos_ai` AFTER INSERT ON `videos` FOR EACH ROW
INSERT INTO videoSearch(id, title, description)
VALUES(NEW.id, NEW.title, NEW.description);;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `videos_au` AFTER UPDATE ON `videos` FOR EACH ROW
UPDATE videoSearch
	SET id = NEW.id,
		title = NEW.title,
		description = NEW.description
	WHERE id = OLD.id;;
DELIMITER ;

DELIMITER ;;
CREATE TRIGGER `videos_ad` AFTER DELETE ON `videos` FOR EACH ROW
DELETE FROM videoSearch WHERE id = OLD.id;;
DELIMITER ;