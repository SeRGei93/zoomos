<?
$_SERVER["DOCUMENT_ROOT"] = "~/bx-optim.fun/public_html/";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

error_reporting(E_ERROR | E_PARSE);
@set_time_limit(0);
@ini_set('memory_limit', '512M');


$GLOBALS['DB']->Query('SET wait_timeout=28800');

CModule::includeModule('zoomos.tires2');

use ZMS\Config;
use ZMS\Helper\Executor;

define("LOG_FILENAME", Config::zms_config('ZMS_LOG') . date("Y-m-d-H-i") . ".log");

Executor::launch();



?>
