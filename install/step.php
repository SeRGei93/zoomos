<?

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid())
{

	return;
}

if ($errorException = $APPLICATION->GetException())
{

	echo(CAdminMessage::ShowMessage($errorException->GetString()));
} else
{

    echo '<div class="adm-detail-content-btns">';
    echo '<a href="' . $APPLICATION->getCurPage() . '" class="adm-btn">' . getMessage("MOD_BACK") . '</a>';
    echo '<a href="/bitrix/admin/settings.php?lang=' . LANG . '&mid=zoomos.tires2" class="adm-btn adm-btn-green">' . getMessage("ZOOMOS_SETTINGS") . '</a>';
    echo '</div>';

    echo CAdminMessage::showNote(getMessage("MOD_INST_OK"));


}
?>
