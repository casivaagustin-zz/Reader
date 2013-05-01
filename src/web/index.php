<?php

global $app;
$app = require_once __DIR__ . '/../app/bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->register(new Silex\Provider\SessionServiceProvider());

// Routes
$app->get('/', function (Request $request) use ($app) {
  if (!Reader\Model\User::isLoggedIn()) {
    return $app->redirect('/login');
  }
  
  $page = $request->get('page', 0);
  $post = new \Reader\Controler\Post();
  return $post->index($page);
});

$app->get('/all', function (Request $request) use ($app) {
  if (!Reader\Model\User::isLoggedIn()) {
    return $app->redirect('/login');
  }
  
  $page = $request->get('page', 0);
  $post = new \Reader\Controler\Post();
  return $post->all($page);
});

$app->get('/post/{id}/read', function ($id) {
  if (!Reader\Model\User::isLoggedIn()) {
    return "";
  }
  
  $post = new \Reader\Controler\Post();
  return $post->markAsRead($id);
});

$app->get('/login', function() {
  $user = new Reader\Controler\User();
  return $user->presentLogin();
});

$app->post('/login', function (Request $request) {
  $user = new Reader\Controler\User();
  $data = $request->get('data');
  return $user->doLogin($data['name'], $data['password']);
});

$app->get('/logout', function() use($app) { 
  if (!Reader\Model\User::isLoggedIn()) {
    return $app->redirect('/login');
  }
  
  \Reader\Model\User::logout();
  return $app->redirect('/');
});

$app->get('/subscriptions', function () use ($app) {
  if (!Reader\Model\User::isLoggedIn()) {
    return $app->redirect('/login');
  }
  
  $subscription = new \Reader\Controler\Subscription();
  return $subscription->index();
});

$app->post('/subscriptions/import', function (Request $request) use ($app) {
  $file = $request->files->get('opml');
  
  if (!Reader\Model\User::isLoggedIn()) {
    return $app->redirect('/login');
  }
  
  if (isset($_FILES['opml']) && $_FILES['opml']['tmp_name'] != '') {
    $opml = file_get_contents($_FILES['opml']['tmp_name']);
    try {
      libxml_use_internal_errors(true);
      \Reader\Model\Subscription::import($opml, $app['session']->get('user')); 
    } catch(\Exception $e) {
      //Write Some lgo
    }
    unlink($_FILES['opml']['tmp_name']);
  }
  
  return $app->redirect('/subscriptions');
});

$app->delete('/subscription/{id}', function ($id) use ($app) {
  if (!Reader\Model\User::isLoggedIn()) {
    return $app->redirect('/login');
  }
  $subscription = new \Reader\Controler\Subscription();
  return $subscription->delete($id);
});

$app->post('/subscription', function (Request $request) use ($app) {
  if (!Reader\Model\User::isLoggedIn()) {
    return $app->redirect('/login');
  }
  
  $data = $request->get('data', null);
  $subscription = new \Reader\Controler\Subscription();
  return $subscription->add($data);
});

$app->put('/user', function(Request $request) {
  $user = new \Reader\Controler\User();
  $data = $request->get('data');
  return $user->register($data['name'], $data['password']); 
});

$app->post('/user/recover', function(Request $request){
  $user = new \Reader\Controler\User();
  $data = $request->get('data');
  return $user->recover($data['name']); 
});

$app->run();