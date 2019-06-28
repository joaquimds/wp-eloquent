<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class Pagination extends RoutemasterOowpView
{
    public $pageCount;
    public $page;
    public $urlPattern;

    public function render($args = ['neighbours' => 2])
    {
        $neighbours = $args['neighbours'];
        $pages = [];
        $middleStart = max(1, $this->page - $neighbours);
        $middleEnd = min($this->pageCount, $this->page + $neighbours);
        for ($i = $middleStart; $i <= $middleEnd; $i++) {
            $pages[] = [
                'page' => $i,
                'active' => $i == $this->page
            ];
        }
        if ($middleStart > 1) {
            if ($middleStart > 2) {
                array_unshift($pages, [
                    'page' => null,
                    'active' => false
                ]);
            }
            array_unshift($pages, [
                'page' => 1,
                'active' => false
            ]);
            array_unshift($pages, [
                'page' => $this->page - 1,
                'active' => false,
                'label' => '&lt'
            ]);
        }
        if ($middleEnd < $this->pageCount) {
            if ($middleEnd < $this->pageCount - 1) {
                $pages[] = [
                    'page' => null
                ];
            }
            $pages[] = [
                'page' => $this->pageCount,
                'active' => false
            ];
            $pages[] = [
                'page' => $this->page + 1,
                'active' => false,
                'label' => '&gt;'
            ];
        }
        $pages = array_map(function ($item) {
            return (object)$item;
        }, $pages);
        ?>
        <ul class="pagination">
            <?php foreach ($pages as $page) : ?>
                <li class="page-number <?php echo implode(' ', [$page->page ? '' : 'disabled', $page->page && $page->active ? 'active' : '']); ?>">
                    <?php if ($page->page) : ?>
                        <a href="<?php echo str_replace('%page%', $page->page, $this->urlPattern); ?>">
                            <?php echo empty($page->label) ? $page->page : $page->label; ?>
                        </a>
                    <?php else : ?>
                        <a>...</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }
}
