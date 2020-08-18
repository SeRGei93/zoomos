<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);
Loader::includeModule('iblock');
Loader::includeModule('catalog');


$dbCatalogList = CCatalog::getList();
$iblockCatalogList[0] = getMessage("ZOOMOS_IBLOCK_NOT_PICKED");
while ($catalog = $dbCatalogList->fetch()) {
    #if (CCatalogSKU::getInfoByProductIBlock($catalog['ID'])) {
    $iblockCatalogList[$catalog['ID']] = $catalog['NAME'] . ", id:" . $catalog['ID'];
    #}
}


$dbPriceChange[0] = getMessage("ZOOMOS_PRICE_CHANGE");

if (CModule::IncludeModule("iblock")) {
    $arSelect = Array("ID", "IBLOCK_ID", "NAME", "PROPERTY_*");
    $arFilter = Array("IBLOCK_ID" => 56);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $dbPriceChange[$arFields['NAME']] = $arFields['NAME'];
    }
}


$arTabs = array(
	array(
		"DIV" => "edit",
		"TAB" => Loc::getMessage("ZOOMOS_OPTIONS_TAB_NAME"),
		"TITLE" => Loc::getMessage("ZOOMOS_OPTIONS_TAB_NAME"),
		"OPTIONS" => array()
	)
);


//Общие настройки обработки
$arOptionParam = array(
        
	//Loc::getMessage("ZOOMOS_OPTIONS_TAB_COMMON"),

    array(
        "ZOOMOS_KEY",
        Loc::getMessage("ZOOMOS_OPTIONS_KEY"),
        "rft.by-USMTOAfSa",
        array("text", 50)
    ),
    array(
        "ZOOMOS_IBLOCK_SHINY_ID",
        Loc::getMessage("ZOOMOS_IBLOCK_PARAMS_SHINY"),
        '',
        array(
            "selectbox",
            $iblockCatalogList
        )
    ),
    array(
        "ZOOMOS_IBLOCK_DISKI_ID",
        Loc::getMessage("ZOOMOS_IBLOCK_PARAMS_DISKI"),
        '',
        array(
            "selectbox",
            $iblockCatalogList
        )
    ),
    array(
        "ZOOMOS_IBLOCK_AKB_ID",
        Loc::getMessage("ZOOMOS_IBLOCK_PARAMS_AKB"),
        '',
        array(
            "selectbox",
            $iblockCatalogList
        )
    ),
//    array(
//        "Z_PRICE_CHANGE",
//        Loc::getMessage("ZOOMOS_PRICE_CHANGE_ITEM"),
//        '',
//        array(
//            "selectbox",
//            $dbPriceChange
//        )
//    ),
//    array(
//        "ZOOMOS_LIMIT",
//        Loc::getMessage("ZOOMOS_OPTIONS_LIMIT"),
//        "",
//        array("text", 5)
//    ),
//    array(
//        "ZOOMOS_DEACTIVATE_NOPRICE",
//        Loc::getMessage("ZOOMOS_DEACTIVATE_NOPRICE"),
//        "N",
//        array("checkbox")
//    ),

);


//Добавим параметры
$arTabs[0]["OPTIONS"] = array_merge($arTabs[0]["OPTIONS"], $arOptionParam);
//---------------------

$tabControl = new CAdminTabControl(
	"tabControl",
	$arTabs
);
?>

<?



//$arInfoMessage[]=Loc::getMessage("ZOOMOS_OPTIONS_AUTHOR");

//if ($arInfoMessage):
//	CAdminMessage::showMessage(array(
//		"MESSAGE" => implode($arInfoMessage,"\n\n"),
//		"TYPE" => 'OK',
//	));
//endif;


?>



<?
$tabControl->Begin();
?>



<?
//Отображение формы с настройками
?>
<form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>"
	  method="post">

	<?
	foreach ($arTabs as $aTab)
	{

		if ($aTab["OPTIONS"])
		{

			$tabControl->BeginNextTab();

			__AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);


		}
	}


	?>





	<?
	$tabControl->Buttons();
	?>


	<input type="submit" name="apply" value="<? echo(Loc::GetMessage("ZOOMOS_OPTIONS_INPUT_APPLY")); ?>"
		   class="adm-btn-save"/>
	<input type="submit" name="default" value="<? echo(Loc::GetMessage("ZOOMOS_OPTIONS_INPUT_DEFAULT")); ?>"/>



	<?
	echo(bitrix_sessid_post());

	?>

</form>



<?
$tabControl->End();
?>

<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        <div class="adm-info-message-title">Внимание</div>
        <? echo(Loc::GetMessage("HINT")); ?>
    </div>
</div>

<?
if ($request->isPost() && check_bitrix_sessid())
{

	foreach ($arTabs as $aTab)
	{
		foreach ($aTab["OPTIONS"] as $arOption)
		{

			if (!is_array($arOption))
			{

				continue;
			}


			if ($arOption["note"])
			{


				continue;
			}

			if ($request["apply"])
			{

				$optionValue = $request->getPost($arOption[0]);

				if ($arOption[0] == "switch_on")
				{
					if ($optionValue == "")
					{

						$optionValue = "N";
					}
				}



				Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
			} elseif ($request["default"])
			{

				Option::set($module_id, $arOption[0], $arOption[2]);
			}
		}
	}

	LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . $module_id . "&lang=" . LANG);
}
?>
