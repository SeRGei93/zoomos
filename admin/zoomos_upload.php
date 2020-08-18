<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CModule::includeModule('zoomos.tires2');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

use Bitrix\Main\Config\Option;
use ZMS\Helper\Process;
use ZMS\Helper\Progress;

$key = Option::get("zoomos.tires2", "ZOOMOS_KEY");
$iblockId = Option::get("zoomos.tires2", "Z_CATALOG_ID");

$script = '/home/s/sergmotol/bx-optim.fun/public_html/zoomos/_start.php';


if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Export"]=="Y")
{
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

    if ($_REQUEST["PROGRESS"] != "Y"){
        exec("/usr/local/bin/php7.3 " . $script . " > /dev/null 2>/dev/null &");
    }

    $isRunning = exec ( 'ps ax| grep '. $script , $output, $retval);

    $jsonValue = json_decode(Progress::getProgress(), true);

    echo '<script>CloseWaitWindow();</script>';

    if (count($output) == 3) {

        echo '<script>restart();</script>';

        $message = 'Идет загрузка прайса';

        if ($jsonValue['progress']>0){
            $message = "Шаг 1: обработка прайса " . $jsonValue['progress'] . "/" . $jsonValue['total'];
            $total = $jsonValue['total'];
            $progress = $jsonValue['progress'];
        }

        if ($jsonValue['new'] > 0){
            $message = "Шаг 2: добавляем новые товары " . $jsonValue['new'] . "/" . $jsonValue['totalNew'];
            $total = $jsonValue['totalNew'];
            $progress = $jsonValue['new'];
        }

        if ($jsonValue['update'] > 0){
            $message = "Шаг 3: обновляем товары " . $jsonValue['update'] . "/" . $jsonValue['totalUpdate'];
            $total = $jsonValue['totalUpdate'];
            $progress = $jsonValue['update'];
        }


        \CAdminMessage::ShowMessage(array(
            "MESSAGE" => $message,
            "DETAILS" => "#PROGRESS_BAR#",
            "HTML" => true,
            "TYPE" => "PROGRESS",
            "PROGRESS_TOTAL" => $total,
            "PROGRESS_VALUE" => $progress,
        ));

        $result = [
            'new' => $jsonValue['new'] ?? 0,
            'upd' => $jsonValue['update'] ?? 0,
            'noPrice' => $jsonValue['noPrice'] ?? 0,
            'updatePrice' => $jsonValue['updatePrice'] ?? 0
        ];


        echo '<script>DoNext('.CUtil::PhpToJSObject($result).');</script>';
    }
    else
    {

        CAdminMessage::ShowMessage(array("MESSAGE"=>'Загрузка завершена',"TYPE"=>"OK"));

        echo '<script>EndExport();</script>';
    }

    require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}

$APPLICATION->setTitle("Мастер обмена Zoomos");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div id="tbl_iblock_export_result_div"></div>
<?
$aTabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => 'Импорт',
        "ICON" => "main_user_edit",
        "TITLE" => 'Обновление товаров',
    ),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
?>

<script>
    var running = false;

    function DoNext(NS)
    {
        var queryString =
            'Export=Y'
            + '&lang=<?=LANGUAGE_ID?>'
            + '&<?echo bitrix_sessid_get()?>'
        ;

        if(NS)
        {
            queryString+='&PROGRESS=Y';
        }

        if(running)
        {
            ShowWaitWindow();
            BX.ajax.post(
                'zoomos_upload.php?'+queryString,
                NS,
                function(result){
                    document.getElementById('tbl_iblock_export_result_div').innerHTML = result;
                }
            );
        }

        var updatePrice = 'Обновлено цен: ' + NS.updatePrice;
        var totalUpd = 'Обновлено товаров: ' + NS.upd;
        var totalNew = 'Новых товаров: ' + NS.new;
        var noPrice = 'Товаров с нулевым остатком: ' + NS.noPrice;

        document.getElementById('progress-upd').innerHTML = totalUpd;
        document.getElementById('progress-new').innerHTML = totalNew;
        document.getElementById('progress-updatePrice').innerHTML = updatePrice;
        document.getElementById('progress-noPrice').innerHTML = noPrice;
    }

    function check(NS){
        var queryString =
            'Export=Y'
            + '&lang=<?=LANGUAGE_ID?>'
            + '&<?echo bitrix_sessid_get()?>'
            + '&PROGRESS=Y'
        ;

        if(!running)
        {
            ShowWaitWindow();
            BX.ajax.post(
                'zoomos_upload.php?'+queryString,
                NS,
                function(result){
                    document.getElementById('tbl_iblock_export_result_div').innerHTML = result;
                }
            );
        }
    }

    check();

    function StartExport()
    {
        running = document.getElementById('start_button').disabled = true;
        DoNext();
    }

    function EndExport()
    {
        running = document.getElementById('start_button').disabled = false;

    }

    function restart() {
        running = document.getElementById('start_button').disabled = true;

        document.getElementById('progress-upd').innerHTML = '';
        document.getElementById('progress-new').innerHTML = '';
        document.getElementById('progress-updatePrice').innerHTML = '';
        document.getElementById('progress-noPrice').innerHTML = '';
    }

</script>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" name="form1" id="form1">
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>

    <tr class="progress">
        <td>
            <div id="progress-updatePrice"></div>
            <div id="progress-noPrice"></div>
            <div id="progress-new"></div>
            <div id="progress-upd"></div>
        </td>
    </tr>

    <?$tabControl->Buttons();?>

    <input type="button" id="start_button" value="Начать импорт" OnClick="StartExport();" class="adm-btn-save">

    <?/*<input type="button" id="stop_button" value="<?echo GetMessage("IBLOCK_CML2_STOP_EXPORT")?>" OnClick="EndExport();">*/?>
    <?$tabControl->End();?>

</form>

<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        <p><b><a href="/bitrix/admin/settings.php?lang=ru&mid=zoomos.tires2" target="_blank">Настройки обмена</a></b></p>
        <p>Ключ: <b><?=$key?></b> </p>
        <p>Ссылка на прайс: <b><a href="http://api.zoomos.by/pricelist?key=<?=$key?>" target="_blank"><b>http://api.zoomos.by/pricelist?key=<?=$key?></b></a></b> </p>
    </div>
</div>

<style>
    .progress div{
        font-size: 14px;
        line-height: 22px;
    }
</style>

<?require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");?>
