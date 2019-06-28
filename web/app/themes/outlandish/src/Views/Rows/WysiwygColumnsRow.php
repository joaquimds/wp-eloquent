<?php

namespace Outlandish\Website\Views\Rows;

class WysiwygColumnsRow extends Row
{
    const ROW_CLASS = 'wysiwyg wysiwyg--columns';

    /** @var array */
    protected $wysiwygColumns;

    public function __construct($wysiwygColumns = [], $defaultArgs = [])
    {
        parent::__construct($defaultArgs);
        $this->wysiwygColumns = $wysiwygColumns;
    }

    public function renderContent($args = [])
    {
        ?>
        <div class="row">
            <?php foreach ($this->wysiwygColumns as $content) : ?>
                <div class="mb-4 <?php echo static::getColumnClasses(count($this->wysiwygColumns)); ?>">
                    <?php echo $content; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

