<?php
namespace ZMS;

use Bitrix\Main\Config\Option;

class Config
{

    public static function zms_config($key)
    {
        $option = array(
            'ZMS_KEY' => Option::get("zoomos.tires2", "ZOOMOS_KEY"),
            'ZMS_JSON_LINK' => 'https://api.zoomos.by/',
            'ZMS_PRICE_LINK' => 'https://api.zoomos.by/pricelist?key=#KEY#',
            'ZMS_SHORT_PRICE_LINK' => 'https://api.zoomos.by/pricelist?key=#KEY#&warrantyInfo=0&competitorInfo=0&deliveryInfo=0',
            'ZMS_SHORT_PRICE_LINK_OFFSET' => 'https://api.zoomos.by/pricelist?key=#KEY#&warrantyInfo=0&competitorInfo=0&deliveryInfo=0&offset=#OFFSET#&limit=#LIMIT#',
            'ZMS_CATEGORIES_LINK' => 'https://api.zoomos.by/categories?key=#KEY#',
            'ZMS_CATEGORY_FILTERS_LINK' => 'https://api.zoomos.by/category/#ID#/filters?key=#KEY#',
            'ZMS_CATEGORY_OFFERS_LINK' => 'https://api.zoomos.by/category/#ID#/offers?key=#KEY#',
            'ZMS_VENDORS_LINK' => 'https://api.zoomos.by/dict/vendors/json?key=#KEY#',
            'ZMS_ITEM_LINK' => 'https://api.zoomos.by/item/#ID#?key=#KEY#',
            'ZMS_ITEM_IMG_LINK' => 'https://api.zoomos.by/img/item/#ID#/main',
            'ZMS_ITEM_IMG_MORE_LINK' => 'https://api.zoomos.by/img/item/#ID#/#NUM#',
            'ZMS_PATH_CACHE' => $_SERVER['DOCUMENT_ROOT'] . '/zoomos/cache/',
            'ZMS_PATH' => $_SERVER['DOCUMENT_ROOT'] . '/zoomos/',
            'ZMS_LOG' => $_SERVER['DOCUMENT_ROOT'] . '/zoomos/log/',
            'ZMS_PATH_ITEM_IMG' => $_SERVER['DOCUMENT_ROOT'] . '/zoomos/cache/img/#ID#_image_product.jpeg',
            'ZMS_PATH_ITEM_IMG_MORE' => $_SERVER['DOCUMENT_ROOT'] . '/zoomos/cache/img/#ID#_image_product_#NUM#.jpeg',
            'ZOOMOS_LIMIT' => Option::get("zoomos.tires2", "ZOOMOS_LIMIT"),
        );

        return $option[$key];
    }


    public static function searchIblockId($zSectionID){
        $bxId = [
            '633' => Option::get("zoomos.tires2", "ZOOMOS_IBLOCK_SHINY_ID"),
            '634' => Option::get("zoomos.tires2", "ZOOMOS_IBLOCK_DISKI_ID"),
            '1842' => Option::get("zoomos.tires2", "ZOOMOS_IBLOCK_AKB_ID"),
        ];

        return $bxId[$zSectionID];
    }

    public static function getCategories()
    {
        return array(633, 634, 1842);
    }

    public static function getIBlocks()
    {
        return array(
            Option::get("zoomos.tires2", "ZOOMOS_IBLOCK_SHINY_ID"),
            Option::get("zoomos.tires2", "ZOOMOS_IBLOCK_DISKI_ID"),
            Option::get("zoomos.tires2", "ZOOMOS_IBLOCK_AKB_ID")
        );
    }

}
