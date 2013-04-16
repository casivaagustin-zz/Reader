<?php

namespace Reader\Controler;

class Controler {

  /**
   * Creates a new instance 
   * 
   * @global Silex\Application $app
   */
  public function __construct() {
    global $app;
    $this->app = $app;
  }
  
}

