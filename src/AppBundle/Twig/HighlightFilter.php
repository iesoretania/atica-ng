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
            new \Twig_SimpleFilter('highlight', [$this, 'highlightFilter'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        );
    }

    private function replaceNeedle($match) {
        return '<span class="highlight">'.$match[0].'</span>';
    }

    public function highlightFilter($string, $needle)
    {
        if (null === $needle || '' === $needle) {
            return $string;
        }
        return preg_replace_callback('/'.preg_quote($needle, '/').'/i', [$this, 'replaceNeedle'], $string);
    }
}