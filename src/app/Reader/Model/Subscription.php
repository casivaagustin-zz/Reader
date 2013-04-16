<?php

namespace Reader\Model;

class Subscription extends Model {

  /**
   * Does the import of the OMPL subscription file
   * @param String $ompl : OMPL content
   * @param Array $user : User Record
   */
  static public function import($ompl, $user) {
    $xml = new \SimpleXMLElement($ompl);
    $body = $xml->body;
    foreach ($body->outline as $outline) {
      self::importOutline($outline, $user);
    }
  }

  /**
   * Makes the import of a single outline;
   * 
   * @param type $outline
   * @param type $user
   */
  static private function importOutline($outline, $user) {
    if ($outline['xmlUrl']) {
      self::subscribe($outline['title'], $outline['xmlUrl'], $user);
    }

    if (isset($outline->outline)) {
      foreach ($outline->outline as $outline) {
        self::importOutline($outline, $user);
      }
    }
  }

  /**
   * Imports a single Source
   * 
   * @global \Reader\Model\type $app
   * 
   * @param String $title : Title of the source
   * @param String $url : Url to Subscribe
   * @param Array $user : USer Record
   * 
   */
  static public function subscribe($title, $url, $user) {
    global $app;

    $source = $app['db']->fetchAssoc('SELECT * FROM source WHERE name = ?', array($title));

    if (!$source) {
      $app['db']->executeQuery('INSERT INTO source(name, url) VALUES (?,?)', array($title, $url));
      $source_id = $app['db']->lastInsertId();
    }
    else {
      $source_id = $source['id'];
    }

    $us = $app['db']->fetchAssoc('SELECT * FROM user_sources 
        WHERE user_id = ? AND source_id = ?', array($user['ID'], $source_id));

    //User don't have this related.
    if (!$us) {
      $app['db']->executeQuery('INSERT INTO user_sources(user_id, source_id) 
          VALUES (?,?)', array($user['ID'], $source_id)
      );
    }
    
    return true;
  }

  /**
   * Gets all Subscriptions
   * 
   * @global \Reader\Model\type $app
   * @return Array
   */
  public static function getAll() {
    global $app;
    $user = $app['session']->get('user');
    return $app['db']->fetchAll('SELECT * FROM source s 
      INNER JOIN user_sources us ON s.id = us.source_id 
      WHERE us.user_id = ?
      AND s.enabled = "true"  
      ORDER BY s.name', array($user['ID']));
    
  }

  /**
   * Deletes a Subscription
   * 
   * @global \Silex\Application $app
   * @param Integer $source_id
   * 
   * @return True on Success
   */
  public static function delete($source_id) {
    global $app;
    $user = $app['session']->get('user');
    $app['db']->executeQuery('DELETE FROM user_sources WHERE user_id = ? AND source_id = ?', array($user['ID'], $source_id));  
    return true;
  }

  
  /**
   * Adds a Subscription
   * 
   * @global \Silex\Application $app
   * @param Array Data
   * 
   * @return Array The new Record or String with message
   */
  public static function add($name, $url) {
    global $app;
    $user = $app['session']->get('user');

    $source = $app['db']->fetchAssoc('SELECT * FROM source WHERE url = ?', array($url));  
    
    if (!$source) {
      $app['db']->executeQuery('INSERT INTO source(name, url) VALUES(?, ?)', array($name, $url));
      $sourceId = $app['db']->lastInsertId();
    } else {
      $sourceId = $source['id'];
    }

    $related = $app['db']->fetchAssoc('SELECT * FROM user_sources WHERE user_id = ? AND source_id = ?', 
            array($user['ID'], $sourceId));  
    
    if (!$related) {
      $app['db']->executeQuery('INSERT INTO user_sources(user_id, source_id) VALUES(?, ?)', 
              array($user['ID'], $sourceId));
    } else {
      return 'Already in Sources';
    }
    
    $subscription = $app['db']->fetchAssoc('SELECT * FROM source s 
      INNER JOIN user_sources us ON s.id = us.source_id 
      WHERE us.user_id = ?
      AND s.id = ?
      AND s.enabled = "true"  
      ORDER BY s.name', array($user['ID'], $sourceId)); 
    
    return $subscription;
  }

  
}

