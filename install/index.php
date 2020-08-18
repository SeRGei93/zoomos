<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

//Подключим языковые файлы
Loc::loadMessages(__FILE__);


class zoomos_tires2 extends CModule
{
	var $MODULE_ID = 'zoomos.tires2';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $PARTNER_NAME = 'Сергей Бушкевич';
	var $PARTNER_URI = 'https://t.me/stefanovichby';

	/**
	 * ZOOMOS constructor.
	 * Выводит информацию о модуле
	 */
	public function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__ . "/version.php");;

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("ZOOMOS_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("ZOOMOS_DESCRIPTION");
		$this->PARTNER_NAME = 'Сергей Бушкевич';
		$this->PARTNER_URI = 'https://t.me/stefanovichby';

		return false;
	}

	/**
	 * Реализация процесса установки
	 *
	 * @return bool
	 */
	public function DoInstall()
	{

		global $APPLICATION;

		if (CheckVersion(ModuleManager::getVersion("main"), "17.00.00"))
		{

			$this->InstallFiles();
			$this->InstallDB();

			ModuleManager::registerModule($this->MODULE_ID);

			$this->InstallEvents();
		} else
		{

			$APPLICATION->ThrowException(
				Loc::getMessage("ZOOMOS_INSTALL_ERROR_VERSION")
			);
		}

		$APPLICATION->IncludeAdminFile(
			Loc::getMessage("ZOOMOS_INSTALL_TITLE") . " \"" . Loc::getMessage("ZOOMOS_NAME") . "\"",
			__DIR__ . "/step.php"
		);



		return false;
	}

	/**
	 * Копирование файлов в систему
	 *
	 * @return bool
	 */
	public function InstallFiles()
	{
        mkdir($_SERVER["DOCUMENT_ROOT"] . "/upload/zoomos_cache");
        mkdir($_SERVER["DOCUMENT_ROOT"] . "/upload/zoomos_cache/img");
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
        copyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/zoomos", $_SERVER["DOCUMENT_ROOT"] . "/zoomos", true, true);

        return false;
	}

	/**
	 * Создание таблиц в БД
	 *
	 * @return bool
	 */
	public function InstallDB()
	{
		return false;
	}


	/**
	 * Регистрация событий
	 *
	 * @return bool
	 */
	public function InstallEvents()
	{

		//При окончании генерации страницы
//		EventManager::getInstance()->registerEventHandler(
//			"main",
//			"OnEndBufferContent",
//			$this->MODULE_ID,
//			"BxFun\Zoomos\init",
//			"Init"
//		);

		//Перед окончанием генерации страницы
        /*
		EventManager::getInstance()->registerEventHandler(
			"main",
			"OnBeforeEndBufferContent",
			$this->MODULE_ID,
			"test\Compressor\Base",
			"optimize"
		);
        */

		return false;
	}


	/**
	 * Реализация провесса удаления
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function DoUninstall()
	{

		global $APPLICATION;

		$this->UnInstallFiles();
		$this->UnInstallDB();
		$this->UnInstallEvents();

		ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(
			Loc::getMessage("ZOOMOS_UNINSTALL_TITLE") . " \"" . Loc::getMessage("ZOOMOS_NAME") . "\"",
			__DIR__ . "/unstep.php"
		);

		return false;
	}

	/**
	 * Удаление добавленых файлов
	 *
	 * @return bool
	 */
	public function UnInstallFiles()
	{
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin', $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

		return false;
	}

	/**
	 * Удаление таблиц из БД
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function UnInstallDB()
	{
		Option::delete($this->MODULE_ID);

		return false;
	}


	/**
	 * Удаление обработчика событий
	 *
	 * @return bool
	 */
	public function UnInstallEvents()
	{

		//При окончании генерации страницы
//        EventManager::getInstance()->registerEventHandler(
//            "main",
//            "OnEndBufferContent",
//            $this->MODULE_ID,
//            "test\Compressor\Base",
//            "optimize"
//        );


		//Перед окончанием генерации страницы
		/*
        EventManager::getInstance()->unRegisterEventHandler(
			"main",
			"OnBeforeEndBufferContent",
			$this->MODULE_ID,
            "test\Compressor\Base",
            "optimize"
		);
		*/

		return false;
	}
}