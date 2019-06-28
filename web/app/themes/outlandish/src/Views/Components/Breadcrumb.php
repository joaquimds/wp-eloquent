<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

/**
 * Class Breadcrumb
 * @package Outlandish\Website\Views\Components
 */
class Breadcrumb extends RoutemasterOowpView
{
    /** @var [] */
    protected $trail;

    public function __construct($trail)
    {
        parent::__construct(compact('trail'));
    }

    public function render($args = [])
    {
        $items = array_map(function ($item) {
            if (is_string($item)) {
                return "<span>$item</span>";
            } else {
                return "<a href=\"$item[0]\">$item[1]</a>";
            }
        }, $this->trail)
        ?>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <div class="container-fluid">
                <?php echo implode(' &gt; ', $items); ?>
            </div>
        </nav>
        <?php
    }
}
