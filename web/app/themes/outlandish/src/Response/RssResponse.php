<?php

namespace Outlandish\Website\Response;

use Outlandish\Website\Views\Utility\OlRssFeed;
use Outlandish\Wordpress\Routemaster\Oowp\Response\ViewResponse;

class RssResponse extends ViewResponse
{
    public function __construct(array $outputArgs)
    {
        parent::__construct($outputArgs);
        $this->headers[] = 'Content-Type: application/xml';
        $this->view = new OlRssFeed();
    }
}
