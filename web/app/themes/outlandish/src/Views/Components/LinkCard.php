<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

/**
 * TODO: use img elements rather than background images
 *
 * Class LinkCard
 * @package Outlandish\Website\Views\Components
 */
class LinkCard extends RoutemasterOowpView
{
    /** @var string */
    protected $url;

    /** @var string */
    protected $image;

    /** @var string */
    protected $title;

    /** @var string */
    protected $caption;

    public function __construct($url, $image, $title, $caption = '')
    {
        parent::__construct(compact('url', 'image', 'title', 'caption'));
    }

    public function render($label = '')
    {
        $modifier = '';
        $inlineImageStyle = "style=\"background-image:url($this->image)\"";
        if (!$this->image) {
            $modifier = 'link-card--no-image';
            $inlineImageStyle = '';
        }
        ?>
        <article>
            <a class="link-card <?php echo $modifier;?>" href="<?php echo $this->url; ?>">
                <div class="link-card__image" <?php echo $inlineImageStyle; ?>>
                    <?php if ($label) : ?>
                        <div class="link-card__label">
                            <?php echo $label; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h4 class="link-card__title"><?php echo $this->title; ?></h4>
                <?php if ($this->caption) : ?>
                    <p class="link-card__text">
                        <?php echo $this->caption; ?>
                    </p>
                <?php endif; ?>
            </a>
        </article>
        <?php
    }
}
