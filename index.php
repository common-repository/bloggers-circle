<?php

/*
  Plugin Name: Bloggers Circle
  Plugin URI: http://bloggers-circle.com/
  Description: Helps you develop your writting skills by allowing others to comment on your drafts before publishing.
  Version: 0.65.2
  Author: Justin Alexander
  Author URI: http://bloggers-circle.com/
  License: BSD
 */

if (!class_exists('BloggersCircle')) {

  class BloggersCircle {

    var $dev = false;
    var $name = 'BloggersCircle';
    var $tag = 'bloggers_circle';
    var $site_options = array();
    var $options = array();
    var $homeSite = "http://bloggers-circle.com/";
    var $APISite = "http://service.bloggers-circle.com/";
    var $tableName = "";

    function BloggersCircle() {
      if ($this->dev) {
        //$this->homeSite.='dev/';
        $this->APISite.='dev/';
        error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
      }
      if (is_admin ()) {
        //$this->message=array('title'=>'MyBloggersCircle','content'=>'hello world');
        global $wpdb, $blog_id, $current_user;
        if (!defined('ABSPATH')) {
          require_once('../wp-load.php');
        }
        require_once(ABSPATH . '/wp-includes/pluggable.php');
        get_currentuserinfo();

        if ($options = get_option($this->tag)) {

          $this->site_options = $options;
          if (isset($this->site_options[$current_user->ID]))
            $this->options = $this->site_options[$current_user->ID];
        }else {
          $this->options = array();
          add_option($this->tag, $this->options);
        }

        $this->tableName = $wpdb->prefix . $this->tag;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->tableName}'") != $this->tableName) {
          $sql = "
					CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
					  `id` char(32) COLLATE utf8_unicode_ci NOT NULL,
					  `postid` int(11) NOT NULL,
					  `text` text COLLATE utf8_unicode_ci NOT NULL,
					  `rating` int(11) DEFAULT NULL,
					  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					  PRIMARY KEY  (`id`),
					  KEY `postid` (`postid`),
					  KEY `rating` (`rating`),
					  KEY `created` (`created`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
                            ";
          require_once(ABSPATH . 'wp-includes/pluggable.php');
          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

          dbDelta($sql);
        }

        if (empty($this->options['nextUpdate']) || $this->options['nextUpdate'] * DEVTIME < time()) {
          if (!$this->fetchUpdates())
            return false;
        }

        wp_enqueue_script('jquery');
        
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.3/themes/smoothness/jquery-ui.css');
		  //add_thickbox();

        //star ratings
        $pluginPath = get_bloginfo('wpurl') . '/wp-content/plugins/bloggers_circle/';
        wp_enqueue_script('jquery-uistar',$pluginPath.'jstars/jquery.jstars.js',array('jquery'));
        wp_enqueue_style('jquery-uistar-style',$pluginPath.'jstars/jquery.jstars.css');
        wp_enqueue_style('bloggers_circle-style',$pluginPath.'admin.css');
        wp_enqueue_script("{$this->tag}_JS",$pluginPath.'admin.js',array('jquery','jquery-ui-dialog','jquery-uistar'));

        wp_localize_script("{$this->tag}_JS","{$this->tag}_global" , array('APISite'=>$this->APISite,
                                                                          'uid'=>$this->options['uid']));

        //add_action('admin_menu', array(&$this, 'admin_menu'));

        //add_action('admin_init', array(&$this, 'admin_init'));
        //add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);
        //define javascript
        //add_action('admin_footer', array(&$this,'admin_javascript'));
        add_action("wp_ajax_{$this->tag}_updatePoints", array(&$this, 'ajaxUpdatePoints'));
        add_action("wp_ajax_{$this->tag}_submitPost", array(&$this, 'ajaxsubmitPost'));
        add_action("wp_ajax_{$this->tag}_displayReview", array(&$this, 'ajaxdisplayReview'));
        add_action("wp_ajax_{$this->tag}_rateReview", array(&$this, 'ajaxrateReview'));


        /* Define the custom box */
        add_action('add_meta_boxes', array(&$this, 'add_custom_box'));
      }
    }

    function fetchUpdates() {
      global $wpdb;
      global $current_user;

      try {


        $params = array('siteurl' => get_bloginfo('wpurl'),
            'sitename' => get_option('blogname'),
            'adminemail' => get_option('admin_email'),
            'useremail' => $current_user->user_email,
            'userid' => $current_user->ID,
            'WPLANG' => defined('WPLANG') && strlen(trim(WPLANG))>0 ? WPLANG:$_SERVER['HTTP_ACCEPT_LANGUAGE'],
            'uid' => empty($this->options['uid']) ? '' : $this->options['uid']);

        $fetchURL = "{$this->APISite}status.php?" . http_build_query($params);
        $fetch = file_get_contents($fetchURL);
        //var_dump($fetch,$fetchURL);
//        var_dump($_SERVER);
        $opts = json_decode($fetch);
//                var_dump($fetchURL,$fetch,$opts);
        if (!empty($opts) && isset($opts->success) && $opts->success == true) {
          $this->options['uid'] = $opts->uid;
          $this->options['points'] = $opts->points;
          $this->options['pending'] = $opts->pending;
          $this->options['rating'] = $opts->rating;
          $this->options['networkSize'] = $opts->networkSize;
          $this->options['bL'] = $opts->bL;
          $this->options['supportPage'] = $opts->supportPage;
          $this->options['nextUpdate'] = time() + intval($opts->updateInterval);
          $this->site_options[$current_user->ID] = $this->options;
          update_option($this->tag, $this->site_options);
//					echo "<pre>";
//					var_dump($opts);
          if(isset($opts->reviews))
          foreach ($opts->reviews as $review) {
            $res = $wpdb->insert($this->tableName, array('id' => $review->id, 'postid' => $review->postid, 'text' => base64_decode($review->text)));
          }

          if (!empty($opts) && !empty($opts->message)) {
            $this->message = $opts->message;
          }
        }
      } catch (Exception $e) {
        return false;
      }
      return true;
    }

    function admin_menu() {
      add_submenu_page(
              'plugins.php',
              'Manage ' . $this->name,
              $this->name,
              'administrator',
              $this->tag,
              array(&$this, 'settings')
      );
    }

    function admin_init() {
      register_setting($this->tag . '_options', $this->tag);
    }

    function settings() {
      include_once('settings.php');
    }

    function plugin_row_meta($links, $file) {
      $plugin = plugin_basename(__FILE__);
      if ($file == $plugin) {
        return array_merge(
                $links,
                array(sprintf(
                            '<a href="plugins.php?page=%s">%s</a>',
                            $this->tag, __('Settings')
                ))
        );
      }
      return $links;
    }

    function add_custom_box() {
      $boxTitle = "Bloggers Circle";
      $sectionid = "{$this->tag}_sectionid";
      $textDomain = "{$this->tag}_textdomain";
      $position = 'side';
      $priority = 'high';
      add_meta_box($sectionid, __($boxTitle, $textDomain), array(&$this, 'inner_custom_box'), 'post', $position, $priority);
      add_meta_box($sectionid, __($boxTitle, $textDomain), array(&$this, 'inner_custom_box'), 'page', $position, $priority);
    }

    /* Prints the box content */


    function inner_custom_box() {
      $textDomain = "{$this->tag}_textdomain";
      global $wpdb, $post;
      $postid = $post->ID;
      $reviews = $wpdb->get_results("SELECT * FROM {$this->tableName} WHERE postid={$postid} AND (rating IS NULL OR rating > 0)", OBJECT);

      // Use nonce for verification
      wp_nonce_field(plugin_basename(__FILE__), "{$this->tag}_noncename");

      include(dirname(__FILE__) . '/adminbox.html.php');
    }

    function admin_javascript() {
      $ajaxurl = admin_url('admin-ajax.php');
      include(dirname(__FILE__) . '/js.inc.php');
    }

    function ajaxUpdatePoints() {
      $points = 20;
      echo json_encode(compact('points'));
      die();
    }

    function ajaxsubmitPost() {

      $success = false;
      $message = false;
      try {
        $post_ID = $_POST['post_ID'];
        $post_title = $_POST['post_title'];
        $post_type = $_POST['post_type'];
        $content = $_POST['content'];
        $post_name = $_POST['post_name'];
        $tags = $_POST['tags'];
        $rating = $_POST['rating'];
        $uid = $this->options['uid'];

        $result = self::do_post_request($this->APISite . "submitPost.php",
                        compact('post_ID', 'post_title', 'post_type', 'content', 'post_name', 'tags', 'rating', 'uid'));

        //var_dump($result);
        $result = json_decode($result);
        if ($result->success == true) {
          $this->fetchUpdates();
          $success = true;
        } else {
          echo json_encode($result);
          die();
        }

        if (is_object($result) && !empty($result->message)) {
          $message = $result->message;
        }
      } catch (Exception $e) {
        echo $e;
      }

      echo json_encode(compact('success', 'message'));
      die();
    }

    function ajaxdisplayReview() {
      global $wpdb;
      $success = false;
      $text = "Error loading review, try again" . $_POST['revid'];
      $revid = $_POST['revid'];
      $sql = "SELECT * FROM {$this->tableName} WHERE id='{$revid}'";
      try {

        $reviews = $wpdb->get_results($sql, OBJECT);
        if (!empty($reviews)) {
          $text = "";
          foreach ($reviews as $k => $review) {
            $text .= html_entity_decode($review->text);
            $success = true;
          }
        }
      } catch (Exception $e) {

      }
      echo json_encode(compact('success', 'text'));
      die();
    }

    function ajaxrateReview() {
      global $wpdb;
      $success = false;
      $error = null;
      try {
        $uid = $this->options['uid'];
        $revid = $id = $_REQUEST['revid'];
        $rating = $_REQUEST['rating'];


        $params = compact('uid', 'rating', 'revid');
        $fetchURL = "{$this->APISite}rateReview.php?" . http_build_query($params);
        $fetch = file_get_contents($fetchURL);
        $opts = json_decode($fetch);

        if (!empty($opts) && isset($opts->success) && $opts->success == true) {
          $wpdb->update($this->tableName, compact('rating'), compact('id'));
          $success = true;
        }else{
        	$error = compact('fetchURL','fetch');
        }
      } catch (Exception $e) {
        $success = false;
        $error = $e;
      }
      echo json_encode(compact('success', 'error'));
      die();
    }

    protected static function do_post_request($url, $data, $optional_headers = null) {
      $params = array('http' => array(
              'method' => 'POST',
              'content' => http_build_query($data)
              ));
      if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
      }
      $ctx = stream_context_create($params);

      $fp = fopen($url, 'rb', false, $ctx);
      if (!$fp) {
        throw new Exception("Problem with $url, $php_errormsg");
      }
      $response = @stream_get_contents($fp);
      if ($response === false) {
        throw new Exception("Problem reading data from $url, $php_errormsg");
      }
      return $response;
    }

  }

  $BloggersCircle = new BloggersCircle();
}
