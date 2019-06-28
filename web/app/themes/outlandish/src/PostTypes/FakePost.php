<?php

namespace Outlandish\Website\PostTypes;

/**
 * Package specific version of FakePost that returns null for all missing function calls
 *
 * Class FakePost
 * @package Outlandish\Website\PostTypes
 */
class FakePost extends \Outlandish\Wordpress\Oowp\PostTypes\FakePost
{

    public function __call($name, $arguments)
    {
        return null;
    }

    public static function __callStatic($name, $arguments)
    {
        return null;
    }

    public function breadcrumbTrail($includeSelf = true)
    {
        return BasePost::breadcrumbTrailForPost($this, $includeSelf);
    }
}
