<?php

namespace Outlandish\Website\Views\RowGroups;

use Outlandish\Website\PostTypes\RowBuilderPost;
use Outlandish\Website\Views\Rows\GravityFormRow;
use Outlandish\Website\Views\Rows\HeroImageRow;
use Outlandish\Website\Views\Rows\ImageRow;
use Outlandish\Website\Views\Rows\LinksRow;
use Outlandish\Website\Views\Rows\WysiwygColumnsRow;
use Outlandish\Website\Views\Rows\WysiwygRow;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class RowBuilderRows extends RoutemasterOowpView
{
    /** @var array */
    protected $rows;

    /**
     * @param array $rows
     */
    public function __construct($rows = [])
    {
        parent::__construct(compact('rows'));
    }

    public function render($args = [])
    {
        foreach ($this->rows as $row) {
            $rowView = null;
            $rowRenderArgs = [];

            switch ($row->acf_fc_layout) {
                case RowBuilderPost::ROW_TYPE_FEATURED_LINKS:
                case RowBuilderPost::ROW_TYPE_POST_TYPE_LINKS:
                    $rowView = new LinksRow(
                        $row->linked_items,
                        isset($row->display_excerpts) ? $row->display_excerpts : false,
                        (array)$row
                    );
                    break;
                case RowBuilderPost::ROW_TYPE_WYSIWYG:
                    $rowView = new WysiwygRow($row->wysiwyg, (array)$row);
                    break;
                case RowBuilderPost::ROW_TYPE_WYSIWYG_COLUMNS:
                    $rowView = new WysiwygColumnsRow($row->wysiwygColumns, (array)$row);
                    break;
                case RowBuilderPost::ROW_TYPE_HERO_IMAGE:
                    $rowView = new HeroImageRow(
                        $row->image,
                        $row->image_mobile,
                        $row->background_colour,
                        (array)$row
                    );
                    break;
                case RowBuilderPost::ROW_TYPE_IMAGE_ROW:
                    $rowView = new ImageRow($row->images, (array)$row);
                    break;
                case RowBuilderPost::ROW_TYPE_GRAVITY_FORM:
                    $rowView = new GravityFormRow($row->gravity_form_id, (array)$row);
                    break;
            }

            if ($rowView) {
                $rowView->render($rowRenderArgs);
            } else {
                echo 'TODO: ' . $row->acf_fc_layout . '<br />';
                print_r($row);
            }
        }
    }
}
