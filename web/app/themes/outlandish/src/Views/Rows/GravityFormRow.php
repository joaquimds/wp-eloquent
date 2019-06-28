<?php

namespace Outlandish\Website\Views\Rows;

class GravityFormRow extends Row
{
    const ROW_CLASS = 'gform-row';

    /** @var int */
    protected $gravityFormId;

    /**
     * @param $gravityFormId
     * @param array $defaultArgs
     */
    public function __construct($gravityFormId, $defaultArgs = [])
    {
        parent::__construct($defaultArgs);
        $this->gravityFormId = $gravityFormId;
    }

    protected function renderContent($args = [])
    {
        ?>
        <div class="gform-row__body">
            <?php
            gravity_form(
                $this->gravityFormId,
                false, // display title
                false, // display description
                false, // display inactive
                null,  // field values
                true,  // ajax
                0,     // tabindex
                true   // echo
            );
            ?>
        </div>
        <?php
    }

    protected function renderButtons()
    {
        // No buttons, as weird to have these after the form submit button
    }
}
