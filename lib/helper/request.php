<?php

namespace ZMS\Helper;

class Request
{
    /**
     * @param string $link
     * @return string
     */
    public static function get($link)
    {
        return self::isCurlExists()
            ? self::getByCurl($link)
            : self::getByContent($link);
    }

    /** @return bool */
    private static function isCurlExists()
    {
        return \function_exists("curl_init");
    }

    /**
     * @param string $link
     * @return mixed
     */
    private static function getByCurl($link)
    {
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $link);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        \curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:6.0.2) Gecko/20100101 Firefox/6.0.2');
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = \curl_exec($ch);
        \curl_close($ch);

        return $json;
    }

    /**
     * @param string $link
     * @return string
     */
    private static function getByContent($link)
    {
        return \file_get_contents($link);
    }
}
