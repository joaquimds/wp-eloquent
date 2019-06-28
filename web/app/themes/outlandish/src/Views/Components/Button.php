<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class Button extends RoutemasterOowpView
{
    /** @var string */
    protected $url;

    /** @var string */
    protected $label;

    public function __construct($url, $label)
    {
        parent::__construct(compact('url', 'label'));
    }

    public function render($modifier = '')
    {
        if ($modifier) {
            $modifier = 'btn--' . $modifier;
        }
        ?>
        <a class="btn mb-2 <?php echo $modifier; ?>" href="<?php echo $this->url; ?>" role="button">
            <?php echo $this->label; ?>
        </a>
        <?php
    }
}
