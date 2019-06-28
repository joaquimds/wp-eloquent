<?php

namespace Outlandish\Website\Views\Rows;

use Outlandish\Website\Views\Components\ButtonGroup;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

abstract class Row extends RoutemasterOowpView
{
    const ROW_CLASS = '';

    /** @var string */
    public $title;

    /** @var string */
    public $subtitle;

    /** @var array */
    public $buttons;

    /** @var string */
    public $anchor;

    /**
     * All default args are optional
     *
     * @param array $defaultArgs
     */
    public function __construct(array $defaultArgs = [])
    {
        parent::__construct($defaultArgs);
        $this->anchor = $this->anchor ? 'id="' . $this->anchor . '"' : '';
    }

    /**
     * @param array $args
     */
    public function render($args = [])
    {
        ?>
        <section class="content-row <?php echo static::ROW_CLASS; ?>" <?php echo $this->anchor; ?>>
            <div class="container-fluid">
                <?php
                $this->renderTitle();
                $this->renderSubtitle();
                $this->renderContent($args);
                $this->renderButtons();
                ?>
            </div>
        </section>
        <?php
    }

    protected function renderTitle()
    {
        if ($this->title) { ?>
            <h2 class="content-row__title"><?php echo $this->title; ?></h2>
        <?php }
    }

    protected function renderSubtitle()
    {
        if ($this->subtitle) { ?>
            <div class="content-row__subtitle"><?php echo $this->subtitle; ?></div>
        <?php }
    }

    /**
     * Subclasses will usually override this instead of render()
     * @param array $args
     */
    protected function renderContent($args = [])
    {
    }

    protected function renderButtons()
    {
        if ($this->buttons) { ?>
            <div class="content-row__buttons">
                <?php (new ButtonGroup($this->buttons))->render(); ?>
            </div>
        <?php }
    }

    /**
     * Get the correct column classes for content with columns
     *
     * @param $columnCount
     * @return string
     */
    protected static function getColumnClasses($columnCount)
    {
        switch ($columnCount) {
            case 2:
                return 'col-12 col-sm-6 col-md-6';
            case 3:
                return 'col-12 col-sm-4 col-md-4';
            case 4:
                return 'col-12 col-sm-6 col-md-6 col-lg-3';
        }
        return '';
    }
}
