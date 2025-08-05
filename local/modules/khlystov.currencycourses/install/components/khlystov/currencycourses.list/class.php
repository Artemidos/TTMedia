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
            if (!isset($arParams['CACHE_TIME'])) {
                $arParams['CACHE_TIME'] = 3600;
            } else {
                $arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);
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

        public function executeComponent(): void {
            try {
                $this->_checkModules();
                $this->getResult();
            } catch (SystemException $e) {
                ShowError($e->getMessage());
            }
        }

        protected function getResult()
        {
            $fieldsSelect = empty($this->arParams['LIST_FIELD_CODE'])
              ? ['DATE', 'CODE', 'COURSE']
              : $this->arParams['LIST_FIELD_CODE'];

            $filter = array_filter($this->getFilter(), function($element) {
                return $element !== "";
            });

            // Создаем объект навигации
            $nav = new PageNavigation("page");
            $nav->allowAllRecords(false)
              ->setPageSize($this->arParams['ELEMENTS_COUNT']) // количество элементов на странице
              ->initFromUri();

            // если нет валидного кеша, получаем данные из БД
            if ($this->startResultCache(false, [$filter, $nav->getCurrentPage()])) {
                $totalCount = \Khlystov\CurrencyCourses\DataTable::getCount($filter);
                $nav->setRecordCount($totalCount);

                // Запрос к инфоблоку через класс ORM
                $res = \Khlystov\CurrencyCourses\DataTable::getList([
                  'select' => $fieldsSelect,
                  'filter' => $filter,
                  'limit'  => $nav->getLimit(),
                  'offset' => $nav->getOffset(),
                ]);
                // Формируем массив arResult
                $this->arResult['ITEMS'] = [];
                while ($arItem = $res->fetch()) {
                    $this->arResult['ITEMS'][] = $arItem;
                }

                $this->arResult['NAV_OBJECT'] = $nav;
                // кэш не затронет весь код ниже, он будет выполняться на каждом хите,
                // здесь работаем с другим $arResult, будут доступны только те ключи массива,
                // которые перечислены в вызове SetResultCacheKeys()
                if (isset($this->arResult)) {
                    // ключи $arResult перечисленные при вызове этого метода, будут доступны в component_epilog.php и ниже по коду, обратите внимание там будет другой $arResult
                    $this->SetResultCacheKeys(
                      array()
                    );
                    // подключаем шаблон и сохраняем кеш
                    $this->IncludeComponentTemplate();
                }
            }
        }
    }