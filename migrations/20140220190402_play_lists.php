<?php

use Phinx\Migration\AbstractMigration;

class PlayLists extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     */
    public function change()
    {
        $this->table('playlists')
            ->addColumn('user_id', 'integer')
                ->addForeignKey('user_id', 'users', 'id', array('delete' => 'cascade'))
            ->addColumn('name', 'string')
            ->addColumn('created', 'datetime')
            ->addColumn('private', 'boolean')
            ->addIndex(array('user_id', 'name'), array('unique' => TRUE))
            ->create();

        $this->table('playlist_videos', array('id' => false, 'primary_key' => array('playlist_id', 'video_id')))
            ->addColumn('playlist_id', 'integer')
                ->addForeignKey('playlist_id', 'playlists', 'id', array('delete' => 'cascade'))
            ->addColumn('video_id', 'string', array('length' => 8))
                ->addForeignKey('video_id', 'videos', 'id', array('delete' => 'cascade'))
            ->addColumn('added', 'datetime')
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