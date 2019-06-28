<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class Banner extends RoutemasterOowpView
{
    /** @var string */
    protected $image;

    /** @var string */
    protected $title;

    /** @var string */
    protected $mobileImage;

    /** @var array */
    protected $buttons;

    public function __construct($title = '', $image = '', $mobileImage = '', $buttons = [])
    {
        parent::__construct(compact('title', 'image', 'mobileImage', 'buttons'));

        if ($this->image) {
            if (!$this->mobileImage) {
                $this->mobileImage = $this->image;
            }
        } else {
            $this->mobileImage = null;
        }
    }

    public function render($fullHeight = false)
    {
        if (!$this->image) {
            $fullHeight = false;
        }
        ?>
        <header class="banner <?php echo $fullHeight ? 'banner--full' : ''; ?>">
            <?php
            // only display images if we can do so for all screen sizes,
            // in order to avoid potentially confusing situations where there
            // are images for some screen sizes but not others
            if ($this->image && $this->mobileImage) : ?>
                <div class="banner__images">
                    <div class="banner__image banner__image--desktop d-none d-sm-block"
                         style="background-image:url(<?php echo $this->image; ?>);">
                        <div class="banner__gradient"></div>
                    </div>

                    <div class="banner__image banner__image--mobile d-sm-none"
                         style="background-image:url(<?php echo $this->mobileImage; ?>);">
                        <div class="banner__gradient"></div>
                    </div>
                </div>
            <?php else : ?>
                <div class="banner__background"></div>
            <?php endif; ?>

            <?php if ($this->title || $this->buttons) : ?>
                <div class="banner__content">
                    <div class="container-fluid">
                        <?php if ($this->title) : ?>
                            <h1 class="banner__title display-3">
                                <?php echo $this->title; ?>
                            </h1>
                        <?php endif; ?>

                        <?php if ($this->buttons) : ?>
                            <div class="banner__buttons">
                                <?php (new ButtonGroup($this->buttons))->render('light'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </header>
        <?php
    }
}
