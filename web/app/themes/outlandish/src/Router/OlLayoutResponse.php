<?php

namespace Outlandish\Website\Router;

use Outlandish\Website\OutlandishTheme;
use Outlandish\Website\Views\Layout;
use Outlandish\Wordpress\Routemaster\Oowp\Response\ContainerViewResponse;

class OlLayoutResponse extends ContainerViewResponse
{
    protected function preRender()
    {
        parent::preRender();

        // update jquery version
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://code.jquery.com/jquery-3.3.1.min.js', [], '3.3.1', true);

        $root    = get_stylesheet_directory_uri() . '/public/';
        $version = OutlandishTheme::THEME_ASSET_VERSION;
        wp_enqueue_script('app-js', "{$root}app.js", ['jquery'], $version, true);
        wp_enqueue_style('app-css', "{$root}app.css", [], $version, 'all');
    }


    protected function createContainerView($view)
    {
        return new Layout($view);
    }
}
