<?php


namespace ZMS\Bx;

use ZMS\Config;
use ZMS\Helper\Help;
use ZMS\Helper\Json;
use ZMS\Helper\Logger;
use ZMS\Helper\Progress;
use ZMS\Helper\Request;
use Bitrix\Main\Config\Option;

class Product
{
    /** @var integer */
    public $bxSection;

    /** @var array */
    public $bxElements = [];

    /** @var array */
    public $newProducts = [];

    /** @var array */
    public $forUpdateProducts = [];

    /** @var \CIBlockElement */
    private $CIBlockElement;

    /** @var \CCatalogProduct */
    private $CCatalogProduct;

    /** @var \CPrice */
    private $CPrice;

    /** @var \CIBlockProperty */
    private $CIBlockProperty;

    /** @var \CIBlockPropertyEnum */
    private $CIBlockPropertyEnum;

    private $progress;
    private $progressNew;
    private $progressUpdate;
    private $progressUpdatePrice;
    private $progressNoPrice;


    public function __construct()
    {
        $this->CIBlockElement = new \CIBlockElement();
        $this->CCatalogProduct = new \CCatalogProduct();
        $this->CIBlockProperty = new \CIBlockProperty();
        $this->CIBlockPropertyEnum = new \CIBlockPropertyEnum();
        $this->CPrice = new \CPrice();
    }

    public function startUpdate($sections)
    {
        $this->bxSection = $sections;

        self::getIbElements();

        $zmElements = $this->getPriceList();

        $countProducts = count($zmElements);
        Progress::Limit($countProducts);
        AddMessage2Log("Начинаем. Всего товаров: ". $countProducts, "zoomos.tires2");

        foreach ($zmElements as $key => $zmElement) {

            $syncId = $zmElement['id'];

            $markPropUpd = $zmElement['itemDateUpdMillis'] ?? $zmElement['itemDateAddMillis'];
            $markPriceUpd = $zmElement['dateUpdMillis'] ?? $zmElement['dateAddMillis'];

            if (isset($this->bxElements[$syncId]))
            {
                if ($this->bxElements[$syncId]['PRICE_UPD'] != $markPriceUpd)
                {
                    self::updateOffer($zmElement, $this->bxElements[$syncId]['ID'], false);
                }

                if ($this->bxElements[$syncId]['PROP_UPD'] != $markPropUpd)
                {
                    #echo 'Товар ' . $this->bxElements[$syncId]['ID'] . ': ' . $this->bxElements[$syncId]['PROP_UPD'] . ' - ' . $markPropUpd . PHP_EOL;

                    $this->forUpdateProducts[$syncId] = $this->bxElements[$syncId];
                }
            }
            else
            {
                $this->newProducts[$syncId]['ID'] = self::createNewElement(array(
                    "ACTIVE" => "Y",
                    #"CREATED_BY" => 4,
                    "IBLOCK_ID" => Config::searchIblockId($zmElement['category']['id']),
                    "XML_ID" => $syncId,
                    "NAME" => $zmElement['vendor']['name'] . ' ' . $zmElement['model'],
                    "CODE" => $zmElement['linkRewrite'],
                    'PREVIEW_PICTURE' => \CFile::MakeFileArray(self::getImage($syncId)),
                ));

                $this->newProducts[$zmElement['id']]['IBLOCK_ID'] = Config::searchIblockId($zmElement['category']['id']);

                self::updateOffer($zmElement, $this->newProducts[$syncId]['ID'], true);
            }

            unset($this->bxElements[$syncId]);

            $this->progress++;
            Progress::setProgress(array('progress' => $this->progress));
        }

        foreach ($this->bxElements as $bxElement)
        {
            self::UpdateCatalogQuantity($bxElement['ID'], array('QUANTITY' => 0));
            AddMessage2Log("Установлен остаток равный 0 : ". $bxElement['ID'], "zoomos.tires2");

            $this->progressNoPrice++;
        }

        // Чистим память
        $this->bxElements = Null;
        $zmElements = Null;

        Progress::setProgress(
            array(
                'totalNew' => count($this->newProducts),
                'totalUpdate' => count($this->forUpdateProducts),
                'noPrice' => $this->progressNoPrice
            )
        );

        foreach ($this->newProducts as $key => $newProduct)
        {
            $data = self::getDetailElementFields($key);

            if ($this->bxSection[$data['CAT']['VENDOR']]['CHILD'][$data['CAT']['MODEL']]['ID'] == FALSE && $newProduct['IBLOCK_ID'] != 20)
            {
                if ($data['CAT']['MODEL'])
                {

                    $sectionId = Section::createNewCategories(array(
                        "IBLOCK_SECTION_ID" => $this->bxSection[$data['CAT']['VENDOR']]['ID'],
                        "IBLOCK_ID" => $newProduct['IBLOCK_ID'],
                        "NAME" => $data['CAT']['NAME'],
                        "XML_ID" => $data['CAT']['MODEL'],
                        'CODE' => Help::Translit($data['CAT']['NAME']) . '_' . $data['CAT']['VENDOR'],
                        'PICTURE' => $data['CAT']['PICTURE']
                    ));

                    if (is_numeric($sectionId))
                    {
                        $this->bxSection[$data['CAT']['VENDOR']]['CHILD'][$data['CAT']['MODEL']] = array(
                            'ID' => $sectionId,
                            'XML_ID' => $data['CAT']['MODEL']
                        );
                    }
                }

            }

            \CIBlockElement::SetElementSection(
                $newProduct['ID'],
                array(
                    $this->bxSection[$data['CAT']['VENDOR']]['CHILD'][$data['CAT']['MODEL']]['ID'] ?? $this->bxSection[$data['CAT']['VENDOR']]['ID']
                )
            );

            Property::setProps($newProduct['ID'], $data['FILTERS'], $newProduct['IBLOCK_ID']);

            $this->progressNew++;
            Progress::setProgress(array('new' => $this->progressNew));
        }

        // Чистим память
        $this->newProducts = Null;

        foreach ($this->forUpdateProducts as $key => $forUpdateProduct) {

            $data = self::getDetailElementFields($key);

            Property::setProps($forUpdateProduct['ID'], $data['FILTERS'], $forUpdateProduct['IBLOCK_ID']);

            AddMessage2Log("Обновлены значения фильтров элемента ID: ".$forUpdateProduct['ID'], "zoomos.tires2");

            $this->progressUpdate++;
            Progress::setProgress(array('update' => $this->progressUpdate));
        }

        // Чистим память
        $this->forUpdateProducts = Null;

    }

    public static function createNewElement($arFields)
    {
        $el = new \CIBlockElement;

        if($PRODUCT_ID = $el->Add($arFields))
            AddMessage2Log("Новый товар ID: ".$PRODUCT_ID, "zoomos.tires2");
        else
            AddMessage2Log("Error: ".$el->LAST_ERROR, "zoomos.tires2");
        return $PRODUCT_ID;
    }

    public static function getDetailElementFields($zmsID)
    {
        $url = str_replace(
            array('#ID#','#KEY#'),
            array($zmsID, Config::zms_config('ZMS_KEY')),
            Config::zms_config('ZMS_ITEM_LINK')
        );

        $data = Json::decode(Request::get($url));

        $props1 = static::getZElementFilters($data['filters']);

        $props2 = [
            array('CODE' => 'PROP_UPD','VALUE' => $data['dateUpdMillis'] ?? $data['dateAddMillis'],'TYPE' => 'S'),
            array('CODE' => 'WARRANTY','VALUE' => $data['warrantyInfo']['warrantyMonth'],'TYPE' => 'S'),
            array('CODE' => 'SUPPLIER','VALUE' => $data['warrantyInfo']['supplier'],'TYPE' => 'S'),
            array('CODE' => 'COUNTRY','VALUE' => $data['warrantyInfo']['country'],'TYPE' => 'S'),
            array('CODE' => 'PROIZVODITEL','VALUE' => $data['vendor']['name'],'TYPE' => 'S'),
            array('CODE' => 'SERVICE_CENTER','VALUE' => $data['warrantyInfo']['serviceCenters'],'TYPE' => 'S'),
            array('CODE' => 'MODEL_AVTOSHINY','VALUE' => $data['parentItem']['model'],'TYPE' => 'S'),
            array('CODE' => 'MODEL_DISKA','VALUE' => $data['parentItem']['model'],'TYPE' => 'S'),
        ];

        $array['FILTERS'] = array_merge($props1, $props2);

        $array['CAT'] = [
            'VENDOR' => $data['vendor']['id'],
            'MODEL' => $data['parentItem']['id'],
            'NAME' => $data['parentItem']['model'],
            'PICTURE' => \CFile::MakeFileArray(self::getImage($data['parentItem']['id']))
        ];

        return $array;
    }

    /** Получаем все свойства элемента */
    public static function getZElementFilters($filters)
    {
        $props = [];

        for ($i = 0; $i < count($filters); $i++){
            $code = Property::OverrideProps($filters[$i]['id']);
            $val = $filters[$i]['values'][0]['name'];

            switch ($filters[$i]['id']){
                case 8632:
                    $xmlID = Property::OverridePropsShipyXmlId($val);
                    break;
                default:
                    $xmlID = Property::OverridePropsXmlId(Help::Translit($val));
            }

            $props[$i] = [
                'CODE' => $code,
                'XML_ID' => $xmlID,
                'VALUE' => $val,
                'TYPE' => Property::DetermineProp($filters[$i]['type'])
            ];
        }

        return $props;
    }

    /** Получаем главное фото элемента и сохраняем в папку */
    public static function getImage($id)
    {
        $file = str_replace(
            '#ID#',
            $id,
            Config::zms_config('ZMS_PATH_ITEM_IMG')
        );

        $pathImg = str_replace(
            '#ID#',
            $id,
            Config::zms_config('ZMS_ITEM_IMG_LINK')
        );

        if (!file_exists($file)) {
            $zoomos_img = Request::get(($pathImg)); //картинка
            file_put_contents($file, $zoomos_img);
        }

        return $file;

    }

    protected function updateOffer($arElement, $PRODUCT_ID, $new = FALSE){

        if ($new == TRUE)
        {
            // Добавляем элементу парметры товара
            $this->CCatalogProduct::Add(
                array(
                    'ID' => $PRODUCT_ID,
                    'QUANTITY' => (int)str_replace('>', '', $arElement['supplierInfo']['quantity']),
                    'TYPE'=>'1'
                )
            );

            AddMessage2Log("Добавлены параметры товара элементу : ". $PRODUCT_ID, "zoomos.tires2");
        }
        else
        {
            // обновляем количество доступное!
            self::UpdateCatalogQuantity($PRODUCT_ID, array('QUANTITY' => str_replace('>', '', $arElement['supplierInfo']['quantity'])));
            AddMessage2Log("Обновлен цена и остаток: ". $PRODUCT_ID, "zoomos.tires2");

            $this->progressUpdatePrice++;
        }

        // обновляем цену
        self::UpdateCatalogPrice(
            $PRODUCT_ID,
            $arElement['price'],
            $arElement['priceCurrency']
        );

        $mark = $arElement['dateUpdMillis'] ?? $arElement['dateAddMillis'];

        Property::setPropTypeS(
            $PRODUCT_ID,
            Config::searchIblockId($arElement['category']['id']),
            array('PRICE_UPD' => (string) $mark)
        );


    }

    /**
     * Установить цену
     * @param int $ProductID
     * @param float $Price
     * @param string $Currency
     */
    public function UpdateCatalogPrice($ProductID, $Price, $Currency)
    {
        if (\CModule::IncludeModule("catalog")) {
            $this->CPrice::SetBasePrice($ProductID, $Price, $Currency);
        }
    }


    /**
     * Обновить остаток
     * @param int $PRODUCT_ID
     * @param array $arFileds
     */
    public function UpdateCatalogQuantity($PRODUCT_ID, $arFileds)
    {
        if (\CModule::IncludeModule("catalog")) {
            $this->CCatalogProduct::Update($PRODUCT_ID, $arFileds);
        }
    }


    /** Получаем список элементов инфоблока */
    private function getIbElements()
    {
        $res = $this->CIBlockElement::GetList(
            false,
            array(
                'IBLOCK_ID' => Config::getIBlocks(),
                'ACTIVE' => 'Y'
            ),
            Array(
                'IBLOCK_ID',
                'ID',
                'XML_ID',
                'PROPERTY_PRICE_UPD',
                'PROPERTY_PROP_UPD',
            )
        );

        while ($element = $res->Fetch()){
            $this->bxElements[$element['XML_ID']] = [
                'IBLOCK_ID' => $element['IBLOCK_ID'],
                'ID' => $element['ID'],
                'PRICE_UPD' => $element['PROPERTY_PRICE_UPD_VALUE'],
                'PROP_UPD' => $element['PROPERTY_PROP_UPD_VALUE']
            ];
        }
    }


    /** Получаем прайс */
    public function getPriceList()
    {
        $url = str_replace(
            '#KEY#',
            Config::zms_config('ZMS_KEY'),
            Config::zms_config('ZMS_SHORT_PRICE_LINK')
        );

        return Json::decode(Request::get($url));
    }


    public static function UpdateElementFields($ID, $arFields)
    {
        if (\CModule::IncludeModule("iblock")) {
            $CIBlockElement = new \CIBlockElement();
            $CIBlockElement->Update($ID, $arFields);
        }
    }


}