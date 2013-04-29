<?php

namespace Reader\Model;

class Post extends Model {

  /**
   * Gets Posts
   * 
   * @global \Silex\Application $app
   * @param Integer $page : Page Number, Optional, Default 1
   * @return Array of Posts
   */
  public function get($page = 0) {
    global $app;
    $user = $app['session']->get('user');
    
    $posts = $app['db']->fetchAll('SELECT p.id, p.title, p.link, p.body, p.date, s.id as source_id, s.name
      FROM (post p INNER JOIN source s ON p.source_id = s.id) 
        INNER JOIN user_sources us ON us.source_id = s.id
      WHERE us.user_id = ?
      AND p.id NOT IN (SELECT p.id FROM read as r WHERE r.post_id = p.id AND user_id = ?) 
      ORDER BY date DESC
      LIMIT ? OFFSET ?', array ($user['ID'], $user['ID'], self::PAGE_SIZE, ($page * self::PAGE_SIZE)));
    return $posts;
  }

  /**
   * Gets All Posts
   * 
   * @global \Silex\Application $app
   * @param Integer $page : Page Number, Optional, Default 1
   * @return Array of Posts
   */
  public function getAll($page = 0) {
    global $app;
    $user = $app['session']->get('user');
    
    $posts = $app['db']->fetchAll('SELECT p.id, p.title, p.link, p.body, p.date, s.id as source_id, s.name, r.date_time as readed
      FROM (post p INNER JOIN source s ON p.source_id = s.id) 
        INNER JOIN user_sources us ON us.source_id = s.id
        LEFT OUTER JOIN read r ON r.post_id = p.id AND r.user_id = us.user_id
      WHERE us.user_id = ?
      ORDER BY date DESC
      LIMIT ? OFFSET ?', array ($user['ID'], self::PAGE_SIZE, ($page * self::PAGE_SIZE)));
    return $posts;
  }


  /**
   * Mark a post as read
   * 
   * @global \Silex\Application $app
   * @param Integer $postId
   * @return Boolean
   */
  public static function markAsRead($postId) {
    global $app;
    $user = $app['session']->get('user');
    
    $markedAsRead = $app['db']->fetchAssoc('SELECT * FROM read 
        WHERE post_id = ? AND user_id = ?', 
        array($postId, $user['ID'])
            );
    
    if (!$markedAsRead) {
      $app['db']->executeQuery('INSERT INTO read(post_id, user_id, date_time) 
        VALUES(?,?,?)',
        array($postId, $user['ID'], time()));
    }

    return true;
  }

  /**
   * Imports Posts from a Feed.
   * 
   * @global \Silex\Application $app
   * 
   * @param Array $source : Source Record
   * @param \Feedtcher\Feed $feed : New Feed
   */
  static public function import($source, $feed){
    global $app;
    $imported = 0;
    if (self::haveNewPosts($source, $feed)) {
      $app['db']->executeQuery('UPDATE source 
          SET hash = ?, last_update = ? 
          WHERE id = ?', 
          array(
              $feed->getHash(), time(), $source['id'])
          );  

      foreach($feed as $entry) {
        $exists = $app['db']->fetchAssoc('SELECT 1 FROM post WHERE link = ?', array($entry->link));
        if (!$exists) {
          $app['db']->executeQuery('INSERT INTO 
            post(title, body, post_date, author, link, hash, source_id, date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)', 
              array(
                $entry->title,
                $entry->description,
                $entry->date,
                $entry->author,
                $entry->link,
                $feed->getHash(),
                $source['id'],
                $entry->date,
              )
          ); 
          $imported++;
        }
      }
    }
    return $imported;
  }

  /**
   * Check if the Feed is new or if it was already imported.
   * 
   * Checks the feed and the source hash, if these are different we have
   * new posts, if not we fetch the same things thant before so there is not new
   * posts.
   * 
   * @param Array $source
   * @param \Feedtcher\Feed $feed
   * @return Boolean
   */
  static private function haveNewPosts($source, $feed) {
    if ($feed->getHash() == $source['hash']) {
      return false;
    }
    return true;
  }
}

