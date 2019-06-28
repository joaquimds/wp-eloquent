<?php

namespace Outlandish\Website\PostTypes;

use Outlandish\Website\OutlandishTheme;
use Outlandish\Wordpress\Eloquoowp\PostTypes\EloquentPost;
use Outlandish\Wordpress\Oowp\PostTypeManager;
use Outlandish\Wordpress\Oowp\PostTypes\WordpressPost;
use Outlandish\Wordpress\Oowp\WordpressTheme;
use Outlandish\Website\Helpers\ACF\Options;

/**
 * Base class for all post types
 */
abstract class BasePost extends EloquentPost
{
    /**
     * This implementation of permalink() enforces the post to have one of four structures:
     *
     * /%post_name%/
     * /%post_type%/%post_name%/
     * /%category%/%post_name%/
     * /%post_type%/%category%/%post_name%/
     *
     * To restore the original OOWP permalink structure, of:
     *
     * /%parent_permalink%/%post_name%/
     *
     * simply delete this method.
     *
     * @param bool $leaveName
     * @param bool $includePostType
     * @param bool $includeCategories
     * @return string
     */
    public function permalink($leaveName = false, $includePostType = true, $includeCategories = false)
    {
        $permalink = get_bloginfo('url') . '/';

        if ($this->isHomepage()) {
            return $permalink;
        }

        $postType = static::postType();

        if ($includePostType) {
            $permalink .= $postType . '/';
        }

        if ($includeCategories) {
            // inject the category into the url
            $categories = get_the_category($this->ID);

            if (count($categories) > 0) {
                $permalink .= $categories[0]->slug . '/';
            }
        }

        $permalink .= $leaveName ? "%{$postType}%" : $this->post_name;

        return $permalink . '/';
    }

    /*
     * IMAGE UTILITIES
     */

    /**
     * ACF image fields can return arrays, IDs or URL strings.
     * This works with the all three return types, but it's not
     * recommended to use URL strings, as $imageSize will be ignored.
     *
     * @param array|int|string $image - the return value from an ACF image field
     * @param string $imageSize
     * @return false|string - image URL on success, false on failure
     */
    protected static function acfImageToUrl($image, $imageSize = 'thumbnail')
    {
        if (isset($image['id'])) {
            $imageID = $image['id'];
        } elseif (is_numeric($image)) {
            $imageID = $image;
        } elseif (is_string($image)) {
            // Just return the URL string; $imageSize is ignored
            return $image;
        } else {
            return false;
        }

        return wp_get_attachment_image_url($imageID, $imageSize);
    }

    /**
     * @param string $fieldName
     * @param string $imageSize
     * @return false|string
     */
    protected function getImageFromACF($fieldName = 'featured_image', $imageSize = 'thumbnail')
    {
        return static::acfImageToUrl($this->metadata($fieldName), $imageSize);
    }

    /**
     * @param string $imageSize
     * @param array $attrs -- this is ignored
     * @return string
     */
    public function featuredImage($imageSize = 'hero', $attrs = [])
    {
        return $this->getImageFromACF('featured_image', $imageSize);
    }

    /**
     * Fallback on main featured image
     *
     * @param string $imageSize
     * @return string
     */
    public function featuredImageMobile($imageSize = 'large')
    {
        return $this->getImageFromACF('featured_image_mobile', $imageSize)
            ?: $this->getImageFromACF('featured_image', $imageSize);
    }

    /**
     * Fallback on main featured image, and then placeholder image
     *
     * @param string $imageSize
     * @return string
     */
    public function featuredImageThumbnail($imageSize = 'medium_large')
    {
        $image = $this->getImageFromACF('featured_image_thumbnail', $imageSize);

        if (!$image) {
            $image = $this->getImageFromACF('featured_image', $imageSize);
        }

        if (!$image) {
            $image = static::placeholderImage();
        }

        return $image;
    }

    /**
     * @param string $imageSize
     * @return string
     */
    public function placeholderImage($imageSize = 'medium_large')
    {
        return Options::placeholderImage($imageSize);
    }

    /*
     * END IMAGE UTILITIES
     */

    /*
     * METADATA UTILITIES
     */

    /**
     * Used for the page's tab title and for sharing and SEO metadata
     *
     * @return string
     */
    public function titleForMetadata()
    {
        $title = $this->metadata('page_title');

        if (!$title) {
            if ($this->isHomepage()) {
                $title = WordpressTheme::getInstance()->siteTitle();
            } else {
                $title = $this->title();
            }
        }

        return $title;
    }

    /**
     * Used for sharing and SEO metadata
     *
     * @return string
     */
    public function descriptionForMetadata()
    {
        return $this->metadata('page_description') ?: $this->excerpt(120);
    }

    /**
     * Returns Open Graph metadata used for Facebook, Twitter,
     * Google+ and LinkedIn sharing cards
     *
     * (Twitter has it's own metadata tags but falls back on
     * Open Graph when these tags are not present)
     *
     * TODO: Perhaps add article:author and article:publisher?
     * Facebook recommends having these where relevant
     *
     * @return array
     */
    public function openGraphData()
    {
        $openGraphData = [
            'og:url' => $this->permalink(),
            'og:type' => 'website',
            'og:title' => $this->titleForMetadata(),
            'og:description' => $this->descriptionForMetadata(),
            'og:image' => $this->ogImage(),
            'og:site_name' => WordpressTheme::getInstance()->siteTitle()
        ];

        // Adding og:image:width and og:image:height supposedly means
        // the image appears on Facebook the first time a post is shared
        // Without these, images area fetched asynchronously and not
        // displayed the first time a post is shared
        if (!empty($openGraphData['og:image'])) {
            $openGraphData = array_merge(
                $openGraphData,
                [
                    'og:image:width' => OutlandishTheme::OG_IMAGE_WIDTH,
                    'og:image:height' => OutlandishTheme::OG_IMAGE_HEIGHT
                ]
            );
        }

        return $openGraphData;
    }

    /**
     * See here for info on card types:
     * https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards
     *
     * @return string
     */
    public function twitterCardType()
    {
        return 'summary_large_image';
    }

    /**
     * Recommended dimensions for Facebook are currently 1200x630
     *
     * @return string
     */
    public function ogImage()
    {
        return $this->featuredImage(OutlandishTheme::OG_IMAGE_SIZE_NAME)
            ?: Options::defaultShareImage();
    }

    /*
    * END METADATA UTILITIES
    */


    /**
     * Get array of all parent posts, beginning
     * with highest parent
     *
     * @return array
     */
    public function parents()
    {
        $post = $this;
        $parents = [];

        while ($parent = $post->parent()) {
            $parents[] = $parent;
            $post = $parent;
        }

        return array_reverse($parents);
    }

    /*
     * HELPERS
     */

    /**
     * Can be used to add excerpt support to post types
     *
     * Would be called from self::getRegistrationArgs()
     * (but only after this override method has called
     * parent::getRegistrationArgs())
     *
     * @param $registrationArgs
     * @return mixed
     */
    public static function addExcerptSupport($registrationArgs)
    {
        if (empty($registrationArgs['supports'])) {
            $registrationArgs['supports'] = [];
        }

        $registrationArgs['supports'][] = 'excerpt';

        return $registrationArgs;
    }

    /*
     * END HELPERS
     */


    /*
     * RELATED CONTENT
     */

    /**
     * @return string[] - names of related post types
     */
    public static function getRelatedPostTypes()
    {
        return PostTypeManager::get()->getConnectedPostTypes(static::postType());
    }

    /**
     * Override this method if you want to exclude certain
     * post types or otherwise alter the related post behaviour
     *
     * @param array $postsToExclude - IDs of posts to exclude from return array
     *                       (if they have are already on the page, for example)
     * @return array - array of related posts, grouped/keyed by post type
     */
    public function getRelatedPostsGroupedByType($postsToExclude = [])
    {
        $relatedPostsGroupedByType = [];

        foreach (static::getRelatedPostTypes() as $postType) {
            $relatedPosts = $this->connected(
                $postType,
                false,
                ['post__not_in' => $postsToExclude]
            );

            $relatedPostsGroupedByType[$postType] = $relatedPosts->posts;
        }

        return $relatedPostsGroupedByType;
    }

    /*
     * END RELATED CONTENT
     */

    /**
     * Get breadcrumb trail
     *
     * @param bool $includeSelf
     *
     * @return array
     */
    public function breadcrumbTrail($includeSelf = true)
    {
        return static::breadcrumbTrailForPost($this, $includeSelf);
    }

    /**
     * Static method so that it can be used by classes which aren't children of BasePost,
     * e.g. FakePost
     *
     * @param BasePost|FakePost|WordpressPost $post
     * @param bool $includeSelf
     *
     * @return array
     */
    public static function breadcrumbTrailForPost($post, $includeSelf = true)
    {
        $trail = [
            [home_url(), 'Home']
        ];

        $parents = [];
        if (method_exists($post, 'parents')) {
            $parents = $post->parents();
        } elseif (method_exists($post, 'parent')) {
            $parents = [$post->parent()];
        }
        $parents = array_filter($parents);

        if ($parents) {
            $homeId = intval(get_option('page_on_front'));
            foreach ($post->parents() as $parent) {
                if ($parent->ID === $homeId) {
                    continue;
                }

                $trail[] = [$parent->permalink(), $parent->title()];
            };
        }

        if ($includeSelf) {
            $trail[] = $post->title();
        }

        return $trail;
    }
}
