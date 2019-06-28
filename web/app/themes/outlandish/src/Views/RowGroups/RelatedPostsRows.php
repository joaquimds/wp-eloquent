<?php

namespace Outlandish\Website\Views\RowGroups;

use Outlandish\Website\Views\Rows\LinksRow;
use Outlandish\Wordpress\Oowp\PostTypeManager;
use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class RelatedPostsRows extends RoutemasterOowpView
{
    /** @var array - post arrays keyed by post type name */
    protected $relatedPostsGroupedByType;

    /** @var array - strings keyed by post type name */
    protected $rowTitles;

    public function __construct($relatedPostsGroupedByType)
    {
        parent::__construct(compact('relatedPostsGroupedByType'));

        // Generate titles for each row
        foreach (array_keys($relatedPostsGroupedByType) as $postType) {
            $postTypeClass = PostTypeManager::get()->getClassName($postType);
            $this->rowTitles[$postType] = 'Related ' . $postTypeClass::friendlyNamePlural();
        }
    }

    public function render($args = [])
    {
        foreach ($this->relatedPostsGroupedByType as $postType => $posts) {
            if (empty($posts)) {
                continue;
            }

            $args = ['title' => $this->rowTitles[$postType]];
            $relatedContentRow = new LinksRow($posts, false, $args);
            $relatedContentRow->render();
        }
    }
}
