<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class CallToAction extends RoutemasterOowpView
{
    /** @var string */
    protected $title;

    /** @var string */
    protected $content;

    /** @var string */
    protected $button_url;

    /** @var string */
    protected $button_label;

    public function render($args = [])
    {
        ?>
        <div class="cta">
            <div class="cta__body">
                <?php if ($this->title) : ?>
                    <h3 class="cta__title"><?php echo $this->title; ?></h3>
                <?php endif; ?>

                <?php if ($this->content) : ?>
                    <div class="cta__content"><?php echo $this->content; ?></div>
                <?php endif; ?>

                <?php if ($this->button_url && $this->button_label) : ?>
                    <?php (new Button($this->button_url, $this->button_label))->render(); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
