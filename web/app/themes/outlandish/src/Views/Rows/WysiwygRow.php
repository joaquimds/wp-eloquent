<?php

namespace Outlandish\Website\Views\Rows;

class WysiwygRow extends Row
{
    const ROW_CLASS = 'wysiwyg';

    /** @var string */
    protected $content;

    public function __construct($content = '', $defaultArgs = [])
    {
        parent::__construct($defaultArgs);
        $this->content = $content;
    }

    public function render($args = [])
    {
        if (!$this->content && !$this->title && !$this->subtitle && !$this->buttons) {
            return;
        }
        parent::render($args);
    }

    protected function renderContent($args = [])
    {
        ?>
        <div class="wysiwyg__content">
            <?php echo $this->content; ?>
        </div>
        <?php
    }
}
