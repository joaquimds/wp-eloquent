<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class ShareIcons extends RoutemasterOowpView
{
    const TWITTER_CHAR_LIMIT = 280;

    // Twitter shortens all URLs to 23 chars (or lengthens them to 23 if they are shorter)
    const TWITTER_URL_LENGTH = 23;

    /** @var string */
    protected $title;

    /** @var string */
    protected $url;

    /** @var bool */
    protected $showPrintIcon;

    /** @var string */
    protected $textForTwitter;

    /** @var string */
    protected $textForWhatsApp;

    /**
     * @param string $title
     * @param string $url
     * @param bool $showPrintIcon
     */
    public function __construct($title, $url, $showPrintIcon = false)
    {
        parent::__construct(compact('showPrintIcon'));

        $title = sanitize_text_field($title);

        // Ensure tweets aren't too long
        // +1 for the space between the two
        $tweetLength = strlen($title) + static::TWITTER_URL_LENGTH + 1;

        if ($tweetLength > static::TWITTER_CHAR_LIMIT) {
            // -3 to account for the ellipsis (Twitter treats it as two chars) and space before the url
            $textForTwitter = trim(substr($title, 0, static::TWITTER_CHAR_LIMIT - static::TWITTER_URL_LENGTH - 3));
            $textForTwitter .= '…';
        } else {
            $textForTwitter = $title;
        }

        $this->textForTwitter = static::encodeTitle($textForTwitter);
        $this->textForWhatsApp = static::encodeTitle($title . PHP_EOL . PHP_EOL . $url);
        $this->title = static::encodeTitle($title);
        $this->url = rawurlencode($url);
    }

    public function render($modifier = '')
    {
        $className = $modifier ? 'share--'.$modifier : '';
        if ($modifier === 'sticky') {
            $className .= ' js-sticky-share';
        }
        ?>

        <aside class="share <?php echo $className; ?>">

            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $this->url; ?>"
               title="Share on Facebook"
               target="_blank"
               rel="noopener"
               class="share__icon">
                <i class="fab fa-facebook-f"></i>
            </a>

            <a href="https://twitter.com/share?url=<?php echo $this->url; ?>&text=<?php echo $this->textForTwitter; ?>"
               title="Share on Twitter"
               target="_blank"
               rel="noopener"
               class="share__icon">
                <i class="fab fa-twitter"></i>
            </a>

            <a href="whatsapp://send?text=<?php echo $this->textForWhatsApp; ?>"
               title="Share on WhatsApp"
               class="share__icon d-sm-none">
                <i class="fab fa-whatsapp"></i>
            </a>

            <a href="mailto:?subject=<?php echo $this->title; ?>&body=<?php echo $this->url; ?>"
               title="Email"
               target="_blank"
               rel="noopener"
               class="share__icon d-md-none">
                <i class="fas fa-envelope"></i>
            </a>

            <?php if ($this->showPrintIcon) : ?>
                <span title="Print this page"
                   onclick="window.print();"
                   class="share__icon share__icon--print d-none d-md-inline-block">
                    <i class="far fa-print"></i>
                </span>
            <?php endif; ?>

        </aside>

        <?php
    }

    protected static function encodeTitle($title)
    {
        // html decode to replace e.g. '&#8211;' with '–', then url encode
        return rawurlencode(html_entity_decode($title, ENT_COMPAT, 'UTF-8'));
    }
}
