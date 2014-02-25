<?php

use Phinx\Migration\AbstractMigration;

class FullTextSearch extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->query(
'CREATE TABLE `videoSearch` (
  `id` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `tags` text,
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title_description_tags` (`title`,`description`,`tags`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=MyISAM');


        // videos triggers
        $this->query(
'CREATE TRIGGER `videos_ai` AFTER INSERT ON `videos` FOR EACH ROW
    INSERT INTO videoSearch(id, title, description)
    VALUES(NEW.id, NEW.title, NEW.description);
;');

        $this->query(
'CREATE TRIGGER `videos_au` AFTER UPDATE ON `videos` FOR EACH ROW
    UPDATE videoSearch
        SET
            id = NEW.id,
            title = NEW.title,
            description = NEW.description
        WHERE id = OLD.id;
;');

$this->query(
'CREATE TRIGGER `videos_ad` AFTER DELETE ON `videos` FOR EACH ROW
    DELETE FROM videoSearch WHERE id = OLD.id;
;');

    // tags triggers
    $this->query(
'CREATE TRIGGER `video_tags_ai` AFTER INSERT ON `video_tags` FOR EACH ROW
    UPDATE videoSearch SET tags = (SELECT GROUP_CONCAT(tag) FROM video_tags WHERE video_tags.video_id = NEW.video_id ORDER BY video_tags.position) WHERE id = NEW.video_id;
;');

    $this->query(
'CREATE TRIGGER `video_tags_au` AFTER UPDATE ON `video_tags` FOR EACH ROW
    UPDATE videoSearch SET tags = (SELECT GROUP_CONCAT(tag) FROM video_tags WHERE video_tags.video_id = NEW.video_id ORDER BY video_tags.position) WHERE id = NEW.video_id;
;');

    $this->query(
'CREATE TRIGGER `video_tags_ad` AFTER DELETE ON `video_tags` FOR EACH ROW
    UPDATE videoSearch SET tags = (SELECT GROUP_CONCAT(tag) FROM video_tags WHERE video_tags.video_id = OLD.video_id ORDER BY video_tags.position) WHERE id = OLD.video_id;
;');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('videoSearch')->drop();

        foreach(array('videos', 'video_tags') as $table) {
            foreach(array('ai', 'au', 'ad') as $action) {
                $this->query("DROP TRIGGER IF EXISTS `{$table}_{$action}`");
            }
        }
    }
}