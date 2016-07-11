<?php

namespace Grav\Plugin;

use Grav\Common\Data\Blueprints;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;

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
        $twig = $this->grav['twig'];
        $avatar = $this->config->get('plugins.aboutme.picture_src');
        $avatar = $this->grav['base_url_absolute'] . is_array($avatar) ? key($avatar) : $avatar;
        $twig->twig_vars['avatar'] = $this->config->get('plugins.aboutme.gravatar.enabled')
            ? $this->getGravatarUrl() : $this->grav['base_url'] . $avatar;
        
        $pages =  $this->config->get('plugins.aboutme.social_pages.pages');
        uasort($pages, function($a, $b) {
            return $a['position'] < $b['position'] ? -1 : $a['position'] == $b['position'] ? 0 : 1;
        });
        $twig->twig_vars['social_pages'] = $pages;
    }

    /**
     *
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
    * Get the profile picture based on the gravatar config
    **/
    private function getGravatarUrl()
    {
        $gravatar = $this->config->get('plugins.aboutme.gravatar');
        return '//www.gravatar.com/avatar/' . md5(strtolower(trim($gravatar['email']))) . '?s=' . $gravatar['size'];
    }

    public function onAssetsInitialized()
    {
        if ($this->config->get('plugins.aboutme.built_in_css')) {
            $this->grav['assets']->add('plugin://aboutme/assets/css/aboutme.css');
        }
        
        if ($this->config->get('plugins.aboutme.social_pages.use_font_awesome')) {
            $this->grav['assets']->add('plugin://aboutme/assets/css/font-awesome.min.css');
        }
    }
}
