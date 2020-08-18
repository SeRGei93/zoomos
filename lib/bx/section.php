<?php

namespace ZMS\Bx;

use ZMS\Config;
use ZMS\Helper\Help;


class Section
{

    /**
     * @var array
     */
    public $allBXVendor = array();


    public function startUpdate(){
        self::getAllIblockVendor();
        $this->sectionListUpdate(self::getZMSVendors());
    }

    private function sectionListUpdate($zmsSections)
    {
        foreach ($zmsSections as $zmsSection) {
            if (!isset($this->allBXVendor[$zmsSection['XML_ID']])) {
                $this->allBXVendor[$zmsSection['XML_ID']] = [
                    'ID' => self::createNewCategories($zmsSection),
                    'IBLOCK_ID' => $zmsSection['IBLOCK_ID']
                ];
            }
        }
    }

    /**
     * @param $zmsSection
     * @return int
     */
    public static function createNewCategories($zmsSection)
    {
        $bs = new \CIBlockSection();

        if (!$zmsSection['CODE']){
            $code = Help::Translit($zmsSection['NAME']);
        }else{
            $code = $zmsSection['CODE'];
        }

        $arFields = [
            "ACTIVE" => 'Y',
            "IBLOCK_SECTION_ID" => $zmsSection['IBLOCK_SECTION_ID'] ?? false,
            "IBLOCK_ID" => $zmsSection['IBLOCK_ID'],
            "NAME" => $zmsSection['NAME'],
            "XML_ID" => $zmsSection['XML_ID'],
            'CODE' => $code,
            "SORT" => 100,
            'PICTURE' => $zmsSection['PICTURE'] ?? false,
        ];

        $ID = $bs->Add($arFields);

        $res = ($ID>0);
        if(!$res)
            echo $bs->LAST_ERROR . PHP_EOL;
        return $ID;
    }


    private function getAllIblockVendor()
    {
        $iblocks = Config::getIBlocks();

        for ($i = 0; $i < count($iblocks); $i++){
            self::getIblockVendor($iblocks[$i]);
        }
    }


    private function getIblockVendor($iblock)
    {

        $arFilter = Array(
            'IBLOCK_ID'=> $iblock,
            'DEPTH_LEVEL' => 1
        );

        $db_list = \CIBlockSection::GetList(
            false,
            $arFilter,
            true,
            Array(
                'ID',
                'IBLOCK_SECTION_ID',
                'IBLOCK_ID',
                'XML_ID',
                'NAME'
            )
        );

        while($ar_result = $db_list->GetNext())
        {
            $this->allBXVendor[$ar_result['XML_ID']] = [
                'ID' => $ar_result['ID'],
                'IBLOCK_ID' => $ar_result['IBLOCK_ID'],
                'XML_ID' => $ar_result['XML_ID'],
                'NAME' => $ar_result['NAME'],
                'CHILD' => self::getSectionChild($ar_result['ID'])
            ];
        }

    }


    public static function getSectionChild($id){

        $rsParentSection = \CIBlockSection::GetByID($id);

        $result = [];

        if ($arParentSection = $rsParentSection->GetNext())
        {
            $arFilter = array(
                'IBLOCK_ID' => $arParentSection['IBLOCK_ID'],
                '>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],
                '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],
                '>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']
            );

            $rsSect = \CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);

            while ($arSect = $rsSect->GetNext())
            {
                $result[$arSect['XML_ID']] = [
                    'ID' => $arSect['ID'],
                    'IBLOCK_ID' => $arSect['IBLOCK_ID'],
                    'XML_ID' => $arSect['XML_ID'],
                    'NAME' => $arSect['NAME']
                ];
            }
        }

        return $result;
    }

    public static function getZMSVendors()
    {
        $allCategories = Config::getCategories();
        $array = [];

        for ($i = 0; $i < count($allCategories); $i++)
        {
            $str = Property::getFilter($allCategories[$i]);

            $props = $str['vendors'];

            foreach ($props as $lev1) {
                $array[$lev1['id']] = [
                    'IBLOCK_ID' => Config::searchIblockId($allCategories[$i]),
                    'XML_ID' => $lev1['id'],
                    'NAME' => $lev1['name']
                ];
            }
        }

        return $array;
    }


}