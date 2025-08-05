<?php
    use Bitrix\Main\Loader;
    use Bitrix\Main\Application;

    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);
    define('BX_NO_ACCELERATOR_RESET', true);

    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

    $APPLICATION->RestartBuffer();

    $request = Application::getInstance()->getContext()->getRequest();
    $filterData = $request->getPostList()->toArray();

    header('Content-Type: application/json');
    echo json_encode(['filter' => $filterData]);
    die();