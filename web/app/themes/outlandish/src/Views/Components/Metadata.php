<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class Metadata extends RoutemasterOowpView
{
    /** @var string */
    protected $title;

    /** @var string */
    protected $robots;

    /** @var string */
    protected $description;

    /** @var array */
    protected $openGraphData;

    /**
     * See here for info on Twitter card types:
     * https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards
     *
     * @var
     */
    protected $twitterCardType;

    /** @var string - the Twitter handle for this site e.g. @outlandish */
    protected $twitterSite;

    /**
     * @param string $title
     * @param string $robots
     * @param string $description
     * @param array $openGraphData
     * @param string $twitterCardType
     * @param string $twitterSite
     */
    public function __construct(
        $title,
        $robots = '',
        $description = '',
        $openGraphData = [],
        $twitterCardType = '',
        $twitterSite = ''
    ) {
        parent::__construct(compact(
            'title',
            'robots',
            'description',
            'openGraphData',
            'twitterCardType',
            'twitterSite'
        ));

        // Robots tag is already set by the MU plugin Disallow Indexing in non-production environments.
        if (WP_ENV !== 'production') {
            $this->robots = '';
        }
    }

    public function render($args = [])
    {
        ?>
        <title><?php echo $this->title; ?></title>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <?php if ($this->robots) : ?>
            <meta name="robots" content="<?php echo $this->robots; ?>"/>
        <?php endif; ?>
        <?php if ($this->description) : ?>
            <meta name="description" content="<?php echo $this->description; ?>"/>
        <?php endif; ?>
        <?php if ($this->twitterCardType) : ?>
            <meta name="twitter:card" content="<?php echo $this->twitterCardType; ?>"/>
        <?php endif ?>
        <?php if ($this->twitterSite) : ?>
            <meta name="twitter:site" content="<?php echo $this->twitterSite; ?>"/>
        <?php endif ?>
        <?php foreach ($this->openGraphData as $key => $value) : ?>
            <meta property="<?php echo $key; ?>" content="<?php echo $value; ?>"/>
        <?php endforeach; ?>
        <?php
    }
}
