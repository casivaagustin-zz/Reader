<?php

namespace Reader\Model;

abstract class Model {
  
  const PAGE_SIZE = 50;
  /**
   * Creates a new instance 
   * 
   * @global Silex\Application $app
   */
  public function __construct() {
  
  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
    return $this->$name = $value;
  }

}

