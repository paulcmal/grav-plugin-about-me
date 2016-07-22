<?php

namespace Grav\Plugin;

use Grav\Common\Grav;

require_once __DIR__ . '/../vendor/autoload.php';

/*
    aboutMeUser is a class of users expected to be described with at least a name, a URL and an avatar.
    If no avatar was passed as argument, one will be generated using https://github.com/yzalis/Identicon
*/
class aboutMeUser {
    public $name;
    public $url;
    public $avatar;
    public $title;
    public $description;
    public $social_pages;
    public $inline;
    public $block;


    /*
        __construct($config)
            $config[]:
                - name: username
                - url: userpage URL
                - avatar (optional): user avatar (if none, replaced by an identicon)
                - title (optional): user title
                - description (optional): user description
                - social_pages[] (optional): array of info about user's social pages
        Class constructor.
                
    */    
    public function __construct($config) {
        $array = $config;
        // Name and URL are the only two mandatory parameters
        if (!isset($array['name']) || !isset($array['url'])) {
            return null;
        }
        $this->name = $array['name'];
        $this->url = $array['url'];
        $this->avatar = isset($array['avatar']) ? $array['avatar'] : $this->genAvatar();
        $this->title = isset($array['title']) ? $array['title'] : '';
        $this->description = isset($array['description']) ? $array['description'] : '';
        $this->social_pages = isset($array['social_pages']) ? $array['social_pages'] : [];

        // Pre-load inline and block templates (with no hidden values) to be cached
        $twig = Grav::instance()['twig'];
        $this->inline = $twig->processTemplate('partials/author/inline.html.twig', ['author' => $this]);
        $this->block = $twig->processTemplate('partials/author/block.html.twig', ['author' => $this]);
    }
    
    /*
        genAvatar() returns string
            $config[]: not used yet
        Generate inline SVG Identicon with username as parameter
        Output can be directly embedded in an <img src="">
    */
    public function genAvatar($config = []) {
        $identicon = new \Identicon\Identicon(new \Identicon\Generator\SvgGenerator());
        return $identicon->getImageDataUri($this->name);
    }
}
?>
