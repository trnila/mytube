<?php

use Phinx\Migration\AbstractMigration;

class ThumbnailsTimeCanBeNull extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * More information on this method is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-change-method
	 */
	public function change()
	{
		$this->table('video_thumbnails')
			->changeColumn('time', 'integer', array('null' => TRUE))
			->update();
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