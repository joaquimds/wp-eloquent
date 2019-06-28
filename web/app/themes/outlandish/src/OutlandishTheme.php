<?php

namespace Outlandish\Website;

use Outlandish\Website\PostTypes\Article;
use Outlandish\Website\PostTypes\BasePost;
use Outlandish\Website\PostTypes\MP;
use Outlandish\Website\PostTypes\Page;
use Outlandish\Website\Router\OlRouter;
use Outlandish\Wordpress\Eloquoowp\EloquentManager;
use Outlandish\Wordpress\Oowp\PostTypeManager;
use WP_CLI;

class OutlandishTheme
{
    // update this to force browser refreshes
    const THEME_ASSET_VERSION = '1.0.0';

    // These should be the recommended Facebook share image dimensions
    const OG_IMAGE_WIDTH = 1200;
    const OG_IMAGE_HEIGHT = 630;
    const OG_IMAGE_SIZE_NAME = 'og_share_card';

    public static function init()
    {
        self::registerNavMenus();
        self::registerPostTypes();
        self::registerConnections();
        self::setupEloquent();
        self::setupRouter();
        self::addImageSizes();
        self::addAdminScripts();
        self::enablePopoutImages();
        self::restrictWysiwygFormats();
        self::configureGravityForms();
        self::removeDashboardWidgets();
        self::addOptionsPages();
        self::displayTitleInRowHeader();
        self::hideCustomFieldsMenuInProduction();
        self::removeEmojiIcons();
        self::addParentClassToNav();
        self::removeRedundantTitlesFromNav();
        self::loadRowBuilderPostTypeSelectOptions();
        self::loadFooterMenuSelectOptions();
        self::addAcfPageSlugLocationRule();
        self::registerCommands();
    }

    protected static function registerNavMenus()
    {
        add_action('init', function () {
            register_nav_menu('header-menu', __('Header Menu'));
            // The footer builder allows up to four elements
            // Add four footer nav menu locations so all four can be menus
            for ($i = 1; $i <= 4; $i++) {
                register_nav_menu("footer-$i", __("Footer Menu $i"));
            }
        });
    }

    protected static function registerPostTypes()
    {
        add_action('init', function () {
            PostTypeManager::get()->registerPostTypes([
                Page::class,
                Article::class,
                MP::class
            ]);
        });
    }

    /**
     * Register connections between post types.
     */
    protected static function registerConnections()
    {
        add_action('init', function () {
            Article::registerConnection(Article::postType(), [
                'sortable'    => 'any',
                'cardinality' => 'many-to-many',
                'title'       => 'Related Articles'
            ]);
        });
    }

    protected static function setupEloquent()
    {
        init_eloquoowp([
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'prefix'    => 'el_'
        ]);
        EloquentManager::registerModels([
            Models\MP::class
        ]);
    }

    protected static function setupRouter()
    {
        add_action('init', function () {
            /** @var OlRouter $router */
            $router = OlRouter::getInstance();
            $router->setup();
        });
    }

    /**
     * Defines custom image sizes and resizing/cropping behaviour
     */
    protected static function addImageSizes()
    {
        add_action('init', function () {
            // always keep the top of images, when a vertical (hard) crop is
            // necessary, for more predictable cropping behaviour
            $hardCropSettings = ['center', 'top'];

            add_image_size('hero', 1920, 1024, $hardCropSettings);
            // don't crop images that are inserted into the wysiwyg body, e.g. popout
            add_image_size('popout', 1920, 1024, false);
            // specific size for open graph share cards
            add_image_size(static::OG_IMAGE_SIZE_NAME, static::OG_IMAGE_WIDTH, static::OG_IMAGE_HEIGHT,
                $hardCropSettings);
        });
    }

    /**
     * Enqueues styles and scripts to restrict post title length in WP backend (on a per-post-type basis), and improve
     * the content row UI.
     */
    protected static function addAdminScripts()
    {
        add_action('admin_enqueue_scripts', function () {
            if (is_admin()) {
                $publicRoot = get_stylesheet_directory_uri() . '/public/';
                $p          = get_current_screen();

                $limits = [
                    Article::postType() => 72
                ];

                wp_enqueue_style('ol-admin', $publicRoot . 'admin.css', [], self::THEME_ASSET_VERSION, 'all');
                wp_enqueue_script('ol-admin', $publicRoot . 'admin.js', ['jquery'], self::THEME_ASSET_VERSION, true);
                // configure the script with the current post type, and the desired title length limits for each post type
                wp_localize_script('ol-admin', 'restrict_title_length', [
                    'post_type' => $p->post_type,
                    'limits'    => $limits
                ]);
            }
        });
    }

    /**
     * Parses post content to modify images with the appropriate size so that they appear wider than the main content
     */
    protected static function enablePopoutImages()
    {
        // use the existing 'hero title' size to indicate that the image should pop out of the column width constraint
        $popoutImageSize = 'popout';

        // don't hardcode sizes on caption wrappers
        add_filter('img_caption_shortcode_width', function () {
            return null;
        });

        // replace default wordpress handling of captioned images when showing as a 'popout' image
        add_filter('img_caption_shortcode', function ($ignore, $attr, $content) use ($popoutImageSize) {
            $popout = preg_match('/<img [^>]+size-' . $popoutImageSize . '[^>]+>/', $content);

            if ( ! $popout) {
                return '';
            }

            $atts = shortcode_atts(array(
                'id'      => '',
                'align'   => 'alignnone',
                'width'   => '',
                'caption' => '',
                'class'   => '',
            ), $attr, 'caption');

            $atts['width'] = (int)$atts['width'];
            if ($atts['width'] < 1 || empty($atts['caption'])) {
                return $content;
            }

            if ( ! empty($atts['id'])) {
                $atts['id'] = 'id="' . esc_attr(sanitize_html_class($atts['id'])) . '" ';
            }

            $class = implode(' ', array_filter(['wp-caption', $atts['align'], $atts['class'], 'popout']));

            if (current_theme_supports('html5', 'caption')) {
                $outer = 'figure';
                $inner = 'figcaption';
            } else {
                $outer = 'div';
                $inner = 'p';
            }

            $html = '<' . $outer . ' ' . $atts['id'] . 'class="' . esc_attr($class) . '">'
                    . do_shortcode($content) . '<' . $inner . ' class="wp-caption-text">' . $atts['caption']
                    . "</{$inner}></{$outer}>";

            return $html;
        }, 10, 3);

        // add 'popout' size as an option
        add_filter('image_size_names_choose', function ($sizes) use ($popoutImageSize) {
            $sizes[$popoutImageSize] = 'Popout';

            return $sizes;
        }, 10, 1);

        // add 'popout' class to all images with hero_image size
        add_filter('the_content', function ($content) use ($popoutImageSize) {
            if ( ! preg_match_all('/<img [^>]+>/', $content, $matches)) {
                return $content;
            }

            $find = 'size-' . $popoutImageSize;
            foreach ($matches[0] as $image) {
                $newImage = str_replace($find, $find . ' popout', $image);
                if ($newImage !== $image) {
                    $content = str_replace($image, $newImage, $content);
                }
            }

            return $content;
        }, 100, 1); // high priority value to be executed later
    }

    /**
     * restrict available formatting in dropdown in wysiwygs
     */
    protected static function restrictWysiwygFormats()
    {
        add_filter('tiny_mce_before_init', function ($arr) {
            $allFormats = [
                'Paragraph'    => 'p',
                // 'Heading 1' => 'h1', // remove H1
                'Heading 2'    => 'h2',
                'Heading 3'    => 'h3',
                'Heading 4'    => 'h4',
                'Heading 5'    => 'h5',
                'Heading 6'    => 'h6',
                'Preformatted' => 'pre'
            ];

            $formats = [];

            foreach ($allFormats as $key => $value) {
                $formats[] = "{$key}={$value}";
            }

            $arr['block_formats'] = implode(';', $formats);

            return $arr;
        });
    }

    /**
     * Configures and fixes various problems with gravityforms
     */
    protected static function configureGravityForms()
    {
        // make gravity forms wait until after jquery has loaded
        add_filter("gform_init_scripts_footer", function () {
            return true;
        });

        // make inline js wait until jquery has loaded
        add_filter('gform_cdata_open', function () {
            return 'document.addEventListener( "DOMContentLoaded", function() { ';
        });
        add_filter('gform_cdata_close', function () {
            return ' }, false );';
        });

        // prevent gravity forms using its own styling
        add_filter('default_option_rg_gforms_disable_css', function () {
            return true;
        });

        // replace spinner with prettier Font Awesome icon
        add_filter('gform_ajax_spinner_url', function ($image_src, $form) {
            return get_stylesheet_directory_uri() . '/public/img/spinner-regular.svg';
        }, 10, 2);

        // move the menu item down
        add_filter('gform_menu_position', function () {
            return 50;
        }, 10, 1);
    }

    /**
     * Removes undesired widgets from WordPress admin dashboard
     */
    protected static function removeDashboardWidgets()
    {
        add_action('admin_init', function () {
            // Quick draft and activity feed relate to built-in posts post type,
            // which we don't use
            remove_meta_box('dashboard_activity', 'dashboard', 'side');
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
            // Unnecessary gravity forms dashboard widget
            remove_meta_box('rg_forms_dashboard', 'dashboard', 'side');
            // Wordpress news feed
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
        });
        // The welcome message invites users to change their theme etc,
        // so not really desirable
        remove_action('welcome_panel', 'wp_welcome_panel');
        // Don't display the Gutenberg promo on the admin Dashboard
        remove_action('try_gutenberg_panel', 'wp_try_gutenberg_panel');
    }

    /**
     * Only administrators can see/edit these
     */
    protected static function addOptionsPages()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title' => 'Theme General Settings',
                'menu_title' => 'Theme Settings',
                'menu_slug'  => 'theme-general-settings',
                'capability' => 'administrator',
                'redirect'   => false
            ]);

            acf_add_options_sub_page([
                'page_title'  => 'Theme Footer Settings',
                'menu_title'  => 'Footer',
                'parent_slug' => 'theme-general-settings',
                'capability'  => 'administrator',
            ]);
        }
    }

    /**
     * Shows the title of a content row in its header when editing a post
     *
     * This is useful when the rows are collapsed
     */
    protected static function displayTitleInRowHeader()
    {
        add_filter(
            'acf/fields/flexible_content/layout_title/name=rows',
            function ($title) {
                if ($text = get_sub_field('title')) {
                    $title .= " &dash; <strong>$text</strong>";
                }

                return $title;
            },
            10,
            4
        );
    }

    /**
     * Custom fields should not be edited in the DB in production
     *
     * This means custom field editing options are hidden from clients,
     * even if they have an admin account
     */
    protected static function hideCustomFieldsMenuInProduction()
    {
        if (WP_ENV === 'production') {
            add_filter('acf/settings/show_admin', function () {
                return false;
            });
        }
    }

    /**
     * Remove Emoji Icons
     *
     * https://www.gavick.com/blog/removing-wordpress-emoji
     */
    protected static function removeEmojiIcons()
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }

    /**
     * Adds a class to the current post's parent nav item
     */
    protected static function addParentClassToNav()
    {
        add_filter('nav_menu_css_class', function ($classes, $item) {
            global $post;

            /** @var BasePost $oowpPost */
            $oowpPost = BasePost::createWordpressPost($post);
            $parent   = $oowpPost->parent();

            if ($parent && ! $parent->isHomepage()) {
                $menuItemPermalink = $item->url;

                if ($parent->permalink() === $menuItemPermalink) {
                    $classes[] = 'current-post-parent';
                }
            }

            return array_unique($classes);
        }, 10, 2);
    }

    /**
     * Remove title attributes from nav items when identical to the label
     */
    protected static function removeRedundantTitlesFromNav()
    {
        add_filter('nav_menu_link_attributes', function ($atts, $navItem) {
            if ($atts['title'] === $navItem->title) {
                unset($atts['title']);
            }

            return $atts;
        }, 10, 2);
    }

    /**
     * Populate the options for the RowBuilder post type select field
     */
    protected static function loadRowBuilderPostTypeSelectOptions()
    {
        if ( ! is_admin()) {
            return;
        }

        add_filter('acf/load_field/name=post_type--autofilled_options', function ($field) {
            $excludedPostTypes = ['attachment'];

            $postTypes = get_post_types(array(
                'public' => true,
            ), 'objects');

            $field['choices'] = [];

            foreach ($postTypes as $postType) {
                $name  = $postType->name;
                $label = $postType->labels->singular_name;

                if (in_array($name, $excludedPostTypes)) {
                    continue;
                }

                $field['choices'][$name] = $label;
            }

            return $field;
        });
    }

    /**
     * Populate the options for the Footer builder's menu select field
     */
    protected static function loadFooterMenuSelectOptions()
    {
        if ( ! is_admin()) {
            return;
        }

        add_filter('acf/load_field/name=footer_menu--autofilled_options', function ($field) {
            $navMenus = get_registered_nav_menus();

            $field['choices'] = array_filter($navMenus, function ($location) {
                if (strpos($location, 'footer') === false) {
                    return false;
                }

                if ( ! has_nav_menu($location)) {
                    return false;
                };

                return true;
            }, ARRAY_FILTER_USE_KEY);

            return $field;
        });
    }

    /**
     * Lets you apply an ACF field group to a specific page via its slug, rather than its ID.
     *
     * Useful if pages have different IDs between environments.
     *
     * More info on custom ACF location rules: https://www.advancedcustomfields.com/resources/custom-location-rules/
     */
    protected static function addAcfPageSlugLocationRule()
    {
        // Add the option
        add_filter('acf/location/rule_types', function ($choices) {
            $choices['Page']['page_slug'] = 'Page slug (i.e. post_name)';

            return $choices;
        });

        // Populate the choices
        add_filter('acf/location/rule_values/page_slug', function () {
            $choices = [];
            foreach (Page::fetchAll()->posts as $page) {
                $choices[$page->post_name] = $page->post_name;
            }

            return $choices;
        });

        // Matching rule
        add_filter('acf/location/rule_match/page_slug', function ($match, $rule, $options) {
            // This check is necessary because Options pages don't have post ID
            if (! empty($options['post_id'])) {
                $currentPostId = $options['post_id'];
                $matchSlug     = $rule['value'];
                $matchPageId   = Page::fetchBySlug($matchSlug)->ID ?? -1;

                if ($rule['operator'] == "==") {
                    $match = $matchPageId == $currentPostId;
                } elseif ($rule['operator'] == "!=") {
                    $match = $matchPageId != $currentPostId;
                }
            }

            return $match;
        }, 10, 3);
    }

    protected static function registerCommands()
    {
        $migrateEloquent = function() {
            Models\MP::createTable();
        };
        if (class_exists('WP_CLI')) {
            try {
                WP_CLI::add_command('eloquent:migrate', $migrateEloquent);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }
}
