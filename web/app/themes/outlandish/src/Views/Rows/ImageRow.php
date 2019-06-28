<?php

namespace Outlandish\Website\Views\Rows;

class ImageRow extends Row
{
    const ROW_CLASS = 'image-row';

    /** @var array */
    protected $images;

    /**
     * @param array $images
     * @param array $defaultArgs
     */
    public function __construct($images = [], $defaultArgs = [])
    {
        parent::__construct($defaultArgs);
        $this->images = array_map(function ($image) {
            if (is_string($image)) {
                $image = [
                    'image' => $image
                ];
            }
            return $image;
        }, $images);
    }

    protected function renderContent($cover = false)
    {
        // images default to 'background-size: contain' if $cover is false
        $modifier = $cover ? 'image-row__image--cover' : '';
        ?>
        <div class="row">
            <?php foreach ($this->images as $image) : ?>
                <div class="mb-4 <?php echo static::getColumnClasses(4); ?>">

                    <?php if (!empty($image['url'])) : ?>
                        <a href="<?php echo $image['url'] ?>">
                    <?php endif; ?>

                        <div class="image-row__image <?php echo $modifier; ?>"
                             style="background-image:url(<?php echo $image['image']; ?>)"></div>

                    <?php if (!empty($image['url'])) : ?>
                        </a>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
