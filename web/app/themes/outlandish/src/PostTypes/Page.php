<?php

namespace Outlandish\Website\PostTypes;

class Page extends RowBuilderPost
{
    // Sets $includePostType to false
    public function permalink($leaveName = false, $includePostType = false, $includeCategories = false)
    {
        return parent::permalink($leaveName, $includePostType, $includeCategories);
    }

    public static function getModel()
    {
        return null;
    }
}
