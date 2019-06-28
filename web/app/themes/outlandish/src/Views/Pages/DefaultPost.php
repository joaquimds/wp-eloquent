<?php

namespace Outlandish\Website\Views\Pages;

use Outlandish\Website\Views\Components\Banner;
use Outlandish\Website\Views\Components\Breadcrumb;
use Outlandish\Website\Views\RowGroups\RelatedPostsRows;
use Outlandish\Website\Views\RowGroups\RowBuilderRows;
use Outlandish\Website\Views\Rows\WysiwygRow;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

/**
 * Generic view optimised for RowBuilderPosts.
 *
 * Class DefaultPage
 * @package Outlandish\Website\Views
 */
class DefaultPost extends RoutemasterOowpView
{
    /** @var Banner */
    protected $banner;

    /** @var Breadcrumb */
    protected $breadcrumb;

    /** @var WysiwygRow */
    protected $mainContent;

    /** @var RowBuilderRows|null */
    protected $rowBuilderRows;

    /** @var RelatedPostsRows|null */
    protected $relatedPostsRows;

    public function __construct(
        $banner,
        $breadcrumb,
        $mainContent,
        $rowBuilderRows = null,
        $relatedPostsRows = null
    ) {
        parent::__construct(compact(
            'banner',
            'breadcrumb',
            'mainContent',
            'rowBuilderRows',
            'relatedPostsRows'
        ));
    }

    public function render($args = [])
    {
        $this->banner->render();
        $this->breadcrumb->render();
        $this->mainContent->render();
        if ($this->rowBuilderRows) {
            $this->rowBuilderRows->render();
        }
        if ($this->relatedPostsRows) {
            $this->relatedPostsRows->render();
        }
    }
}
