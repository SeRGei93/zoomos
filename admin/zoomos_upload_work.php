<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

CModule::includeModule('zoomos.tires2');

use Bitrix\Main\Config\Option;
use ZMS\Helper\Process;
use ZMS\Helper\Progress;

$key = Option::get("zoomos.tires2", "ZOOMOS_KEY");
$iblockId = Option::get("zoomos.tires2", "Z_CATALOG_ID");

$script = '/home/s/sergmotol/bx-optim.fun/public_html/zoomos/_start.php';

$init = new Process($script);

if ($_GET['work_start']){
    exec("/usr/local/bin/php7.3 " . $script . " > /dev/null 2>/dev/null &");
}


$isRunning = exec ( 'ps ax| grep '. $script , $output, $retval);

if ($_GET['get_progress']) {
    if ( count($output) >= 3) {

        $jsonValue = json_decode(Progress::getProgress(), true);


        \CAdminMessage::ShowMessage(array(
            "MESSAGE" => "Обработка прайса " . $jsonValue['progress'] . " из " . $jsonValue['total'],
            "DETAILS" => "#PROGRESS_BAR#",
            "HTML" => true,
            "TYPE" => "PROGRESS",
            "PROGRESS_TOTAL" => $jsonValue['total'],
            "PROGRESS_VALUE" => $jsonValue['progress'],
        ));

        echo '<br>';

        if ($jsonValue['totalNew']){
            echo 'Новых товаров: ' . $jsonValue['new'] . ' из ' . $jsonValue['totalNew'] . '<br>';
        }

        if ($jsonValue['totalUpdate']){
            echo 'Обновлено товаров: ' . $jsonValue['update'] . ' из ' . $jsonValue['totalUpdate'] . '<br>';
        }

    }else{
        ?>

        <div class="adm-detail-content-btns-wrap">
            <div class="adm-detail-content-btns">
                <input id="zms-import-start" type="button" value="Начать импорт" onClick="return startImport()"/>
            </div>
        </div>

    <?}
    die();
}


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->setTitle("Мастер обмена Zoomos");

$aTabs = array(
    array("DIV" => "edit1", "TAB" => "Импорт", "ICON" => "translate_edit"),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>

<?
$tabControl->begin();
$tabControl->beginNextTab();
?>
<div id="zms-import-progress">
    <p>Загрузка...</p>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
    function startImport() {
        if (confirm('Вы хотите запустить обмен?')) {
            $.ajax({
                url: "/bitrix/admin/zoomos_upload.php?work_start=Y"
            });

            $('#zms-import-progress').html('<p>Идет загрузка...</p>');
        }

        return false;
    }

    function getProgress() {
        $.ajax({
            url: "/bitrix/admin/zoomos_upload.php?get_progress=Y"
        }).done(function (data) {

            $('#zms-import-progress').html(data);

            setTimeout(function () {
                getProgress();
            }, 15000);
        });
    }

    getProgress();
</script>


<?$tabControl->end();?>

<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        <p><b><a href="/bitrix/admin/settings.php?lang=ru&mid=zoomos.tires2" target="_blank">Настройки обмена</a></b></p>
        <p>Ключ: <b><?=$key?></b> </p>
        <p>Ссылка на прайс: <b><a href="http://api.zoomos.by/pricelist?key=<?=$key?>" target="_blank"><b>http://api.zoomos.by/pricelist?key=<?=$key?></b></a></b> </p>
    </div>
</div>

<?require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");?>
