<?php
/**
 * Created by PhpStorm.
 * User: lrlopez
 * Date: 24/8/17
 * Time: 15:54
 */

namespace AppBundle\Twig;


class HighlightFilter extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('highlight', [$this, 'highlight'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        );
    }

    public function highlight($string, $needle, $class = 'highlight')
    {
        if (null === $needle || '' === $needle) {
            return $string;
        }
        return preg_replace_callback('/'.preg_quote($needle, '/').'/i', function ($match) use ($class) {
            return '<span class="'.htmlentities($class).'">'.$match[0].'</span>';
        }, $string);
    }
}
