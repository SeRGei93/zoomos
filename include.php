<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

\Bitrix\Main\Loader::registerAutoLoadClasses("zoomos.tires2",array(
        'ZMS\Import' => 'lib/import.php',
        'ZMS\Bx\Section' => 'lib/bx/section.php',
        'ZMS\Bx\Property' => 'lib/bx/property.php',
        'ZMS\Bx\Product' => 'lib/bx/product.php',

        'ZMS\Config' => 'lib/config.php',
        'ZMS\Helper\Help' => 'lib/helper/help.php',
        'ZMS\Helper\Progress' => 'lib/helper/progress.php',
        'ZMS\Helper\Logger' => 'lib/helper/logger.php',
        'ZMS\Helper\Json' => 'lib/helper/json.php',
        'ZMS\Helper\Request' => 'lib/helper/request.php',
        'ZMS\Helper\Executor' => 'lib/helper/executor.php',
        'ZMS\Helper\Process' => 'lib/helper/process.php',
    )
);
