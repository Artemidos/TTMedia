<?php
    use Bitrix\Main\Loader;
    use Bitrix\Main\Application;

    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);
    define('BX_NO_ACCELERATOR_RESET', true);

    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

    $APPLICATION->RestartBuffer();

    $request = Application::getInstance()->getContext()->getRequest();
    $filter = $request->getPostList()->toArray();

    $APPLICATION->IncludeComponent(
      'khlystov:currencycourses.list',
      '',
      [
        'FILTER' => $filter,
        'ELEMENTS_COUNT' => 20
      ]
    );

    die();