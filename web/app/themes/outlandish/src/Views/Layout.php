<?php

namespace Outlandish\Website\Views;

use Outlandish\Website\Views\Rows\CallToActionRow;
use Outlandish\Wordpress\Routemaster\Oowp\View\ContainerView;
use Outlandish\Website\PostTypes\BasePost;
use Outlandish\Website\Views\Components\Metadata;
use Outlandish\Website\Views\Components\Navbar;
use Outlandish\Website\Views\Components\Footer;
use Outlandish\Website\Views\Components\CookieBanner;
use Outlandish\Website\Views\Components\BackToTop;

class Layout extends ContainerView
{
    /** @var string - HTML outputted by wp_nav_menu() */
    public $menuItems;

    /** @var string */
    public $cookieNotification;

    /** @var string */
    public $siteTitle;

    /** @var array */
    public $callsToAction;

    /** @var array */
    public $footerElements;

    public function render($args = [])
    {
        $postType = $this->post->postType();

        $bodyClasses = [
            is_front_page() ? 'front-page' : null,
            $postType,
            $postType . '-' . $this->post->ID,
            $postType . '-' . $this->post->post_name,
        ];

        if (is_user_logged_in()) {
            $bodyClasses[] = 'wp-logged-in';
        }

        if (is_admin_bar_showing()) {
            $bodyClasses[] = 'wp-admin-bar';
            $bodyClasses[] = 'no-customize-support';
        }

        $title = $this->post->title() . ' | ' . $this->theme->siteTitle();

        if ($this->post instanceof BasePost) {
            $title = $this->post->titleForMetadata();

            if (! $this->post->isHomepage()) {
                $title .= ' | ' . $this->theme->siteTitle();
            }
        }

        $metadata = new Metadata(
            $title,
            $this->post->robots(),
            ($this->post instanceof BasePost) ? $this->post->descriptionForMetadata() : '',
            ($this->post instanceof BasePost) ? $this->post->openGraphData() : [],
            ($this->post instanceof BasePost) ? $this->post->twitterCardType() : ''
        );

        $bodyClasses   = implode(' ', array_filter($bodyClasses));
        $navbar        = new Navbar($this->menuItems, $this->siteTitle);
        $callsToAction = new CallToActionRow($this->callsToAction);
        $footer        = new Footer($this->footerElements);
//        $backToTop     = new BackToTop();
        $cookieBanner  = $this->cookieNotification ? new CookieBanner($this->cookieNotification) : null;

        ?><!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
        <?php
        if (WP_ENV === 'production') {
            // Google Analytics usually goes here
        }
        $metadata->render();
        wp_head();
        ?>
</head>
<body class="<?php echo $bodyClasses; ?>">
        <?php $navbar->render(); ?>
    <main role="main">
        <?php
        if ($this->content) {
            $this->content->render();
        }
        ?>
    </main>
        <?php
        $callsToAction->render();
        $footer->render();
//        $backToTop->render();
        if ($cookieBanner) {
            $cookieBanner->render();
        }
        wp_footer();
        ?>
</body>
</html>
        <?php
    }
}
