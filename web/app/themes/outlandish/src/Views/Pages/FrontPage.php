<?php

namespace Outlandish\Website\Views\Pages;

use Outlandish\Website\Views\Components\Banner;
use Outlandish\Website\Views\RowGroups\RowBuilderRows;
use Outlandish\Website\Views\Rows\WysiwygRow;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class FrontPage extends RoutemasterOowpView
{
    const FULL_HEIGHT_BANNER = true;

    /** @var Banner */
    protected $banner;

    /** @var WysiwygRow */
    protected $mainContent;

    /** @var RowBuilderRows */
    protected $rowBuilderRows;

    public function __construct($banner, $mainContent, $rowBuilderRows)
    {
        parent::__construct(compact('banner', 'mainContent', 'rowBuilderRows'));
    }

    public function render($args = [])
    {
        $this->banner->render(static::FULL_HEIGHT_BANNER);
        $this->mainContent->render();
        $this->rowBuilderRows->render();
    }
}
