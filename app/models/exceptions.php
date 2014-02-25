<?php
namespace Model;

class Exception extends \Exception {}
class DuplicateException extends Exception
{
	protected $key;

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function getKey()
	{
		return $this->key;
	}
}