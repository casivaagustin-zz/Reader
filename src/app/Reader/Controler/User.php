<?php

namespace Reader\Controler;

class User extends Controler {

  public function presentLogin() {
    global $app;
    $message = $app['session']->get('message', null);
    return $this->app['twig']->render('User\login.twig', 
      array('message' => $message));  
  }
 
  public function doLogin($username, $password) {
    global $app;
    
    $user = \Reader\Model\User::login($username, $password);
    
    if (empty($user)) {
      $app['session']->set('message', 'Not valid user / password combination');
      return $this->app->redirect('/login');
    }
    
    unset($_SESSION['message']);
    
    return $this->app->redirect('/');
  }

  public function register($username, $password) {
    global $app;

    if (\Reader\Model\User::isLoggedIn()) {
      return $app->json("You are logged in", 500);
    }
    
    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
      return $app->json("That is not an Email", 500);
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

  public function recover($name) {
    global $app;

    if (\Reader\Model\User::isLoggedIn()) {
      return $app->json("You are logged in", 500);
    }

    if (!filter_var($name, FILTER_VALIDATE_EMAIL)) {
      return $app->json("That is not an Email", 500);
    }

    $user = \Reader\Model\User::getUser($name);
    
    if (!$user) {
      return $app->json("Your email does not exists, create an account", 500);
    }
    
    try{
      //Send the email
      $password = \Reader\Model\User::regeneratePassword($user);
      
      $to = $user['name'];
      $subject = 'Password from Reader';
      $message = 'Your new password is ' . $password;
      $headers = 'From: info@reader.surgicalworks.com';
      mail($message, $subject, $message, $headers);
      
      return $app->json('Your Password have been sent, Check in Span Just in case');
    } catch(\Exception $e) {
      $app->json($e->getMessage(), 500);
    }
  }

}

