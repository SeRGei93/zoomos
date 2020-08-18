<?php

namespace ZMS\Helper;

use CModule;
use ZMS\Config;
use ZMS\Helper\Progress;
use ZMS\Import;
use Bitrix\Iblock\PropertyIndex\Manager;

CModule::IncludeModule('iblock');

class Executor
{
    public static function launch()
    {
        Progress::Limit(0);
        $init = new Import();
        $init->start();



        $iblock = Config::getIBlocks();

        for ($i=0; $i < count($iblock); $i++){
            Manager::DeleteIndex($iblock[$i]);
            Manager::markAsInvalid($iblock[$i]);
        }

    }
}
