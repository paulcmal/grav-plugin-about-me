<?php

namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\File\CompiledYamlFile;

class gravUsers implements remoteUserDB {
    public $users;
    static $instance;
    
    public function __construct($config = []) {
        $this->users = [];
        if (isset($config['users'])) {
            foreach($config['users'] as $author) {
                $this->users[$author] = $this->getUser($author);
            }
        }
    }
    
    // Allow only one instance of gravUsers
    public static function instance($config) {
        if (!isset(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }
    
    public function getUsers($config = []) {
        return $this->users;
    }
    
    public function getUser($identifier = '', $config = []) {
        $grav = Grav::instance();
        // Grav only supports small-case usernames
        $identifier = strtolower($identifier);
        // Code taken from Grav\Common\User load(), simplified
        if (!$account = $grav['locator']->findResource('account://' . $identifier . YAML_EXT)) {
            // The user does not exist
            return null;
        }
        $account_file = CompiledYamlFile::instance($account);
        $timestamp = $account_file->modified();
        $cache = $grav['cache'];
        // If data is not cached for the current file timestamp, generate it
        if (!$user = $cache->fetch("gravUser-$identifier-$timestamp")) {
            $content = $account_file->content();
            
            // Generate new user with user YAML info
            $array = [
                'name' => $identifier,
                'title' => isset($content['title']) ? $content['title'] : '',
                'descrition' => isset($content['description']) ? $content['description'] : '',
                'url' => $grav['base_url'] . '/author/' . $identifier,
                'social_pages' => []
            ];
            $user = new aboutMeUser($array);
            // Save to cache
            $cache->save("gravUser-$identifier-$timestamp", $user);
        }
        return $user;
    }
}
?>
