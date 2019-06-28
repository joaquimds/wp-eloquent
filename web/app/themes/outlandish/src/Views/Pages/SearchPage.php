<?php

namespace Outlandish\Website\Views\Pages;

use Outlandish\Website\Views\Components\Breadcrumb;
use Outlandish\Website\Views\Components\SearchForm;
use Outlandish\Website\Views\Rows\LinksRow;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class SearchPage extends RoutemasterOowpView
{
    /** @var Breadcrumb */
    protected $breadcrumb;

    /** @var string */
    protected $message;

    /** @var LinksRow|null */
    protected $resultsRow;

    public function __construct($breadcrumb, $message = '', $resultsRow = null)
    {
        parent::__construct(compact('breadcrumb', 'message', 'resultsRow'));
    }

    public function render($args = [])
    {
        ?>

        <?php $this->breadcrumb->render(); ?>

        <div class="container-fluid">
            <div class="content-row">
                <?php
                if ($this->resultsRow) {
                    $this->renderHasResults();
                } else {
                    $this->renderNoResults();
                }
                ?>
            </div>
        </div>

        <?php
    }

    protected function renderHasResults()
    {
        ?>

        <p><?php echo $this->message; ?></p>
        <?php $this->resultsRow->render(); ?>

        <?php
    }

    protected function renderNoResults()
    {
        ?>

        <p class="body-text"><?php echo $this->message; ?></p>
        <?php (new SearchForm())->render('results'); ?>

        <?php
    }
}
