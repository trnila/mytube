<?php

use Phinx\Migration\AbstractMigration;

class Base extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * More information on this method is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-change-method
	 */
	public function change()
	{
		$this->table('users')
			->addColumn('username', 'string', array('length' => 20, 'null' => false))
			->addColumn('email', 'string', array('length' => 80, 'null' => false))
			->addColumn('password', 'string', array('null' => false))
			->addColumn('active', 'boolean', array('default' => true, 'null' => false))
			->addIndex('username', array('unique' => true))
			->create();

		$this->table('users_identities')
			->addColumn('user_id', 'integer')
			->addColumn('type', 'string', array('length' => 10, 'null' => false))
			->addColumn('identity', 'string', array('null' => false))
			->addForeignKey('user_id', 'users', 'id', array('delete' => 'cascade'))
			->create();

		$this->table('videos', array('id' => false, 'primary_key' => array('id')))
			->addColumn('id', 'string', array('length' => 8))
			->addColumn('title', 'string', array('length' => 100, 'null' => false))
			->addColumn('description', 'text', array('null' => false))
			->addColumn('created', 'datetime', array('null' => false))
			->addColumn('user_id', 'integer', array('null' => false))
			->addColumn('duration', 'integer')
			->addColumn('enabled', 'boolean', array('null' => false))
			->addColumn('isvideo', 'boolean')
			->addForeignKey('user_id', 'users', 'id', array('delete' => 'cascade'))
			->create();

		$this->table('video_ratings', array('id' => false, 'primary_key' => array('video_id', 'user_id')))
			->addColumn('video_id', 'string', array('length' => 8))
			->addColumn('user_id', 'integer', array('null' => false))
			->addColumn('positive', 'boolean', array('null' => false))
			->addColumn('created', 'datetime', array('null' => false))
			->addForeignKey('video_id', 'videos', 'id', array('delete' => 'cascade'))
			->addForeignKey('user_id', 'users', 'id', array('delete' => 'cascade'))
			->create();

		$this->table('video_comments')
			->addColumn('video_id', 'string', array('length' => 8))
			->addColumn('user_id', 'integer', array('null' => false))
			->addColumn('text', 'text', array('null' => false))
			->addColumn('created', 'datetime', array('null' => false))
			->addForeignKey('video_id', 'videos', 'id', array('delete' => 'cascade'))
			->addForeignKey('user_id', 'users', 'id', array('delete' => 'cascade'))
			->create();

		$this->table('video_tags', array('id' => false, 'primary_key' => array('video_id', 'tag')))
			->addColumn('video_id', 'string', array('length' => 8, 'null' => false))
			->addColumn('tag', 'string', array('length' => 20, 'null' => false))
			->addColumn('position', 'integer', array('null' => false))
			->addForeignKey('video_id', 'videos', 'id', array('delete' => 'cascade'))
			->create();




			$this->execute("INSERT INTO `users` (`username`, `email`, `password`, `active`) VALUES ('trnila', '', '111a4e118fe1a664887cb9137465739d43da8cd54c27c3dc1735faca7b3722884aa72660d825824841a4e98bf8ad3129de9934ddc0e834bc301e3af9a77fc1bf', '1');");



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