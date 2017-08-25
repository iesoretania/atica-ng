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

        $needle = preg_quote($needle, '/');

        $replacements = [
            'a' => '[áa]', 'e' => '[ée]', 'i' => '[íi]', 'o' => '[óo]', 'u' => '[úu]',
            'A' => '[ÁA]', 'E' => '[ÉE]', 'I' => '[ÍI]', 'O' => '[ÓO]', 'U' => '[ÚU]',
            'n' => '[ñn]', 'N' => '[ÑN]'
        ];

        $needle = str_replace(array_keys($replacements), $replacements, $needle);

        return preg_replace("/(" . $needle . ")/ui", '<span class="'.htmlentities($class).'">\\1</span>', $string);
    }
}
