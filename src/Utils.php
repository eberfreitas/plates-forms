<?php declare(strict_types=1);

namespace Zyglab\Plates;

class Utils
{
    /**
     * @var string $string
     *
     * @return string
     */
    public static function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['.', '_', '-'], ' ', $string)));
    }

    /**
     * @var string $template
     * @var array $vars
     *
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
