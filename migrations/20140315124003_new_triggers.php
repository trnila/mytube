<?php

use Phinx\Migration\AbstractMigration;

class NewTriggers extends AbstractMigration
{
    public function up()
    {
        $this->execute('DROP FUNCTION IF EXISTS toIndexable');
        $this->execute("CREATE FUNCTION `toIndexable` (`input` text) RETURNS text CHARACTER SET 'utf8'
        NO SQL
        BEGIN
            RETURN REPLACE(REPLACE(REPLACE(REPLACE(input, '-', ' '), ',', ' '), '.', ' '), '_', ' ');
        END");

        $this->execute('DROP TRIGGER IF EXISTS `videos_ai`');
        $this->execute('CREATE TRIGGER `videos_ai` AFTER INSERT ON `videos` FOR EACH ROW
INSERT INTO videoSearch(id, title, description)
    VALUES(NEW.id, toIndexable(NEW.title), toIndexable(NEW.description))');

        $this->execute('DROP TRIGGER IF EXISTS `videos_au`');
        $this->execute('CREATE TRIGGER `videos_au` AFTER UPDATE ON `videos` FOR EACH ROW
UPDATE videoSearch
        SET
            id = NEW.id,
            title = toIndexable(NEW.title),
            description = toIndexable(NEW.description)
        WHERE id = OLD.id');

        $this->execute('DROP TRIGGER IF EXISTS `video_tags_ai`');
        $this->execute('CREATE TRIGGER `video_tags_ai` AFTER INSERT ON `video_tags` FOR EACH ROW
UPDATE videoSearch
SET tags = (SELECT GROUP_CONCAT(tag SEPARATOR " ")
  FROM video_tags
  WHERE video_tags.video_id = NEW.video_id
  ORDER BY video_tags.position)
WHERE id = NEW.video_id');

        $this->execute('DROP TRIGGER IF EXISTS `video_tags_ai`');
        $this->execute('CREATE TRIGGER `video_tags_ai` AFTER INSERT ON `video_tags` FOR EACH ROW
UPDATE videoSearch
SET tags = (SELECT toIndexable(GROUP_CONCAT(tag))
  FROM video_tags
  WHERE video_tags.video_id = NEW.video_id
  ORDER BY video_tags.position)
WHERE id = NEW.video_id');

        $this->execute('DROP TRIGGER IF EXISTS `video_tags_au`');
        $this->execute('CREATE TRIGGER `video_tags_au` AFTER UPDATE ON `video_tags` FOR EACH ROW
UPDATE videoSearch
SET tags = (SELECT toIndexable(GROUP_CONCAT(tag))
 FROM video_tags
 WHERE video_tags.video_id = NEW.video_id
 ORDER BY video_tags.position)
WHERE id = NEW.video_id');

        $this->execute('DROP TRIGGER IF EXISTS `video_tags_ad`');
        $this->execute('CREATE TRIGGER `video_tags_ad` AFTER DELETE ON `video_tags` FOR EACH ROW
UPDATE videoSearch
SET tags = (SELECT toIndexable(GROUP_CONCAT(tag))
 FROM video_tags
 WHERE video_tags.video_id = OLD.video_id
 ORDER BY video_tags.position)
WHERE id = OLD.video_id');
    }


    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}