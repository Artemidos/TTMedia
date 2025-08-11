<?php
    namespace Khlystov\CurrencyCourses;

    use \Bitrix\Main\ArgumentException,
        \Bitrix\Main\Entity\Event,
        \Bitrix\Main\ObjectPropertyException,
        \Bitrix\Main\SystemException,
        \Bitrix\Main\Entity\FieldError,
        \Bitrix\Main\Entity\EventResult,
        \Bitrix\Main\Type\DateTime,
        \Khlystov\CurrencyCourses\DataTable;

    class Events {
        public static function onBeforeAdd(Event $event) {
            $result = new EventResult;
            $arFields = $event->getParameter("fields");

            $dbRes = DataTable::getList([
              'filter' => [
                'CODE' => $arFields['CODE']
              ],
              'limit' => 1
            ])->fetchAll();

            if (!empty($dbRes)) {
                $result->addError(
                  new FieldError(
                    $event->getEntity()->getField("CODE"),
                    "Курс валюты с таким символьным кодом уже существует"
                  )
                );
            } else {
                $arFields['DATE'] = new DateTime(date("d.m.Y H:i:s"));
                $result->modifyFields($arFields);
            }

            return $result;
        }

        public static function onAdd(Event $event) {

        }

        /**
         * @param Event $event
         * @return void
         * @throws ArgumentException
         * @throws ObjectPropertyException
         * @throws SystemException
         */
        public static function onAfterAdd(Event $event): void
        {
            DataTable::clearCache();
        }

        public static function onBeforeUpdate(Event $event) {
            $result = new EventResult;
            $arFields = $event->getParameter("fields");

            $dbRes = DataTable::getList([
              'filter' => [
                'CODE' => $arFields['CODE']
              ],
              'limit' => 1
            ])->fetchAll();

            if (!empty($dbRes)) {
                $result->addError(
                  new FieldError(
                    $event->getEntity()->getField("CODE"),
                    "Курс валюты с таким символьным кодом уже существует"
                  )
                );
            } else {
                $arFields['DATE'] = new \Bitrix\Main\Type\DateTime(date("d.m.Y H:i:s"));
                $result->modifyFields($arFields);
            }

            return $result;
        }

        public static function onUpdate(Event $event) {

        }


        /**
         * @param Event $event
         * @return void
         * @throws ArgumentException
         * @throws ObjectPropertyException
         * @throws SystemException
         */
        public static function onAfterUpdate(Event $event): void
        {

            DataTable::clearCache();
        }

        public static function onBeforeDelete(Event $event) {

        }

        public static function onDelete(Event $event) {

        }

        /**
         * @param Event $event
         * @return void
         * @throws ArgumentException
         * @throws ObjectPropertyException
         * @throws SystemException
         */
        public static function onAfterDelete(Event $event): void
        {

            DataTable::clearCache();
        }
    }