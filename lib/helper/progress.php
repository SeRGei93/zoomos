<?php


namespace ZMS\Helper;

use ZMS\Config;

class Progress
{

    public static function setProgress($progress){

        $file = Config::zms_config('ZMS_PATH') . 'progress.json';

        self::putJson($file,$progress);

    }

    public static function getProgress(){
        $file = Config::zms_config('ZMS_PATH') . 'progress.json';
        return file_get_contents($file);
    }

    public static function Limit($total)
    {
        $file = Config::zms_config('ZMS_PATH') . 'progress.json';

        self::clearFile($file);

        self::putJson($file,array('total' => $total));
    }

    /**
     * @param string $fileName
     * @param string $content
     */
    public static function putContent($fileName, $content)
    {
        self::checkFile($fileName);
        file_put_contents($fileName, $content);
    }


    /**
     * @param string $fileName
     * @param string $content
     */
    public static function appendContent($fileName, $content)
    {
        self::checkFile($fileName);
        file_put_contents($fileName, $content, FILE_APPEND);
    }

    /** @param string $fileName */
    public static function checkFile($fileName)
    {
        CheckDirPath($fileName);
        if (!file_exists($fileName)) {
            $file = fopen($fileName, "w+");
            fwrite($file, "");
            fclose($file);
        }
    }

    /** @param string $fileName */
    public static function clearFile($fileName)
    {
        unlink($fileName);
        self::checkFile($fileName);
    }

    /**
     * @param string $fileName
     * @param array $array
     */
    public static function putJson($fileName, $array)
    {
        $source = (array)json_decode(
            file_get_contents($fileName), true);
        self::putContent($fileName, json_encode(
            array_merge($source, $array)
        ));
    }
}