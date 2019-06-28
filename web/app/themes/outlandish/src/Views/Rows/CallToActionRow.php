<?php

namespace Outlandish\Website\Views\Rows;

use Outlandish\Website\Views\Components\CallToAction;

/**
 * TODO: implement a RowBuilder ACF for this?
 *
 * Class CallToActionRow
 * @package Outlandish\Website\Views\Rows
 */
class CallToActionRow extends Row
{
    /** @var array - with 1 or 2 elements */
    protected $callsToAction;

    public function __construct($callsToAction = [], $defaultArgs = [])
    {
        parent::__construct($defaultArgs);
        // Max of two calls to action
        $this->callsToAction = array_slice($callsToAction, 0, 2);
    }

    public function render($args = [])
    {
        if (empty($this->callsToAction)) {
            return;
        }

        ?>
        <section class="cta-row" <?php echo $this->anchor; ?>>
            <?php foreach ($this->callsToAction as $cta) :
                (new CallToAction($cta))->render();
            endforeach; ?>
        </section>
        <?php
    }
}
