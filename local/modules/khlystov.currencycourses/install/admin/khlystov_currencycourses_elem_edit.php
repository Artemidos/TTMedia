<?php
    // определяем в какой папке находится модуль, если в bitrix, инклудим файл с меню из папки bitrix
    if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/khlystov.currencycourses/")) {
        // присоединяем и копируем файл
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/khlystov.currencycourses/admin/khlystov_currencycourses_elem_edit.php");
    }

    // определяем в какой папке находится модуль, если в local, инклудим файл с меню из папки local
    if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/khlystov.currencycourses/")) {
        // присоединяем и копируем файл
        require_once($_SERVER["DOCUMENT_ROOT"] . "/local/modules/khlystov.currencycourses/admin/khlystov_currencycourses_elem_edit.php");
    }
