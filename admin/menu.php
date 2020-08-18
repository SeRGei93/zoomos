<?

$aMenu = array(
    'parent_menu' => 'global_menu_content',
    'sort' => 10,
    "text" => "Импорт прайсов Zoomos.by",
    "title" => "Интеграция с Zoomos.by",
    "icon" => "workflow_menu_icon",
    "page_icon" => "workflow_menu_icon",
    "url" => "zoomos_upload.php?lang=".LANGUAGE_ID,
    "items_id" => "menu_zoomostires2",
//    'items' => array(
//        array(
//            "title" => "Загрузка шин",
//            "text" => "Загрузка шин",
//            "url" => "zoomos_upload.php?lang=".LANGUAGE_ID,
//            "module_id" => "bxfun_zoomos",
//            "items_id" => "menu_bxfunzms",
//        ),
//    )
);

return (!empty($aMenu) ? $aMenu : false);
?>
