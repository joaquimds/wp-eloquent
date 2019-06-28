<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class Navbar extends RoutemasterOowpView
{
    /** @var string - HTML outputted by wp_nav_menu() */
    protected $menuItems;

    /** @var string */
    protected $siteTitle;

    /** @var string */
    protected $logoSrc;

    /** @var SearchForm|null */
    protected $searchForm;

    /**
     * @param string $menuItems
     * @param string $siteTitle
     * @param string $logoSrc
     * @param bool $showSearch
     */
    public function __construct(
        $menuItems,
        $siteTitle,
        $logoSrc = '',
        $showSearch = true
    ) {
        parent::__construct(compact('menuItems', 'siteTitle', 'logoSrc'));
        $this->searchForm = $showSearch ? new SearchForm() : null;
    }

    /**
     * Markup is adapted from here: https://getbootstrap.com/docs/4.1/components/navs/
     *
     * @param bool $fixed
     */
    public function render($fixed = true)
    {
        ?>
        <header class="navbar-container">
            <div class="<?php echo $fixed ? "navbar fixed-top" : "navbar"; ?>">
                <div class="container-fluid">
                    <a href="/" class="navbar-brand">
                        <?php if ($this->logoSrc) : ?>
                            <img class="navbar-brand__image"
                                 src="<?php echo $this->logoSrc; ?>"
                                 alt="<?php echo $this->siteTitle; ?>"
                                 title="<?php echo $this->siteTitle; ?>"/>
                        <?php else : ?>
                            <?php echo $this->siteTitle; ?>
                        <?php endif; ?>
                    </a>
                    <button class="navbar-toggler collapsed"
                            type="button"
                            data-toggle="collapse"
                            data-target="#main-menu"
                            aria-controls="main-menu"
                            aria-expanded="false"
                            aria-label="Toggle navigation">
                        <span class="icon-bar icon-bar--top"></span>
                        <span class="icon-bar icon-bar--middle"></span>
                        <span class="icon-bar icon-bar--bottom"></span>
                    </button>
                    <div id="main-menu" class="collapse navbar-collapse">
                        <nav class="ml-auto">
                            <ul class="navbar-nav">
                                <?php echo $this->menuItems; ?>
                            </ul>
                        </nav>
                        <?php if ($this->searchForm) {
                            $this->searchForm->render('navbar');
                        } ?>
                    </div>
                </div>
            </div>
        </header>
        <?php
    }
}
