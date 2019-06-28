<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

/**
 * Button that appears in the bottom-right corner
 * as you scroll down the page
 *
 * Displayed/hidden via some JavaScript
 *
 * Class BackToTop
 * @package Outlandish\Website\Views\Components
 */
class BackToTop extends RoutemasterOowpView
{

    public function render($args = [])
    {
        ?>
        <div class="to-top js-to-top d-sm-none" aria-hidden="true">
            <i class="far fa-angle-up"></i>
        </div>
        <?php
    }
}
