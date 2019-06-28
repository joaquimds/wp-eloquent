<?php

namespace Outlandish\Website\Views\Rows;

use Outlandish\Website\Views\Components\LinkCard;
use Outlandish\Website\PostTypes\BasePost;

class LinksRow extends Row
{
    const ROW_CLASS = 'links-row';

    /** @var array */
    protected $linkedItems;

    /** @var bool */
    protected $showExcerpts;

    public function __construct(
        $linkedItems = [],
        $showExcerpts = false,
        $defaultArgs = []
    ) {
        parent::__construct($defaultArgs);
        $this->linkedItems = $linkedItems;
        $this->showExcerpts = $showExcerpts;
    }

    protected function renderContent($showLabels = false)
    {
        if (empty($this->linkedItems)) {
            return;
        }
        ?>
        <div class="row">
            <?php foreach ($this->linkedItems as $linkedPost) :
                /** @var BasePost $linkedPost */
                $linkCard = new LinkCard(
                    $linkedPost->permalink(),
                    $linkedPost->featuredImageThumbnail(),
                    $linkedPost->title(),
                    $this->showExcerpts ? $linkedPost->excerpt(120) : null
                );
                ?>
                <div class="mb-4 <?php echo static::getColumnClasses(4); ?>">
                    <?php
                    $linkCard->render($showLabels ? $linkedPost->friendlyName() : null);
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
