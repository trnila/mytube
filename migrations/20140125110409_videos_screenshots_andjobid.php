<?php

use Phinx\Migration\AbstractMigration;

class VideosScreenshotsAndjobid extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * More information on this method is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-change-method
	 */
	public function change()
	{
		$this->table('videos')
			->addColumn('jobid', 'string', array('null' => TRUE))
			->update();

		$this->table('video_thumbnails')
			->addColumn('video_id', 'string')
			->addColumn('number', 'integer')
			->addColumn('time', 'integer')
			->addForeignKey('video_id', 'videos', 'id')
			->create();
	}

	/**
	 * Migrate Up.
	 */
	public function up()
	{

	}

	/**
	 * Migrate Down.
	 */
	public function down()
	{

	}
}