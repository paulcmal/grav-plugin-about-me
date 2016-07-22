<?php

namespace Grav\Plugin;

use Grav\Common\Grav;

class aboutMeUsers implements remoteUserBackend {
    public $user;
    static $instance;

    /*
        __construct($config)
            $config[]: not yet used
        Class constructor.
                
    */    
    public function __construct($config = []) {
        $grav = Grav::instance();
        
        $cache = $grav['cache'];
        // Need to refresh the cache 
        if (!$this->user = $cache->fetch('aboutMeUser')) {
            $config = $grav['config'];
            // Figure out avatar URL
            $avatar = $config->get('plugins.aboutme.picture_src');
            $avatar = ($grav['base_url'] ? $grav['base_url']: '/') . (is_array($avatar) ? key($avatar) : $avatar);
            // Generate social pages
            $pages =  $config->get('plugins.aboutme.social_pages.pages');
            uasort($pages, function($a, $b) {
                return $a['position'] < $b['position'] ? -1 : $a['position'] == $b['position'] ? 0 : 1;
            });
            // Generate user array
            $array = [
                'name' => $config->get('plugins.aboutme.name'),
                'title' => $config->get('plugins.aboutme.title'),
                'description' => $config->get('plugins.aboutme.description'),
                'url' => $grav['base_url_absolute'],
                'avatar' => $config->get('plugins.aboutme.gravatar.enabled')
                ? $this->getGravatarUrl() : $avatar,
                'social_pages' => $pages
            ];
            // Save user as aboutMeUser instance
            $this->user = new aboutMeUser($array);
            // Save to cache
            $cache->save('aboutMeUser', $this->user);
        }
    }
    
    /*
        instance($config) returns aboutMeUsers
            $config: config passed to __construct() upon instantiation
        Returns the only active aboutMeUsers instance (constructs it if needed)
    */
    public static function instance($config = []) {
        if (!isset(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }
    
    /*
        getUser() returns aboutMeUser
            $identifier: not used, as aboutme config only has one user
            $config: not yet used
        Returns aboutMeuser generated from plugin config
    */
    public function getUser($identifier = '', $config = []) {
        return $this->user;
    }
    
    /**
    * Get the profile picture based on the gravatar config
    **/
    private function getGravatarUrl()
    {
        $gravatar = Grav::instance()['config']->get('plugins.aboutme.gravatar');
        return '//www.gravatar.com/avatar/' . md5(strtolower(trim($gravatar['email']))) . '?s=' . $gravatar['size'];
    }
}
?>
