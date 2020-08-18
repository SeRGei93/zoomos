<?php

namespace ZMS\Bx;

use ZMS\Helper\Help;
use ZMS\Helper\Json;
use ZMS\Helper\Request;
use ZMS\Config;


class Property
{


    /** Устанавливаем свойства элемента */
    public static function setProps($id, $props, $iblock)
    {
        $CIBlockPropertyEnum = new \CIBlockPropertyEnum();
        $CIBlockElement = new \CIBlockElement();

        for ($i = 0; $i <= count($props); $i++){

            if($props[$i]['CODE'] == 'TIP_AVTOSHINY' ||
                $props[$i]['CODE'] == 'INDEKS_SKOROSTI' ||
                $props[$i]['CODE'] == 'SEALING_METHOD' ||
                $props[$i]['CODE'] == 'KONSTRUKTSIYA'
            ){
                $props[$i]['TYPE'] = 'S';
            }


            switch ($props[$i]['TYPE']) {
                case 'L':
                    $property_enums = $CIBlockPropertyEnum::GetList(
                        false,
                        Array(
                            'IBLOCK_ID'=> $iblock,
                            'CODE'=>$props[$i]['CODE'],
                            'XML_ID'=> $props[$i]['XML_ID']
                        )
                    );
                    if($enum_fields = $property_enums->GetNext())
                    {
                        if ($enum_fields['XML_ID'] == $props[$i]['XML_ID']){
                            $CIBlockElement::SetPropertyValuesEx(
                                $id,
                                $iblock,
                                array(
                                    $props[$i]['CODE'] => $enum_fields['ID']
                                )
                            );
                        }
                    }
                    break;
                default:
                    $CIBlockElement::SetPropertyValues(
                        $id,
                        $iblock,
                        $props[$i]['VALUE'],
                        $props[$i]['CODE']
                    );
            }
        }
    }

    public static function setPropTypeS($id, $iblock, $array)
    {
        $CIBlockElement = new \CIBlockElement();

        foreach ($array as $k => $v) {
            $CIBlockElement::SetPropertyValues($id, $iblock, $v, $k);
        }
    }


    /**
     * Заменяем значения свойств
     */
    public static function OverridePropsValue($val)
    {
        $alf = array(
            'да' => 'Y', 'есть' => 'Y', 'нет' => 'N'
        );

        return strtr($val, $alf);
    }


    /**
     * Заменяем значения свойств
     */
    public static function OverridePropsShipyXmlId($val)
    {
        $alf = array(
            'да' => 'ship', 'есть' => 'ship', 'нет' => 'no_ship'
        );

        return strtr($val, $alf);
    }


    /**
     * Заменяем xml-id на подходящие для aspro.tires2
     */
    public static function OverridePropsXmlId($val)
    {
        $alf = array(
            'zimnie' => 'winter',
            'letnie' => 'summer',
            'shipovannye' => 'ship',
            'neshipovannye' => 'no_ship',
            'est' => 'Y',
            'obratnaya' => 'REVERSE',
        );

        return strtr(Help::Translit($val), $alf);
    }


    /**
     * Даем свойствам другой символьный код
     */
    public static function OverrideProps($prop)
    {
        $alf = array(
            '8628' => 'SHIRINA_PROFILYA',
            '8629' => 'VYSOTA_PROFILYA',
            '8630' => 'POSADOCHNYY_DIAMETR',
            '8631' => 'SEZONNOST',
            '8632' => 'SHIPY',
            '8633' => 'TIP_AVTOSHINY',
            '8634' => 'KONSTRUKTSIYA',
            '8635' => 'KONSTRUKTSIYA_AVTOSHINY',
            '8636' => 'SEALING_METHOD',
            '8637' => 'INDEKS_NAGRUZKI',
            '8639' => 'INDEKS_SKOROSTI',
            '8640' => 'WHEEL_TYPE',
            '8642' => 'SHIRINA_DISKA',
            '8643' => 'POSADOCHNYY_DIAMETR_DISKA',
            '8644' => 'COUNT_OTVERSTIY',
            '8645' => 'MEZHBOLTOVOE_RASSTOYANIE',
            '8646' => 'DIAMETR_STUPITSY',
            '8647' => 'VYLET_DISKA',
            '8648' => 'DISK_COLOR',
            '13633' => 'VOLTAGE',
            '13634' => 'EMKOST',
            '13635' => 'COLD_SCROLL',
            '13636' => 'POLARNOST',
            '13637' => 'LENGHT',
            '13638' => 'WIDTH',
            '13639' => 'HEIGHT',
            '13640' => 'WEIGHT',
        );

        return strtr($prop, $alf);
    }



    /**
     * Определяем тип свойства
     * @param $str
     * @return string
     */
    public static function DetermineProp($str)
    {
        $alf = array(
            'numeric' => 'N', 'enum' => 'L', 'bool' => 'L', 'string' => 'S', 'E' => 'E'
        );

        return strtr($str, $alf);
    }


    /** Получаем список свойств для фильтра из категории */
    public static function getFilter($section)
    {
        $url = str_replace(
            array('#KEY#', '#ID#'),
            array(Config::zms_config('ZMS_KEY'), $section),
            Config::zms_config('ZMS_CATEGORY_FILTERS_LINK')
        );

        return Json::decode(Request::get($url));
    }

}