<?php

namespace Grav\Plugin;

require_once(__DIR__ . '/interfaces.php');
require_once(__DIR__ . '/backends/aboutMeUsers.php');
require_once(__DIR__ . '/backends/gravUsers.php');

use Grav\Common\Grav;

class authorDB {
    public $backend;
    public $backends;
    public $users;
    static $instance;
    
    /*
        __construct($config)
            $config[]: not used yet
        Class constructor.
                
    */    
    protected function __construct($config = []) {
        $grav = Grav::instance();
        // Fetch user list from taxonomy
        $users = $this->userList();
        
        // Register backends with current user list
        $this->backends['aboutme'] = aboutMeUsers::instance(['users' => $users]);
        $this->backends['grav'] = gravUsers::instance(['users' => $users]);
        
        // Set default remoteUserDB to fetch users with
        $backend = $grav['config']->get('plugins.aboutme.backend');
        $this->backend = isset($this->backends[$backend]) ? $this->backends[$backend] : 'grav';
    }
    
    /*
        instance($config) returns gravUsers
            $config: config passed to __construct() upon instantiation
        Returns the only active gravUsers instance (constructs it if needed)
    */
    public static function instance($config = []) {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    
    /*
        populate()
             $config: not used yet
        Saves
    */
    public function populate($config = []) {
        $grav = Grav::instance();
        
        $users = $this->userList();
        $this->backends['aboutme'] = aboutMeUsers::instance(['users' => $users]);
        $this->backends['grav'] = gravUsers::instance(['users' => $users]);
        
        $backend = $grav['config']->get('plugins.aboutme.backend');
        $this->backend = isset($this->backends[$backend]) ? $this->backends[$backend] : 'grav';
    }
    
    /*
        userList() returns Array(string)
            $config: not used yet
        Returns an array of the usernames found in the 'author' taxonomy
    */
    public function userList($config = []) {
        $grav = Grav::instance();
        $cache = $grav['cache'];
        // Get the active userlist from cache
        if (!$users = $cache->fetch('activeUsers')) {
            // If not in cache, generate from 'author' taxonomy
            $users = [];
            $taxonomy = $grav['taxonomy']->taxonomy();
            foreach($taxonomy['author'] as $author => $value) {
                $users[] = $author;
            }
            // Save in cache for further use
            $cache->save('activeUsers', $users);
        }
        return $users;
    }
}

?>
