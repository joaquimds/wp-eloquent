<?php

namespace Outlandish\Website\Router;

use Outlandish\Website\PostTypes\Article;
use Outlandish\Website\PostTypes\BasePost;
use Outlandish\Website\PostTypes\FakePost;
use Outlandish\Website\PostTypes\Page;
use Outlandish\Website\PostTypes\RowBuilderPost;
use Outlandish\Website\Response\RssResponse;
use Outlandish\Website\Views\Components\Banner;
use Outlandish\Website\Views\Components\Breadcrumb;
use Outlandish\Website\Views\Components\ShareIcons;
use Outlandish\Website\Views\Pages\ArticlePage;
use Outlandish\Website\Views\Pages\DefaultPost;
use Outlandish\Website\Views\Pages\FrontPage;
use Outlandish\Website\Views\Pages\MPPage;
use Outlandish\Website\Views\Pages\SearchPage;
use Outlandish\Website\Views\RowGroups\RelatedPostsRows;
use Outlandish\Website\Views\RowGroups\RowBuilderRows;
use Outlandish\Website\Views\Rows\LinksRow;
use Outlandish\Website\Views\Rows\WysiwygRow;
use Outlandish\Wordpress\Oowp\OowpQuery;
use Outlandish\Wordpress\Oowp\WordpressTheme;
use Outlandish\Wordpress\Routemaster\Oowp\OowpRouter;
use Outlandish\Wordpress\Routemaster\Oowp\View\SitemapView;
use Outlandish\Wordpress\Routemaster\Response\XmlResponse;

class OlRouter extends OowpRouter
{
    public function __construct()
    {
        parent::__construct(new OlRouterHelper());
        $this->addRoute('|^article/(.*)/?$|i', 'article');
        $this->addRoute('|^mps/?$|i', 'mps');
        $this->addRoute('|^search/?$|i', 'search');
        $this->addRoute('|^rss.xml$|i', 'rss');
        $this->addRoute('|^not-found/$|i', 'show404');
    }

    public function route()
    {
        // ignore any requests that post gravity form AJAX data
        if (isset($_POST['gform_ajax'])) {
            return;
        }
        parent::route();
    }

    protected function frontPage()
    {
        /** @var Page $frontPage */
        $frontPage = $this->helper->querySingle(
            ['page_id' => get_option('page_on_front')],
            true
        );

        return new FrontPage(
            new Banner(
                $frontPage->metadata('strapline'),
                $frontPage->featuredImage(),
                $frontPage->featuredImageMobile(),
                $frontPage->metadata('buttons')
            ),
            new WysiwygRow($frontPage->content()),
            new RowBuilderRows($frontPage->getRowBuilderContent())
        );
    }

    protected function defaultPost($slug)
    {
        /** @var BasePost $post */
        $post = $this->helper->querySingle(
            ['name' => $slug, 'post_type' => 'any'],
            true
        );

        $rowBuilderRows = null;
        $linkedPostIds = [];
        $relatedPostsRows = null;

        if ($post instanceof BasePost) {
            if ($post instanceof RowBuilderPost) {
                $rowBuilderContent = $post->getRowBuilderContent();
                $rowBuilderRows = new RowBuilderRows($rowBuilderContent);
                $linkedPostIds = $post->linkedPostIds;
            }
            $relatedPosts = $post->getRelatedPostsGroupedByType($linkedPostIds);
            $relatedPostsRows = new RelatedPostsRows($relatedPosts);
        }

        return new DefaultPost(
            new Banner(
                $post->title(),
                $post->featuredImage(),
                $post->featuredImageMobile(),
                $post->metadata('buttons')
            ),
            new Breadcrumb($post->breadcrumbTrail()),
            new WysiwygRow($post->content()),
            $rowBuilderRows,
            $relatedPostsRows
        );
    }

    protected function article($slug)
    {
        /** @var Article $post */
        $post = $this->helper->querySingle(
            ['name' => $slug, 'post_type' => Article::postType()],
            true
        );

        return new ArticlePage(
            new Banner(
                $post->title(),
                $post->featuredImage(),
                $post->featuredImageMobile(),
                $post->metadata('buttons')
            ),
            new Breadcrumb($post->breadcrumbTrail()),
            new ShareIcons($post->titleForMetadata(), $post->permalink()),
            new WysiwygRow($post->content()),
            new RelatedPostsRows($post->getRelatedPostsGroupedByType())
        );
    }

    protected function mps()
    {
        /** @var Page $page */
        $page = $this->helper->querySingle(
            ['name' => 'mps'],
            true
        );

        return new MPPage(
            new Banner(
                $page->title(),
                $page->featuredImage(),
                $page->featuredImageMobile(),
                $page->metadata('buttons')
            ),
            new Breadcrumb($page->breadcrumbTrail()),
            new ShareIcons($page->titleForMetadata(), $page->permalink()),
            new WysiwygRow($page->content())
        );
    }

    /**
     * Shows the search page
     * @route /search
     */
    protected function search()
    {
        if (array_key_exists('s', $_GET)) {
            $searchTerm = sanitize_text_field(stripslashes(urldecode($_GET['s'])));
        } else {
            $searchTerm = '';
        }

        $post = new FakePost();
        $post->post_title = $searchTerm ? "Search results: $searchTerm" : "Search results";
        $post->setAsGlobal();

        $searchQuery = $searchTerm ? new OowpQuery(['s' => $searchTerm]) : null;
        $resultsRow = $searchQuery && $searchQuery->post_count ? new LinksRow($searchQuery->posts, true) : null;

        if ($resultsRow) {
            $matches = ($searchQuery->post_count) === 1 ? 'match' : 'matches';
            $message = "Found $searchQuery->post_count $matches for <strong>$searchTerm</strong>";
        } elseif ($searchTerm) {
            $message = "Sorry, no matches for <strong>$searchTerm</strong>. Do you want to try again?";
        } else {
            $message = "No search term entered. Do you want to try again?";
        }

        return new SearchPage(
            new Breadcrumb($post->breadcrumbTrail()),
            $message,
            $resultsRow
        );
    }

    /**
     * Enforces a 404 response even if someone visits siteurl.com/not-found/ directly.
     */
    protected function show404()
    {
        $response = $this->helper->createNotFoundResponse();
        $response->setRouteName('404');
        $response->headers[] = 'HTTP/1.0 404 Not Found';
        return $response;
    }

    /**
     * @route /rss.xml
     */
    protected function rss()
    {
        return new RssResponse([
            'items' => Article::fetchAll(['orderby' => 'date', 'order' => 'desc']),
            'name' => get_bloginfo('name'),
            'url' => WordpressTheme::getInstance()->homeURL()
        ]);
    }

    /**
     * Prevent the Not Found page appearing in the sitemap.
     *
     * @route /sitemap.xml
     */
    protected function sitemap()
    {
        $notFound = Page::fetchBySlug('not-found');
        $postsToHide = [];
        if ($notFound) {
            $postsToHide[] = $notFound->ID;
        }
        $view = new SitemapView(new OowpQuery([
            'post_type' => 'any',
            'orderby' => 'date',
            'post__not_in' => $postsToHide
        ]));
        return new XmlResponse($view);
    }
}
