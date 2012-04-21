<?php
namespace Model\Repository;

class Users extends Repository
{
	const PASSWORD_SALT = 'ho5Dei3aLi';

	public function hash($email, $password)
	{
		return hash('sha512', md5(self::PASSWORD_SALT . $email) . sha1($password . self::PASSWORD_SALT));
	}
}