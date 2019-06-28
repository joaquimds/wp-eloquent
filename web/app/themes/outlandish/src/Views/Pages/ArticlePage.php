<?php

namespace Outlandish\Website\Views\Pages;

use Outlandish\Website\Views\Components\Banner;
use Outlandish\Website\Views\Components\Breadcrumb;
use Outlandish\Website\Views\Components\ShareIcons;
use Outlandish\Website\Views\RowGroups\RelatedPostsRows;
use Outlandish\Website\Views\Rows\WysiwygRow;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class ArticlePage extends RoutemasterOowpView
{
    /** @var Banner */
    protected $banner;

    /** @var Breadcrumb */
    protected $breadcrumb;

    /** @var ShareIcons */
    protected $shareIcons;

    /** @var WysiwygRow */
    protected $mainContent;

    /** @var RelatedPostsRows */
    protected $relatedPostsRows;

    public function __construct(
        $banner,
        $breadcrumb,
        $shareIcons,
        $mainContent,
        $relatedPostsRows
    ) {
        parent::__construct(compact(
            'banner',
            'breadcrumb',
            'shareIcons',
            'mainContent',
            'relatedPostsRows'
        ));
    }

    public function render($args = [])
    {
        $this->banner->render();
        $this->breadcrumb->render();
        ?>
        <div class="container-fluid">
            <?php $this->shareIcons->render('top'); ?>
            <div class="js-sticky-share-container">
                <?php $this->shareIcons->render('sticky'); ?>
                <?php $this->mainContent->render(); ?>
            </div>
            <?php $this->shareIcons->render('bottom'); ?>
        </div>
        <?php
        $this->relatedPostsRows->render();
    }
}
