<?php

namespace App\Services;

class PurifierService
{
    public static function clean(string $content): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', ''); // Disallow all HTML tags
        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($content);
    }
}
