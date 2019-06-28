<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class SearchForm extends RoutemasterOowpView
{

    public function render($modifier = '')
    {
        if ($modifier) {
            $modifier = 'search-form--' . $modifier;
        }
        ?>
        <form class="form-inline search-form <?php echo $modifier; ?> "
              method="GET"
              action="/search"
              role="search">
            <input name="s"
                   class="form-control search-form__input"
                   type="search"
                   placeholder="Search..."
                   aria-label="Search">
            <button class="btn search-form__submit"
                    type="submit">Search
            </button>
        </form>
        <?php
    }
}
