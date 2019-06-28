<?php

namespace Outlandish\Website\PostTypes;

class MP extends BasePost
{
    public static function getModel()
    {
        return \Outlandish\Website\Models\MP::class;
    }

    public static function getRegistrationArgs()
    {
        $args = parent::getRegistrationArgs();
        $args['menu_icon'] = 'dashicons-businessman';

        return $args;
    }

    public static function friendlyName()
    {
        return 'MP';
    }

    public static function postType()
    {
        return 'mp';
    }

    public static function postTypeParentSlug()
    {
        return 'mps';
    }
}
