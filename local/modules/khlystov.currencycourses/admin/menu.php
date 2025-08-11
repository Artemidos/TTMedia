<?php
    defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

    use Bitrix\Main\Localization\Loc;

    global $APPLICATION;

    Loc::loadMessages(__FILE__);

    $MODULE_RIGHT = $APPLICATION->GetGroupRight('khlystov.currencycourses');

    if ($MODULE_RIGHT > "D") {
        // сформируем верхний пункт меню
        $aMenu = array(
          // пункт меню в разделе Настройки
          'parent_menu' => 'global_menu_settings',
          // сортировка
          'sort' => 1,
          // название пункта меню
          'text' => "Хлыстов: Курсы Валют",
          // идентификатор ветви
          "items_id" => "menu_currencycourses",
          // иконка
          "icon" => "currencycourses_menu_icon",
          // идентификатор модуля
          "module_id" => "khlystov.currencycourses",
        );

        // дочерняя ветка меню
        $aMenu["items"][] =  array(
          // название подпункта меню
          'text' => 'Список курсов валют',
          // ссылка для перехода
          'url' => 'khlystov_currencycourses_list.php?lang=' . LANGUAGE_ID
        );

        // возвращаем основной массив $aMenu
        return $aMenu;
    } else {
        return false;
    }
