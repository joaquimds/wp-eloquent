<?php

namespace Outlandish\Website\Helpers\ACF;

use Outlandish\Website\OutlandishTheme;

class Options
{

    /** @var array */
    protected static $options = [];

    /**
     * @param string $name ACF field name
     * @return bool|mixed
     */
    protected static function get($name)
    {
        if (!isset(self::$options[$name])) {
            self::$options[$name] = get_field($name, 'options');
        }
        return self::$options[$name];
    }

    /**
     * @param array $fields e.g. ['fieldName' => 'acf name']
     * @return object
     */
    protected static function getViaMap($fields)
    {
        return (object)array_map(function ($value) {
            return self::get($value);
        }, $fields);
    }

    /**
     * @return array
     */
    public static function footerCallsToAction()
    {
        return self::get('pre_footer') ?: [];
    }

    /**
     * @return array
     */
    public static function footerElements()
    {
        return self::get('footer_elements') ?: [];
    }

    /**
     * @return bool
     */
    public static function hasCookieBanner()
    {
        return self::get('show_cookie_banner');
    }

    /**
     * @return bool
     */
    public static function cookieMessage()
    {
        return apply_filters('the_content', self::get('cookie_message'));
    }

    /**
     * @param string $size
     * @return string (file URL)
     */
    public static function placeholderImage($size = 'medium_large')
    {
        $placeholder = self::get('placeholder_image');
        if (isset($placeholder['id'])) {
            return wp_get_attachment_image_url($placeholder['id'], $size);
        } else {
            return null;
        }
    }

    /**
     * @param string $size
     * @return string (file URL)
     */
    public static function defaultShareImage($size = OutlandishTheme::OG_IMAGE_SIZE_NAME)
    {
        $shareImage = self::get('default_share_image');
        if (isset($shareImage['id'])) {
            return wp_get_attachment_image_url($shareImage['id'], $size);
        } else {
            return null;
        }
    }
}
