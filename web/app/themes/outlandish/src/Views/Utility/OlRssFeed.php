<?php

namespace Outlandish\Website\Views\Utility;

use Outlandish\Wordpress\Oowp\Views\RssFeed;

class OlRssFeed extends RssFeed
{
    public function renderSiteInfo()
    {
        $date = date('D, d M Y H:i:s T');
        ?>
        <title><?php echo $this->name; ?> updates</title>
        <description>Latest articles from <?php echo $this->name; ?></description>
        <link><?php echo $this->url; ?></link>
        <language>en-gb</language>
        <lastBuildDate><?php echo $date; ?></lastBuildDate>
        <pubDate><?php echo $date; ?></pubDate>
        <?php
    }
}
