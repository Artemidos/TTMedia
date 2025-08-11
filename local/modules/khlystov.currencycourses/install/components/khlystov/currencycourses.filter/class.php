<?php
    use Bitrix\Main\Engine\Contract\Controllerable;
    use Bitrix\Main\Context;

    if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

    class CurrencyCoursesFilterComponent extends CBitrixComponent implements Controllerable
    {
        public function configureActions(): array{
            return [];
        }

        protected function getFilterData(): array
        {
            $filter = [];
            foreach ($this->arParams['FIELDS'] as $field) {
                $value = $this->request->getPost($field) ?: $this->request->getQuery($field);
                if ($value !== null && $value !== '') {
                    $filter[$field] = $value;
                }
            }
            return $filter;
        }

        public function executeComponent()
        {
            $this->arResult['FIELDS'] = $this->arParams['FIELDS'];
            $this->arResult['FILTER'] = $this->getFilterData();
            $this->IncludeComponentTemplate();
        }
    }