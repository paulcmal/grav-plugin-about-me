<?php

namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\File\CompiledYamlFile;

class gravUsers implements remoteUserDB {
    public $users;
    static $instance;
    
    /*
        __construct($config)
            $config[]:
                - users[] : list of usernames to generate with
        Class constructor.
                
    */    
    public function __construct($config = []) {
        $this->users = [];
        if (isset($config['users'])) {
            foreach($config['users'] as $author) {
                $this->users[$author] = $this->getUser($author);
            }
        }
    }
    
    /*
        instance($config) returns gravUsers
            $config: config passed to __construct() upon instantiation
        Returns the only active gravUsers instance (constructs it if needed)
    */
    public static function instance($config) {
        if (!isset(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }
    
    /*
        getUsers($config) returns Array(aboutMeUser)
            $config: not used yet
        Returns an array of aboutMeUser objects,
        One for all the authors gravUsers was constructed for.
    */
    public function getUsers($config = []) {
        return $this->users;
    }
    
    /*
        getUser($identifier, $config) returns aboutMeUser or null
            $identifier: user unique id (string)
            $config: not used yet
        Returns an aboutMeUser object for the $identifier user,
        Generated from Grav user files (user/accounts).
    */    
    public function getUser($identifier = '', $config = []) {
        $grav = Grav::instance();
        // Grav only supports small-case usernames
        $identifier = strtolower($identifier);
        // Code taken from Grav\Common\User load(), simplified
        if (!$account = $grav['locator']->findResource('account://' . $identifier . YAML_EXT)) {
            // If the file does 
            return null;
        }
        $account_file = CompiledYamlFile::instance($account);
        $timestamp = $account_file->modified();
        $cache = $grav['cache'];
        $config = $grav['config'];
        // If data is not cached for the current file timestamp, generate it
        if (!$user = $cache->fetch("gravUser-$identifier-$timestamp")) {
            $content = $account_file->content();
            
            // Generate new user with user YAML info
            $array = [
                'name' => $identifier,
                'title' => isset($content['title']) ? $content['title'] : '',
                'descrition' => isset($content['description']) ? $content['description'] : '',
                'url' => $grav['base_url'] . '/author' . $config->get('system.param_sep') . $identifier,
                'social_pages' => []
            ];
            $user = new aboutMeUser($array);
            // Save object to cache
            $cache->save("gravUser-$identifier-$timestamp", $user);
        }
        return $user;
    }
    
    public function addUser($identifier = '', $config = []) {
        // TO BE IMPLEMENTED
    }
}
?>
