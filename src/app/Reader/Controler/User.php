<?php

namespace Reader\Controler;

class User extends Controler {

  public function presentLogin() {
    return $this->app['twig']->render('User\login.twig');  
  }
 
  public function doLogin($username, $password) {
    $user = \Reader\Model\User::login($username, $password);
    if (empty($user)) {
      return $this->app->redirect('/login');
    }
    return $this->app->redirect('/');
  }

}

