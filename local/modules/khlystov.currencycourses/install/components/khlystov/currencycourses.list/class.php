<?php
    use \Bitrix\Main\Loader,
        \Bitrix\Main\Application,
        \Bitrix\Main\Localization\Loc,
        \Bitrix\Main\SystemException,
        \Bitrix\Main\Type\Date,
        \Bitrix\Main\UI\PageNavigation;

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
    }

    class CurrencyCoursesList extends CBitrixComponent {
        private const MODULE_ID = 'khlystov.currencycourses';

        public function executeComponent(): void {
            try {
                $this->_checkModules();
                $this->onIncludeComponentLang();
                $this->onPrepareComponentParams();
                $this->getResult();
            } catch (SystemException $e) {
                ShowError($e->getMessage());
            }
        }

        /**
         * @throws SystemException
         */
        private function _checkModules(): void
        {
            if (!Loader::includeModule(self::MODULE_ID)) {
                throw new SystemException(GetMessage('KCC_MODULE_NOT_INSTALLED'));
            }
        }

        public function onIncludeComponentLang()
        {
            Loc::loadMessages(__FILE__);
        }

        public function onPrepareComponentParams($arParams = array()): array {
            if ($arParams['CACHE_TYPE'] !== 'N') {
                if (!isset($arParams['CACHE_TIME']) || empty($arParams['CACHE_TIME'])) {
                    $arParams['CACHE_TIME'] = 3600;
                } else {
                    $arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
                }
            } else {
                $arParams['CACHE_TIME'] = 0;
            }



            return $arParams;
        }


        protected function getFilter(): array
        {
            $filter = ['ACTIVE' => 'Y'];

            if (!empty($this->arParams['FILTER'])) {
                foreach ($this->arParams['FILTER'] as $key => $value) {
                    if (!$value) continue;

                    switch ($key) {
                        case 'CODE':
                            $filter['%CODE'] = $value;
                            break;
                        case 'COURSE_FROM':
                            $filter['>=COURSE'] = $value;
                            break;
                        case 'COURSE_TO':
                            $filter['<=COURSE'] = $value;
                            break;
                        case 'DATE_FROM':
                            $filter['>=DATE'] = new Date($value, 'Y-m-d');
                            break;
                        case 'DATE_TO':
                            $filter['<=DATE'] = new Date($value, 'Y-m-d');
                            break;
                    }
                }
            }

            return $filter;
        }

        /**
         * @throws \Bitrix\Main\ArgumentException
         * @throws \Bitrix\Main\ObjectPropertyException
         * @throws SystemException
         */
        protected function getResult()
        {
            $this->arResult['ITEMS'] = [];

            $nav = new PageNavigation("currencycourses-list");
            $nav->allowAllRecords(true)
              ->setPageSize($this->arParams['ELEMENTS_COUNT']);

            if (isset($_REQUEST['page'])) {
                $nav->setCurrentPage((int)$_REQUEST['page']);
            } else {
                $nav->initFromUri();
            }

            $fieldsSelect = empty($this->arParams['LIST_FIELD_CODE'])
              ? ['DATE', 'CODE', 'COURSE']
              : $this->arParams['LIST_FIELD_CODE'];

            $filter = array_filter($this->getFilter(), function($element) {
                return $element !== "";
            });

            $totalCount = \Khlystov\CurrencyCourses\DataTable::getCount($filter);
            $nav->setRecordCount($totalCount);

            $cacheKey = md5(serialize([
              self::MODULE_ID,
              $nav->getCurrentPage(),
              $nav->getPageSize()
            ]));

            if ($this->startResultCache($this->arParams['CACHE_TIME'], $cacheKey))
            {
                $taggedCache = Application::getInstance()->getTaggedCache(); // Служба пометки кеша тегами
                $taggedCache->abortTagCache();
                $taggedCache->startTagCache(SITE_ID . '/khlystov');
                $taggedCache->registerTag(self::MODULE_ID);

                // Запрос к инфоблоку через класс ORM
                $res = \Khlystov\CurrencyCourses\DataTable::getList([
                  'select' => $fieldsSelect,
                  'filter' => $filter,
                  'limit'  => $nav->getLimit(),
                  'offset' => $nav->getOffset(),
                  'count_total' => false,
                  'cache' => [
                    'ttl' => $this->arParams['CACHE_TIME'],
                    'cache_joins' => true
                  ]
                ]);

                // Формируем массив arResult
                while ($arItem = $res->fetch()) {
                    $this->arResult['ITEMS'][] = $arItem;
                }

                $this->arResult['NAV_OBJECT'] = $nav;

                if (!empty($this->arResult['ITEMS'])) {
                    // ключи $arResult перечисленные при вызове этого метода, будут доступны в component_epilog.php
                    // и ниже по коду, обратите внимание там будет другой $arResult
                    $this->SetResultCacheKeys(array());

                    // подключаем шаблон и сохраняем кеш
                    $this->IncludeComponentTemplate();
                }

                // Если что-то пошло не так и решили кеш не записывать
                $cacheInvalid = false;
                if ($cacheInvalid) {
                    $taggedCache->abortTagCache();
                    $this->abortResultCache();
                }

                $taggedCache->endTagCache();
            }

            global $APPLICATION;
            $APPLICATION->SetTitle(Loc::getMessage('KCC_BROWSER_TITLE'));
        }
    }