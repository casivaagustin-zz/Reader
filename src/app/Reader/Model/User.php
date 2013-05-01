<?php

namespace Reader\Model;

class User extends Model {

  protected $id;
  protected $name;
  protected $password;

  /**
   * Checks if the User is Logged In
   * 
   * @global \Silex\Application $app
   * @return Boolean
   */
  static public function isLoggedIn() {
    global $app;
    $user = $app['session']->get('user');
    return !empty($user);
  }

  /**
   * Login the USer
   * 
   * @global \Silex\Application $app
   * @param String $name
   * @param Password $password
   * @return \Reader\Model\User|null
   */
  static public function login($name, $password) {
    global $app;
    
    $password = md5($password);
    
    $user = $app['db']->fetchAssoc('SELECT * FROM user WHERE name = ? AND password = ?',
            array($name, $password)
    );
    
    if ($user) {
      $app['session']->set('user', $user);
      return $user;
    }

    return null;
  }

  /**
   * Logouts the User
   * 
   * @global \Silex\Application $app
   */
  static public function logout() {
    global $app;
    $app['session']->set('user', null);
  }

  /**
   * Gets an user by Name.
   * 
   * @global \Silex\Application $app
   * @param String $name
   * @return Array or Null
   */
  static public function getUser($name) {
    global $app;
    return $app['db']->fetchAssoc('SELECT * from user WHERE name = ?', array($name));
  }

  static public function create($name, $password) {
    global $app;
    
    $password = md5($password);
    
    $app['db']->executeQuery('INSERT INTO user(name, password) VALUES(?,?)', 
            array($name, $password)
    );

    return true;
  }

  /**
   * Regenerates and sets the new password for a User.
   * 
   * @param Array $user Record
   * 
   * @return String password
   */
  static public function regeneratePassword($user) {
    global $app;
    
    $clean_password = generatePassword(6,4);
    $password = md5($clean_password);
    
    $app['db']->executeQuery('UPDATE user SET password = ? WHERE ID = ?', 
            array($password, $user['ID']));
    
    return $clean_password;
    
  }
}

function generatePassword($length=9, $strength=0) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}
 
	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}