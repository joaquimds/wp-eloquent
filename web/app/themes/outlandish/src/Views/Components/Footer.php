<?php

namespace Outlandish\Website\Views\Components;

use Outlandish\Wordpress\Routemaster\Oowp\View\RoutemasterOowpView;

class Footer extends RoutemasterOowpView
{
    /** @var array */
    protected $elements;

    /**
     * @param array $elements
     */
    public function __construct($elements = [])
    {
        parent::__construct(compact('elements'));
    }

    /**
     * @param array $args
     */
    public function render($args = [])
    {
        if ($this->elements) :
            $colClasses = ['col-12'];
            if (count($this->elements) > 1) {
                $colClasses[] = 'col-sm-6';
            }
            if (count($this->elements) > 2) {
                $colClasses[] = 'col-md-3';
            }
            $colClasses = implode(' ', $colClasses);
            ?>
            <footer class="footer bg-light <?php echo (count($this->elements) < 4) ? 'footer--center' : ''; ?>">
                <div class="container-fluid">
                    <div class="row justify-content-md-center">
                        <?php foreach ($this->elements as $element) : ?>
                            <div class="<?php echo $colClasses; ?>">
                                <div class="footer__element">
                                    <?php if ($element['title']) : ?>
                                        <h5 class="footer__title"><?php echo $element['title']; ?></h5>
                                    <?php endif; ?>
                                    <?php
                                    switch ($element['acf_fc_layout']) :
                                        case 'wysiwyg':
                                        case 'gravity_form':
                                            echo $element['content'] ?? '';
                                            break;
                                        case 'footer_nav':
                                            ?>
                                            <nav>
                                                <ul class="footer__nav">
                                                    <?php echo $element['content'] ?? ''; ?>
                                                </ul>
                                            </nav>
                                            <?php
                                            break;
                                        case 'social_media':
                                            ?>
                                            <?php foreach ($element['accounts'] as $item) :
                                                $icon = $item['type'];
                                                if ($icon === 'facebook'
                                                    || $icon === 'twitter'
                                                    || $icon === 'youtube') {
                                                    $icon .= '-square';
                                                }
                                                ?>
                                                <a class="footer__social"
                                                   href="<?php echo $item['url']; ?>"
                                                   target="_blank"
                                                   rel="noopener">
                                                    <i class="fab fa-<?php echo $icon; ?>"></i>
                                                </a>
                                            <?php endforeach; ?>
                                            <?php
                                            break;
                                    endswitch;
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </footer>
        <?php endif;
    }
}
