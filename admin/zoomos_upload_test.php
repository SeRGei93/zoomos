<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CModule::includeModule('zoomos.tires2');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");

use Bitrix\Main\Config\Option;
use ZMS\Helper\Process;
use ZMS\Helper\Progress;

$key = Option::get("zoomos.tires2", "ZOOMOS_KEY");
$iblockId = Option::get("zoomos.tires2", "Z_CATALOG_ID");

$script = '/var/www/clients/client1/web1/web/zoomos/_start.php';

$init = new Process($script);

if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Export"]=="Y")
{
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

    exec("php -f " . $script . " > /dev/null 2>/dev/null &");

    ?>

    <script>
        CloseWaitWindow();
    </script>

<?
    $isRunning = exec ( 'ps ax| grep '. $script , $output, $retval);

    if ( count($output) >= 3) {

        $jsonValue = json_decode(Progress::getProgress(), true);


        \CAdminMessage::ShowMessage(array(
            "MESSAGE" => "Загрузка товаров " . $jsonValue['progress'] . " из " . $jsonValue['total'],
            "DETAILS" => "#PROGRESS_BAR#",
            "HTML" => true,
            "TYPE" => "PROGRESS",
            "PROGRESS_TOTAL" => $jsonValue['total'],
            "PROGRESS_VALUE" => $jsonValue['progress'],
        ));

        echo 'Обновлено товаров: ' . $jsonValue['update'] . '<br>';
        echo 'Новых товаров: ' . $jsonValue['new'] . '<br>';

        echo '<script>DoNext();</script>';
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>


<script>
    var running = false;

    function DoNext(NS)
    {

        var queryString =
            'Export=Y'
            + '&lang=<?=LANGUAGE_ID?>'
            + '&<?echo bitrix_sessid_get()?>'
        ;


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
    }

    function StartExport()
    {
        running = document.getElementById('start_button').disabled = true;
        DoNext();
    }

    function EndExport()
    {
        running = document.getElementById('start_button').disabled = false;
    }

</script>


<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" name="form1" id="form1">
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="100%"><div id="zms-import-progress"></div></td>
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

<?require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");?>
