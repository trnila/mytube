<?php
namespace Model\Security;
use Nette;

class Authorizator extends \Nette\Security\Permission
{
	public function __construct()
	{
		// $this->addRole('admin');
		// $this->addResource('Homepage');

		// $this->allow('admin', 'Homepage', 'show');
	}
}