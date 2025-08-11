<?php
    namespace Khlystov\CurrencyCourses;

    use Bitrix\Main\Entity\DataManager,
        Bitrix\Main\Entity,
        Bitrix\Main\Application;

    /**
     * Class DataTable
     *
     * Fields:
     * <ul>
     * <li>ID int mandatory</li>
     * <li>CODE string mandatory</li>
     * <li>DATE datetime mandatory</li>
     * <li>COURSE float mandatory</li>
     * </ul>
     *
     * @package \Khlystov\CurrencyCourses\Data
     */
    class DataTable extends DataManager
    {
        private const CACHE_TAG = 'khlystov.currencycourses';
        private const TABLE_NAME = 'khlystov_currencycourses';
        /**
         * Returns DB table name for entity.
         *
         * @return string
         */
        public static function getTableName(): string {
            return self::TABLE_NAME;
        }

        // Подключение к БД. Если не указывать, то будет использовано значение по умолчанию подключения из
        // файла .settings.php. Если указать, то можно выбрать подключение, которое может быть описано в .setting.php
        /**
         * Returns default connection name.
         *
         * @return string
         */
        public static function getConnectionName(): string
        {
            return "default";
        }

        /**
         * Returns entity map definition.
         *
         * @return array[]
         */
        public static function getMap(): array {
            return array(
              new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
              )),
              new Entity\StringField('CODE', array(
                'required' => true,
              )),
              new Entity\BooleanField('ACTIVE', array(
                'values' => array('Y', 'N')
              )),
              new Entity\DatetimeField('DATE'),
              new Entity\FloatField('COURSE'),
            );
        }

        /**
         * @return void
         * @throws \Bitrix\Main\ArgumentException
         * @throws \Bitrix\Main\ObjectPropertyException
         * @throws \Bitrix\Main\SystemException
         */
        // основной метод очистки кеша по тегу
        public static function clearCache(): void
        {
            // служба пометки кеша тегами
            $taggedCache = Application::getInstance()->getTaggedCache();
            $taggedCache->clearByTag(self::CACHE_TAG);
        }
    }