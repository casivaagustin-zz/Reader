<?php

namespace Reader\Controler;

class Post {

  /**
   * Index page, list all unreaded posts.
   * 
   * @global \Silex\Applicaton $app
   * @param Integer $page, Page Number, Optional, Default 0
   * @return HTML with the page
   */
  public function index($page = 0) {
    global $app;
    $post = new \Reader\Model\Post();
    $posts = $post->get($page);
    return $app['twig']->render('Post\list_unread.twig', array('posts'=> $posts));
  }

  /**
   * List all posts.
   * 
   * @global \Silex\Applicaton $app
   * @param Integer $page, Page Number, Optional, Default 0
   * @return HTML with the page
   */
  
  public function all($page = 0) {
    global $app;
    $post = new \Reader\Model\Post();
    $posts = $post->getAll($page);
    return $app['twig']->render('Post\list_all.twig', array('posts'=> $posts));
  }
  
  /**
   * Mark as readed the post.
   * 
   * @global \Silex\Applicaton $app
   * @param type $postId
   * @return String JSON Encoded
   */
  public function markAsRead($postId) {
    global $app;
    $response = \Reader\Model\Post::markAsRead($postId);
    return json_encode($response);
  }
  
}

