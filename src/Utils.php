<?php declare(strict_types=1);

namespace Zyglab\Plates;

class Utils
{
    /**
     * @param string $string
     * @return string
     */
    public static function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['.', '_', '-'], ' ', $string)));
    }

    /**
     * @param string $template
     * @param array $vars
     * @return string
     */
    public static function format(string $template, array $vars): string
    {
        $replace = [];

        foreach ($vars as $k => $v) {
            $replace['{' . $k . '}'] = $v;
        }

        return strtr($template, $replace);
    }
}
