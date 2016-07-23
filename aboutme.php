<?php

/* TODO
    - remoteUserDB backends
        - gravUsers
            - let users edit their avatar
            - let users have a URL
                - if user URL is set, try to mf2 parse it
                - if no MF2, revert to user settings with url = author page and social_page web = url
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
        - templates
            - author
                - user page
            - authors
                - user list
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
    static $languages = ['en' => '', 'fr' => '', 'de' => ''];

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
        $config = $grav['config'];
        $taxonomies = $config->get('site.taxonomies');

        if (!isset($taxonomies['author'])) {
            $taxonomies[] = 'author';
            $config->set('site.taxonomies', $taxonomies);
        }

        if ($config->get('plugins.aboutme.enabled')) {
            $this->enable([
                'onTwigTemplatePaths'   => ['onTwigTemplatePaths', 0],
                'onTwigSiteVariables'   => ['onTwigSiteVariables', 0],
                'onAssetsInitialized'   => ['onAssetsInitialized', 0],
                'onPagesInitialized'    => ['onPagesInitialized', 0],
            ]);
        }
    }
    
    // WIP ALLOW FOR TRANSLATED MARKDOWN FOR USER PAGES
    // Will replace current translation strings
    
    public function findPage($template, $lang) {
        $file = new \SplFileInfo(__DIR__ . '/pages/' . $template . '.' . $lang . '.md');
        if (!$file->isFile()) {
            $file = new \SplFileInfo(__DIR__ . '/pages/' . $template . '.en.md');
            if (!$file->isFile()) {
                return null;
            }
        }
        return $file;
    }
    
    public function fetchAuthors($lang) {
        if ($file = $this->findPage('authors', $lang)) {
            // Create a new page with authors
            $page = new \Grav\Common\Page\Page;
            $page->init($file);
            $page->modifyHeader('title', Grav::instance()['language']->translate('PLUGIN_ABOUTME.AUTHOR_PAGES.LIST_TITLE'));
            
            // Set the new page as the current page
            $grav = Grav::instance();
            unset($grav['page']);
            $grav['page'] = $page;
        }
    }
    
    public function fetchAuthor($author, $lang) {
        // If user is in the userlist from taxonomy
        if (array_key_exists($author, authorDB::instance()->userList())) {
            // Check if author page exists for current language
            if ($file = $this->findPage('author', $lang)) {
            // create the search page
                $page = new \Grav\Common\Page\Page;
                $page->init($file);
                $page->modifyHeader('user', $author);
                $page->modifyHeader('title', Grav::instance()['language']->translate(['PLUGIN_ABOUTME.AUTHOR_PAGES.PAGE_TITLE', $author]));
                $grav = Grav::instance();
                unset($grav['page']);
                $grav['page'] = $page;
            }
        }
        // Else page will return 404 automatically
    }
    
    public function onPagesInitialized()
    {
        $grav = Grav::instance();
        $cache = $grav['cache'];
        $language = $grav['language'];
        
        $lang = $language->getLanguage();
        $lang = isset(static::$languages[$lang]) ? $lang : 'en';

        if (!$author_route = $cache->fetch('author_route_' . $lang)) {
            $author_route = $language->translate('PLUGIN_ABOUTME.AUTHOR_PAGES.ROUTE');
            $cache->save('author_route_' . $lang, $author_route);
        }
        
        // uri->path() returns URI without language code, so we may check against translation directly
        $route = explode('/', $grav['uri']->path());
        
        // Since route starts with /, element 0 is just an empty string
        if ($route[1] === $author_route) {
            if (count($route) == 2) {
                $this->fetchAuthors($lang);
            } elseif (count($route) > 2) {
                $this->fetchAuthor($route[2], $lang);
            }
        }
        // Here we changed the route
    }

    /**
     * Set variables for the template
     */
    public function onTwigSiteVariables()
    { 
        $authors = authorDB::instance();
        $twig = $this->grav['twig'];
        $twig->twig_vars['aboutme'] = $authors->backends['aboutme']->getUser();
        $twig->twig_vars['users'] = $authors->backend->getUsers();
        $twig->twig_vars['author_backends'] = $authors->backends;
        $header = $this->grav['page']->header();
        // TODO find a way to have always access to an authorship array in Twig
        $authors = isset($header->taxonomy['author']) ? (is_array($header->taxonomy['author']) ? $header->taxonomy['author'] : [$header->taxonomy['author']]) : [];
        $twig->twig_vars['authors'] = $authors;
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
        $config = Grav::instance()['config'];
        if ($config->get('plugins.aboutme.built_in_css')) {
            $theme_css = "plugin://aboutme/assets/css/aboutme_$(this->config->get('system.pages.theme')).css";
            $this->grav['assets']->add(stream_resolve_include_path($theme_css) ? $theme_css : "plugin://aboutme/assets/css/aboutme_antimatter.css");
        }

        if ($config->get('plugins.aboutme.social_pages.use_font_awesome')) {
            $this->grav['assets']->add('plugin://aboutme/assets/css/font-awesome.min.css');
        }
    }
}

