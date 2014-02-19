<?php

use Phinx\Migration\AbstractMigration;

class UsersInformations extends AbstractMigration
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
            ->addColumn('firstname', 'string', array('after' => 'username', 'null' => TRUE))
            ->addColumn('lastname', 'string', array('after' => 'firstname', 'null' => TRUE))
            ->addColumn('aboutme', 'text', array('after' => 'lastname', 'null' => TRUE))
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