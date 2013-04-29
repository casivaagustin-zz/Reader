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
    
    $app['db']->executeQuery('INSERT INTO user(name, password) VALUES(?,?)', 
            array($name, $password)
    );

    return true;
  }
  
}