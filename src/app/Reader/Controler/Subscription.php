<?php

namespace Reader\Controler;

class Subscription extends Controler {

  /**
   * List all Subscriptions and present subscribe and import forms
   * @global \Silex\Application $app
   * @return String HTML
   */
  public function index() {
    global $app;
    $subscriptions = \Reader\Model\Subscription::getAll();
    return $app['twig']->render('Subscription\list.twig', array('subscriptions'=> $subscriptions));
  }

  public function delete($id) {
    \Reader\Model\Subscription::delete($id);
    return true;
  }

  public function add($data) {
    if (!isset($data['url'])) {
      return json_encode($data);
    }
    
    try { 
      $feed = \Feedtcher\Feedtcher::fectch($data['url']);
      $subscription = \Reader\Model\Subscription::add($feed->title, $data['url']);
      return json_encode($subscription);
    } catch(\Exception $e) {
      return json_encode($e->getMessage());
    }
  }

  public function importOpml($file) {
    
    \Reader\Model\Subscription::import($ompl, $user);
  }
}

