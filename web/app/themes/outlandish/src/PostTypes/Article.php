<?php

namespace Outlandish\Website\PostTypes;

class Article extends BasePost
{
    public static function getRegistrationArgs()
    {
        $args = parent::getRegistrationArgs();
        $args['menu_icon'] = 'dashicons-testimonial';

        return $args;
    }

    public static function postTypeParentSlug()
    {
        return 'articles';
    }

    /**
     * Don't show the post title for articles
     *
     * @param bool $includeSelf
     *
     * @return array
     */
    public function breadcrumbTrail($includeSelf = false)
    {
        return static::breadcrumbTrailForPost($this, $includeSelf);
    }

    public static function getModel()
    {
        return null;
    }
}
