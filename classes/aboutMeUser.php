<?php

namespace Grav\Plugin;

use Grav\Common\Grav;

class aboutMeUser {
    public $name;
    public $url;
    public $avatar;
    public $title;
    public $description;
    public $social_pages;
    public $inline;
    public $block;

    public function __construct($array) {
        $this->name = isset($array['name']) ? $array['name'] : '';
        $this->url = isset($array['url']) ? $array['url'] : '';
        $this->avatar = isset($array['avatar']) ? $array['avatar'] : $this->genAvatar();
        $this->title = isset($array['title']) ? $array['title'] : '';
        $this->description = isset($array['description']) ? $array['description'] : '';
        $this->social_pages = isset($array['social_pages']) ? $array['social_pages'] : [];
        // Generate full-visibility inline and block templates for caching
        $this->inline = $this->inline();
        $this->block = $this->block();
    }
    
    public function inline() {
        return Grav::instance()['twig']->processTemplate('partials/author/inline.html.twig', ['author' => $this]);
    }
    
    public function block() {
        return Grav::instance()['twig']->processTemplate('partials/author/block.html.twig', ['author' => $this]);
    }
    
    public function genAvatar($config = []) {
        $identicon = new \Identicon\Identicon(new \Identicon\Generator\SvgGenerator());
        $imageDataUri = $identicon->getImageDataUri($this->name);
        return $imageDataUri;
    }
}
?>
