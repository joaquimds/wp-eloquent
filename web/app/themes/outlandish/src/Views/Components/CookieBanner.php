<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class CookieBanner extends RoutemasterOowpView
{
    /** @var string */
    protected $message;

    public function __construct($message)
    {
        parent::__construct(compact('message'));
    }

    public function render($args = [])
    {
        ?>
        <div class="cookie-banner js-cookie-banner">
            <div class="cookie-banner__message">
                <?php echo $this->message; ?>
                <button aria-label="Close"
                        title="Close cookie notification"
                        class="cookie-banner__button js-close-cookie-banner">&times;
                </button>
            </div>
        </div>
        <?php
    }
}
