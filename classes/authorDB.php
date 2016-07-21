<?php

namespace Grav\Plugin;

require_once(__DIR__ . '/interfaces.php');
require_once(__DIR__ . '/backends/aboutMeUsers.php');
require_once(__DIR__ . '/backends/gravUsers.php');

use Grav\Common\Grav;

class authorDB {
    public $backend;
    public $backends;
    static $instance;
    
    protected function __construct() {
        $this->populate();
    }
    
    // Allow only one instance()
    public static function instance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    
    public function populate() {
        $grav = Grav::instance();
        $config = $grav['config'];
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
        
        $this->backends['aboutme'] = aboutMeUsers::instance(['users' => $users]);
        $this->backends['grav'] = gravUsers::instance(['users' => $users]);
        
        $backend = $config->get('plugins.aboutme.backend');
        $this->backend = isset($this->backends[$backend]) ? $this->backends[$backend] : 'grav';
    }
}

?>
