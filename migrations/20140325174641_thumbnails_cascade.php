<?php

use Phinx\Migration\AbstractMigration;

class ThumbnailsCascade extends AbstractMigration
{
	/**
	 * Migrate Up.
	 */
	public function up()
	{
		$this->table('video_thumbnails')
			->dropForeignKey('video')
			->addForeignKey('video_id', 'videos', 'id', array('delete' => 'cascade'))
			->update();
	}

	/**
	 * Migrate Down.
	 */
	public function down()
	{

	}
}