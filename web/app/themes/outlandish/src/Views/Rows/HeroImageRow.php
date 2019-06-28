<?php

namespace Outlandish\Website\Views\Rows;

use Outlandish\Website\Views\Components\ButtonGroup;

class HeroImageRow extends Row
{
    /** @var string */
    protected $image;

    /** @var string */
    protected $imageMobile;

    /** @var string */
    protected $backgroundColour;

    public function __construct(
        $image = null,
        $imageMobile = null,
        $backgroundColour = null,
        $defaultArgs = []
    ) {
        parent::__construct($defaultArgs);
        $this->image = $image;
        $this->imageMobile = $imageMobile ?: $image;
        $this->backgroundColour = $backgroundColour;
    }

    public function render($args = [])
    {
        ?>
        <section class="content-row hero" <?php echo $this->anchor; ?>>
            <?php if ($this->image) : ?>
                <div class="hero__image hero__image--desktop d-none d-sm-block"
                     style="background-image:url(<?php echo $this->image; ?>);"></div>
            <?php endif; ?>

            <?php if ($this->imageMobile) : ?>
                <div class="hero__image hero__image--mobile d-sm-none"
                     style="background-image:url(<?php echo $this->imageMobile; ?>);"></div>
            <?php endif; ?>

            <?php if ($this->backgroundColour) : ?>
                <div class="hero__background"
                    style="background-color:<?php echo $this->backgroundColour; ?>"></div>
            <?php endif; ?>

            <div class="hero__content">
                <div class="container-fluid">
                    <h2 class="hero__title display-4"><?php echo $this->title; ?></h2>
                    <div class="hero__text"><?php echo $this->subtitle; ?></div>
                    <?php if ($this->buttons) : ?>
                        <div class="hero__buttons mt-4">
                            <?php (new ButtonGroup($this->buttons))->render('light'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
