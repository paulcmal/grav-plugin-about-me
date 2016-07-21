<?php

/* TODO
    - remoteUserDB backends
        - gravUsers
            - let users edit their avatar
            - implement addUser to be used on taxonomy additions
    - remoteUserBackend backends
        - web
            - method for fetching remote author from URL
            - parse mf2, fallback to parsing twitter profile with twitter:site if set
        - XMPP
            - ask server for Jabber vCard
            - see linkmauve.fr for a JS implementation
    - CSS
        - fix antimatter integration
        - write other stylesheets?
    - templates
        - finish action templates (published/action)
    - caching
        - update authorDB cache when a new entry is found in taxonomy 'author'
        - update aboutMeUsers cache when about-me config is modified
*/

/*  DONE
    - author
        - generate Identicon if author doesn't have an avatar
        - pre-load inline/block template as variable for caching
    - backends
        - remoteUserDB backends
            - gravUsers
                - fecthes from Grav user accounts (user/accounts YAML files)
        - remoteUserBackend backends
            - aboutme config
                - fecthes from aboutme plugin config
    - caching
        - active authors are cached
            - generated from 'author' taxonomy
        - backends
            - gravUsers
                - each user entry is cached
*/

namespace Grav\Plugin;

require_once __DIR__ . '/classes/aboutMeUser.php';
require_once __DIR__ . '/classes/authorDB.php';

use Grav\Common\Data\Blueprints;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Grav;

/**
 * Class AboutMePlugin
 * @package Grav\Plugin
 */

class AboutMePlugin extends Plugin
{

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized'  => ['onPluginsInitialized', 0],
        ];
    }

    /**
     *
     */
    public function onPluginsInitialized()
    {
        if (true === $this->isAdmin()) {
            $this->active = false;
            return;
        }
        
        $grav = Grav::instance();
        $this->config = $grav['config'];
        $taxonomies = $this->config->get('site.taxonomies');
        if (!isset($taxonomies['author'])) {
            $taxonomies[] = 'author';
            $this->config->set('site.taxonomies', $taxonomies);
        }

        if ($this->config->get('plugins.aboutme.enabled')) {
            $this->enable([
                'onTwigTemplatePaths'   => ['onTwigTemplatePaths', 0],
                'onTwigSiteVariables'   => ['onTwigSiteVariables', 0],
                'onAssetsInitialized'   => ['onAssetsInitialized', 0]
            ]);
        }
    }

    /**
     * Set variables for the template
     */
    public function onTwigSiteVariables()
    { 
        $authors = authorDB::instance();
        $this->grav["twig"]->twig_vars['aboutme'] = $authors->backends['aboutme']->getUser();
        $this->grav["twig"]->twig_vars['authors'] = $authors->backend->getUsers();
        $this->grav["twig"]->twig_vars['author_backends'] = $authors->backends;
    }

    /**
     *
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onAssetsInitialized()
    {
        if ($this->config->get('plugins.aboutme.built_in_css')) {
            $theme_css = "plugin://aboutme/assets/css/aboutme_$(this->config->get('system.pages.theme')).css";
            $this->grav['assets']->add(stream_resolve_include_path($theme_css) ? $theme_css : "plugin://aboutme/assets/css/aboutme_antimatter.css");
        }

        if ($this->config->get('plugins.aboutme.social_pages.use_font_awesome')) {
            $this->grav['assets']->add('plugin://aboutme/assets/css/font-awesome.min.css');
        }
    }
}

