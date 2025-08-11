<?php
    use Bitrix\Main\Loader;

    if (!Loader::includeModule('khlystov.currencycourses')) {
        return;
    }

    Loader::registerAutoLoadClasses(
      'khlystov.currencycourses',
      array(
        'Khlystov\\CurrencyCourses\\DataTable' => 'lib/Data.php'
      )
    );