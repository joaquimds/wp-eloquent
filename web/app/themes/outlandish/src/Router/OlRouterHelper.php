<?php

namespace Outlandish\Website\Router;

use Outlandish\Website\PostTypes\Page;
use Outlandish\Website\Helpers\ACF\Options;
use Outlandish\Website\PostTypes\FakePost;
use Outlandish\Website\PostTypes\RowBuilderPost;
use Outlandish\Website\Views\Components\Banner;
use Outlandish\Website\Views\Components\Breadcrumb;
use Outlandish\Website\Views\Pages\NotFoundPage;
use Outlandish\Website\Views\RowGroups\RowBuilderRows;
use Outlandish\Website\Views\Rows\WysiwygRow;
use Outlandish\Wordpress\Oowp\PostTypes\WordpressPost;
use Outlandish\Wordpress\Oowp\Views\OowpView;
use Outlandish\Wordpress\Oowp\WordpressTheme;
use Outlandish\Wordpress\Routemaster\Exception\RoutemasterException;
use Outlandish\Wordpress\Routemaster\Oowp\OowpRouterHelper;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;
use Outlandish\Wordpress\Routemaster\View\Renderable;

class OlRouterHelper extends OowpRouterHelper
{
    /**
     * @param RoutemasterOowpView|array $args
     *
     * @return OlLayoutResponse
     */
    public function createDefaultResponse($args = [])
    {
        if ( ! ($args instanceof Renderable || $args instanceof OowpView)) {
            $ex                = new RoutemasterException('Cannot create a default response without a OowpView or Renderable');
            $ex->allowFallback = false;
            throw $ex;
        }

        $response = new OlLayoutResponse($args);

        $menuArgs = [
            'container'       => '',
            'container_class' => '',
            'container_id'    => '',
            'depth'           => 1,
            'echo'            => false,
            'fallback_cb'     => false,
            'items_wrap'      => '%3$s', // return only <li> items, without the <ul> container
            'menu_class'      => '',
            'theme_location'  => 'header-menu'
        ];

        $response->view->menuItems           = wp_nav_menu($menuArgs);
        $response->view->siteTitle           = WordpressTheme::getInstance()->siteTitle();
        $response->view->cookieNotification  = Options::hasCookieBanner() ? Options::cookieMessage() : null;
        $response->view->callsToAction       = Options::footerCallsToAction();
        $response->view->footerElements      = static::prepareFooterElements(Options::footerElements());

        return $response;
    }

    /**
     * Creates a 'not found' response based on the not-found page (with sensible fallback)
     * @return OlLayoutResponse
     */
    public function createNotFoundResponse()
    {

        try {
            $post = $this->querySingle(
                ['name' => 'not-found', 'post_type' => Page::postType()],
                false
            );
        } catch (RoutemasterException $e) {
            // Return a FakePost if a Page
            // with the slug 'not-found' doesn't exist
            $post               = new FakePost();
            $post->post_title   = "Not Found";
            $post->post_content = "<p>Sorry, we can't seem to find what you were looking for.</p>";
            $post->setAsGlobal();
        }

        $notFoundPage = new NotFoundPage(
            new Banner($post->title()),
            new Breadcrumb($post->breadcrumbTrail()),
            new WysiwygRow($post->content()),
            $post instanceof RowBuilderPost ? new RowBuilderRows($post->getRowBuilderContent()) : null
        );

        return $this->createDefaultResponse($notFoundPage);
    }

    protected static function prepareFooterElements($footerElements)
    {
        foreach ($footerElements as &$element) {
            switch ($element['acf_fc_layout']) {
                case 'footer_nav' :
                    $footerMenuArgs = [
                        'container'       => '',
                        'container_class' => '',
                        'container_id'    => '',
                        'depth'           => 1,
                        'echo'            => false,
                        'fallback_cb'     => false,
                        'items_wrap'      => '%3$s', // return only <li> items, without the <ul> container
                        'menu_class'      => '',
                        'theme_location'  => $element['footer_menu--autofilled_options']
                    ];
                    $element['content'] = wp_nav_menu($footerMenuArgs );
                    break;
                case 'gravity_form':
                    if ($element['gravity_form_id'] && function_exists('gravity_form')) {
                        $element['content'] = gravity_form(
                            $element['gravity_form_id'],
                            false, // display title
                            false, // display description
                            false, // display inactive
                            null,  // field values
                            true,  // ajax
                            0,     // tabindex
                            false  // echo
                        );
                    }
                    break;
            }
        }

        return $footerElements;
    }
}
