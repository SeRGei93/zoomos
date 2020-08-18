<?php

namespace ZMS\Helper;

class Help
{

    /** Ищем родительский раздел */
    public static function SearchSectionParent($parent, $ib)
    {
        $id = false;

        if(!empty($parent)){

            $arFilter = array('IBLOCK_ID' => $ib, 'XML_ID' => $parent);

            $dbResult = \CIBlockSection::GetList(array('sort'=>'asc'), $arFilter, false, false);

            $item = $dbResult->GetNext();

            $id = $item['ID'];
        }

        return $id;
    }


    /**
     * Транслитерация
     * @param $name
     * @return mixed
     */
    public static function Translit($name)
    {
        $alf = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '',    'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        $title = strtr($name, $alf);
        $title = mb_strtolower($title);
        $title = str_replace('+', '_plus', $title);
        $title = preg_replace('~[^-a-z0-9_]+~u', '_', $title);
        $title = trim($title, '_');

        return substr($title, 0, 50);
    }

}