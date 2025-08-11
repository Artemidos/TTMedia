<?php
    use Bitrix\Main\Localization\Loc,
        Bitrix\Main\Loader,
        Bitrix\Main\EventManager,
        Bitrix\Main\Application,
        Bitrix\Main\Entity\Base;

    Loc::loadMessages(__FILE__);

    class khlystov_currencycourses extends CModule {
        public $MODULE_ID = 'khlystov.currencycourses';
        public $MODULE_VERSION;
        public $MODULE_VERSION_DATE;
        public $MODULE_NAME;
        public $MODULE_DESCRIPTION;

        public function __construct()
        {
            $arModuleVersion = array();

            include(__DIR__.'/version.php');

            if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
            {
                $this->MODULE_VERSION = $arModuleVersion["VERSION"];
                $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            }

            $this->MODULE_NAME = Loc::getMessage("KCC_INSTALL_NAME");
            $this->MODULE_DESCRIPTION = Loc::getMessage("KCC_INSTALL_DESCRIPTION");
            $this->PARTNER_NAME = 'Хлыстов Артемий';
            $this->PARTNER_URI = 'http://example.ru';
        }

        /**
         * Module installation.
         *
         * @return true
         */
        public function DoInstall(): true {
            RegisterModule($this->MODULE_ID);

            $this->InstallDB();
            $this->AddDefaultData();
            $this->InstallEvents();
            $this->InstallFiles();

            return true;
        }

        /**
         * Module uninstallation.
         *
         * @return true
         */
        public function DoUninstall() {
            $this->UnInstallDB();
            $this->UnInstallEvents();
            $this->UnInstallFiles();

            UnRegisterModule($this->MODULE_ID);

            return true;
        }

        /**
         * Database table creation.
         *
         * @return true
         * @throws \Bitrix\Main\ArgumentException
         * @throws \Bitrix\Main\LoaderException
         * @throws \Bitrix\Main\SystemException
         */
        public function InstallDB() {
            // Подключаем модуль для того что бы был видем класс ORM
            Loader::includeModule($this->MODULE_ID);

            // Через класс Application получаем соединение по переданному параметру,
            // параметр берем из ORM-сущности (он указывается, если необходим другой тип подключения, отличный от default),
            // если тип подключения по умолчанию, то параметр можно не передавать.
            // Далее по подключению вызываем метод isTableExists,
            // в который передаем название таблицы полученное с помощью метода getDBTableName() класса Base
            if (!Application::getConnection(\Khlystov\CurrencyCourses\DataTable::getConnectionName())->isTableExists(Base::getInstance("\Khlystov\CurrencyCourses\DataTable")->getDBTableName())) {
                // eсли таблицы не существует, то создаем её по ORM сущности
                Base::getInstance("\Khlystov\CurrencyCourses\DataTable")->createDbTable();
            }

            return true;
        }

        /**
         * Database table deletion.
         *
         * @return true
         * @throws \Bitrix\Main\ArgumentException
         * @throws \Bitrix\Main\ArgumentNullException
         * @throws \Bitrix\Main\DB\SqlQueryException
         * @throws \Bitrix\Main\LoaderException
         * @throws \Bitrix\Main\SystemException
         */
        public function UnInstallDB() {
            // подключаем модуль для того что бы был видем класс ORM
            Loader::includeModule($this->MODULE_ID);

            \Khlystov\CurrencyCourses\DataTable::clearCache();

            // делаем запрос к бд на удаление таблицы, если она существует, по подключению к бд класса Application с параметром подключения ORM сущности
            Application::getConnection(\Khlystov\CurrencyCourses\DataTable::getConnectionName())->queryExecute('DROP TABLE IF EXISTS ' . Base::getInstance("\Khlystov\CurrencyCourses\DataTable")->getDBTableName());

            return true;
        }

        /**
         * Events installation.
         *
         * @return true
         */
        public function InstallEvents(): true
        {
            $eventManager = EventManager::getInstance();

            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnBeforeAdd',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onBeforeAdd'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAdd',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAdd'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAfterAdd',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAfterAdd'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnBeforeUpdate',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onBeforeUpdate'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnUpdate',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onUpdate'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAfterUpdate',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAfterUpdate'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnBeforeDelete',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onBeforeDelete'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnDelete',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onDelete'
            );
            $eventManager->registerEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAfterDelete',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAfterDelete'
            );

            unset($eventManager);

            return true;
        }

        /**
         * Events uninstallation.
         *
         * @return true
         */
        public function UnInstallEvents(): true
        {
            $eventManager = EventManager::getInstance();

            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnBeforeAdd',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onBeforeAdd'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAdd',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAdd'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAfterAdd',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAfterAdd'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnBeforeUpdate',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onBeforeUpdate'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnUpdate',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onUpdate'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAfterUpdate',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAfterUpdate'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnBeforeDelete',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onBeforeDelete'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnDelete',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onDelete'
            );
            $eventManager->unRegisterEventHandler(
              $this->MODULE_ID,
              '\Khlystov\CurrencyCourses\Data::OnAfterDelete',
              $this->MODULE_ID,
              '\\Khlystov\\CurrencyCourses\\Events',
              'onAfterDelete'
            );

            unset($eventManager);

            return true;
        }

        /**
         * Module files copying.
         *
         * @return true
         */
        public function InstallFiles(): true
        {
            CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . "/bitrix/admin", true, true);
            CopyDirFiles(__DIR__ . '/components', $_SERVER['DOCUMENT_ROOT'] . "/local/components", true, true);

            return true;
        }

        /**
         * Module files deletion.
         *
         * @return true
         */
        public function UnInstallFiles(): true
        {
            DeleteDirFiles(__DIR__ . '/admin', $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

            if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/components/khlystov")) {
                DeleteDirFilesEx(
                  "/local/components/khlystov"
                );
            }

            return true;
        }

        /**
         * Default recordings creation.
         *
         * @return true
         * @throws \Bitrix\Main\LoaderException
         * @throws \Bitrix\Main\ObjectException
         */
        public function AddDefaultData(): true {
            Loader::includeModule($this->MODULE_ID);

            \Khlystov\CurrencyCourses\DataTable::add(
              array(
                'CODE' => 'USD',
                'ACTIVE' => 'Y',
                'DATE' => new \Bitrix\Main\Type\DateTime(date("d.m.Y H:i:s")),
                'COURSE' => '80'
              )
            );

            \Khlystov\CurrencyCourses\DataTable::add(
              array(
                'CODE' => 'EUR',
                'ACTIVE' => 'Y',
                'DATE' => new \Bitrix\Main\Type\DateTime(date("d.m.Y H:i:s")),
                'COURSE' => '90'
              )
            );

            \Khlystov\CurrencyCourses\DataTable::add(
              array(
                'CODE' => 'SEK',
                'ACTIVE' => 'Y',
                'DATE' => new \Bitrix\Main\Type\DateTime(date("d.m.Y H:i:s")),
                'COURSE' => '81'
              )
            );

            \Khlystov\CurrencyCourses\DataTable::add(
              array(
                'CODE' => 'TJS',
                'ACTIVE' => 'Y',
                'DATE' => new \Bitrix\Main\Type\DateTime(date("d.m.Y H:i:s")),
                'COURSE' => '83'
              )
            );

            return true;
        }
    }