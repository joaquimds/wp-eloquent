<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class ButtonGroup extends RoutemasterOowpView
{
    /** @var array */
    protected $buttons;

    public function __construct($buttons = [])
    {
        parent::__construct();

        // remove any invalid buttons
        $this->buttons = array_filter($buttons, function ($button) {
            return (!empty($button['button_url']) && !empty($button['button_label']));
        });
    }

    public function render($modifier = '')
    {
        if ($this->buttons) {
            ?>
            <div>
                <?php foreach ($this->buttons as $button) :
                    (new Button($button['button_url'], $button['button_label']))->render($modifier);
                endforeach ?>
            </div>
            <?php
        }
    }
}
