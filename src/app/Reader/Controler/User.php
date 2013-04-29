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

  public function register($username, $password) {
    global $app;

    if (\Reader\Model\User::isLoggedIn()) {
      return $app->json("You are logged in", 500);
    }

    if (\Reader\Model\User::getUser($username)) {
      return $app->json("Your account exists", 500);
    }

    try{
      \Reader\Model\User::create($username, $password);
      return $app->json('Your account was created');
    } catch(\Exception $e) {
      $app->json($e->getMessage(), 500);
    }
  }
}

