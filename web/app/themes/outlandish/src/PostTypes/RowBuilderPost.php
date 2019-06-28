<?php

namespace Outlandish\Website\PostTypes;

use Outlandish\Wordpress\Oowp\PostTypes\WordpressPost;

/**
 * Class RowBuilderPost
 * @package Outlandish\Website\PostTypes
 */
abstract class RowBuilderPost extends BasePost
{
    const ROW_TYPE_FEATURED_LINKS = 'featured_links';
    const ROW_TYPE_POST_TYPE_LINKS = 'post_type_links';
    const ROW_TYPE_WYSIWYG = 'wysiwyg';
    const ROW_TYPE_WYSIWYG_COLUMNS = 'wysiwyg_columns';
    const ROW_TYPE_HERO_IMAGE = 'hero_image';
    const ROW_TYPE_IMAGE_ROW = 'image_row';
    const ROW_TYPE_GRAVITY_FORM = 'gravity_form_row';

    public $linkedPostIds = [];

    /**
     * Gets all of the row builder rows, reshaping the data for
     * the relevant views, and populating any missing information
     *
     * @return array|string
     */
    public function getRowBuilderContent()
    {
        $rows = $this->getRows();
        $rows = $this->populatePostTypeLinksRows($rows);

        foreach ($rows as $row) {
            switch ($row->acf_fc_layout) {
                case static::ROW_TYPE_FEATURED_LINKS:
                case static::ROW_TYPE_POST_TYPE_LINKS:
                    if ($row->linked_items) {
                        $row->linked_items = WordpressPost::fetchAll([
                            'post__in' => $row->linked_items,
                            'orderby' => 'post__in'
                        ])->posts;
                    }
                    break;
                case static::ROW_TYPE_WYSIWYG:
                    $row->wysiwyg = apply_filters('the_content', $row->wysiwyg);
                    break;
                case static::ROW_TYPE_WYSIWYG_COLUMNS:
                    // flatten array and apply filters
                    $row->wysiwygColumns = array_map(function ($column) {
                        $columnContent = $column['wysiwyg_column'];
                        return apply_filters('the_content', $columnContent);
                    }, $row->wysiwyg_repeater);
                    break;
                case static::ROW_TYPE_IMAGE_ROW:
                    $row->images = array_map(function ($image) {
                        $image['image'] = static::acfImageToUrl($image['image'], 'medium_large');
                        return $image;
                    }, $row->images);
                    break;
                case static::ROW_TYPE_HERO_IMAGE:
                    $mainImage = $row->image;
                    $row->image = static::acfImageToUrl($mainImage, 'hero');
                    $row->image_mobile = static::acfImageToUrl($row->image_mobile ?: $mainImage, 'large');
                    break;
            }
        }

        return $rows;
    }

    protected function getRows()
    {
        $rows = $this->metadata('rows') ?: [];

        if ($rows) {
            // strip out any empty rows
            $rows = array_filter($rows, function ($row) {
                return !empty($row);
            });

            // the 'row' property is an array with only 1 item, so flatten it
            $rows = array_map(function ($row) {
                return (object)$row;
            }, $rows);

            $rows = static::sanitizeRows($rows);
        }

        return $rows;
    }

    /**
     * Fetches post IDs for post type links rows
     *
     * Also keeps track of posts added in all links rows,
     * updating $this->linkedPostIds as it goes
     *
     * @param $rows
     * @return mixed
     */
    protected function populatePostTypeLinksRows($rows)
    {
        foreach ($rows as &$row) {
            if ($row->acf_fc_layout === static::ROW_TYPE_FEATURED_LINKS) {
                $this->updateLinkedPostIds($row->linked_items);
                continue;
            }

            if ($row->acf_fc_layout === static::ROW_TYPE_POST_TYPE_LINKS) {
                if ($row->pinned_items) {
                    // Add the pinned items first, so they can't
                    // appear twice in this row
                    $this->updateLinkedPostIds($row->pinned_items);
                }

                // Just want the IDs for now
                // Hence use of WP Query rather than WordpressPost::fetchAll()
                $linkedItems = new \WP_Query([
                    'fields' => 'ids',
                    'post_type' => $row->{'post_type--autofilled_options'},
                    'posts_per_page' => $row->maximum ? ($row->maximum - count($row->pinned_items)) : -1,
                    'post__not_in' => $row->exclude_already_on_page ? $this->linkedPostIds : []
                ]);

                $row->linked_items = array_merge($row->pinned_items, $linkedItems->posts);

                if ($row->linked_items) {
                    $this->updateLinkedPostIds($row->linked_items);
                }
            }
        }
        return $rows;
    }

    /**
     * Sanitize data here, if necessary
     *
     * @param $rows
     * @return array
     */
    protected static function sanitizeRows($rows)
    {
        $shouldBeArrays = [
            'wysiwyg_repeater',
            'linked_items',
            'pinned_items',
            'images'
        ];

        $rows = array_map(function ($row) use ($shouldBeArrays) {
            // ACF is returning empty strings when
            // these fields are empty. We want empty arrays.
            foreach ($shouldBeArrays as $fieldName) {
                if (isset($row->$fieldName) && !is_array($row->$fieldName)) {
                    $row->$fieldName = [];
                }
            }

            return $row;
        }, $rows);

        return $rows;
    }

    protected function updateLinkedPostIds($ids)
    {
        $this->linkedPostIds = array_unique(array_merge($this->linkedPostIds, $ids));
    }
}
