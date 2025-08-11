<?php
    use Bitrix\Main\Loader,
        Bitrix\Main\Localization\Loc;

    // подключим все необходимые файлы:
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
    Loader::includeModule('khlystov.currencycourses'); // инициализация модуля
    Loc::loadMessages(__FILE__);

    if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/khlystov.currencycourses/")) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/khlystov.currencycourses/prolog_admin.php");
    }

    if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/khlystov.currencycourses/")) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/local/modules/khlystov.currencycourses/prolog_admin.php");
    }

    // явного подключения языкового файла не требуется,
    // получим права доступа текущего пользователя на модуль
    $POST_RIGHT = $APPLICATION->GetGroupRight("khlystov.currencycourses");

    // если нет прав - отправим к форме авторизации с сообщением об ошибке
    if ($POST_RIGHT == "D")
        $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
    
    // здесь будет вся серверная обработка и подготовка данных
    $cData = new \Khlystov\CurrencyCourses\DataTable;
    $sTableID = "khlystov_currencycourses"; // ID таблицы
    $oSort = new CAdminSorting($sTableID, "ID", "ASC"); // объект сортировки
    $lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка
    $by = mb_strtoupper($oSort->getField());
    $order = mb_strtoupper($oSort->getOrder());

    // ******************************************************************** //
    //                           ФИЛЬТР                                     //
    // ******************************************************************** //
    // опишем элементы фильтра
    $FilterArr = Array(
      "find_id",
      "find_date_from",
      "find_date_to",
      "find_active",
      "find_code",
      "find_course_from",
      "find_course_to"
    );

    global $find_id, $find_date_from, $find_date_to, $find_active, $find_code, $find_course_from, $find_course_to;

    // инициализируем фильтр
    $lAdmin->InitFilter($FilterArr);

    // создадим массив фильтрации для выборки на основе значений фильтра
    $arFilter = [];

    if (!empty($find_id)) $arFilter['=ID'] = (int)$find_id;
    if (!empty($find_date_from)) $arFilter['>=DATE'] = new \Bitrix\Main\Type\Date($find_date_from);
    if (!empty($find_date_to)) $arFilter['<=DATE'] = new \Bitrix\Main\Type\Date($find_date_to);
    if (!empty($find_course_from)) $arFilter['>=COURSE'] = floatval($find_course_from);
    if (!empty($find_course_to)) $arFilter['<=COURSE'] = floatval($find_course_to);
    if (!empty($find_active)) $arFilter['=ACTIVE'] = $find_active;
    if (!empty($find_code)) $arFilter['%CODE'] = $find_code;

    // ******************************************************************** //
    //                ОБРАБОТКА ДЕЙСТВИЙ НАД ЭЛЕМЕНТАМИ СПИСКА              //
    // ******************************************************************** //
    // сохранение отредактированных элементов
    if($lAdmin->EditAction() && $POST_RIGHT=="W")
    {
        // пройдем по списку переданных элементов
        foreach($lAdmin->GetEditFields() as $ID=>$arFields)
        {
            // сохраним изменения каждого элемента
            $ID = IntVal($ID);
            if(($rsData = $cData::getById($ID)) && ($arData = $rsData->Fetch()))
            {
                foreach($arFields as $key=>$value) {
                    $arData[$key] = $value;
                }

                if (isset($arData['CODE'])) {
                    $exists = $cData::getList([
                      'filter' => ['=CODE' => $arData['CODE'], '!ID' => $ID],
                      'select' => ['ID']
                    ])->fetch();

                    if ($exists) {
                        $lAdmin->AddGroupError(Loc::getMessage("currencycourses_code_exists"), $ID);
                        continue;
                    }
                }

                if(!$cData::update($ID, $arData)->isSuccess())
                {
                    $lAdmin->AddGroupError(Loc::getMessage("currencycourses_save_error")." ".$cData->LAST_ERROR, $ID);
                }
            }
            else
            {
                $lAdmin->AddGroupError(Loc::getMessage("currencycourses_save_error")." ".Loc::getMessage("currencycourses_no_currencycourses"), $ID);
            }
        }
    }

    // обработка одиночных и групповых действий
    if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W") {
      // если выбрано "Для всех элементов"
      if ($lAdmin->IsGroupActionToAll())
      {
          $arID = array();
          $rsData = $cData::getList([
            'order' => array($by=>$order),
            'filter' => $arFilter
          ]);

          while($arRes = $rsData->Fetch())
              $arID[] = $arRes['ID'];
      }

      $action = $lAdmin->GetAction();

      // пройдем по списку элементов
      foreach($arID as $ID)
      {
          if(strlen($ID)<=0)
              continue;
          $ID = IntVal($ID);

          // для каждого элемента совершим требуемое действие
          switch($action)
          {
              // удаление
              case "delete":
                  @set_time_limit(0);
                  if(!$cData::delete($ID)->isSuccess())
                  {
                      $lAdmin->AddGroupError(Loc::getMessage("currencycourses_del_err"), $ID);
                  }
                  break;

              // активация/деактивация
              case "activate":
              case "deactivate":
                  if(($rsData = $cData::getById($ID)) && ($arFields = $rsData->Fetch()))
                  {
                      $arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
                      if(!$cData::update($ID, $arFields)->isSuccess())
                          $lAdmin->AddGroupError(Loc::getMessage("currencycourses_save_error").$cData->LAST_ERROR, $ID);
                  }
                  else
                      $lAdmin->AddGroupError(Loc::getMessage("currencycourses_save_error")." ".Loc::getMessage("currencycourses_no_currencycourses"), $ID);
                  break;
          }
      }
    }

    // ******************************************************************** //
    //                ВЫБОРКА ЭЛЕМЕНТОВ СПИСКА                              //
    // ******************************************************************** //
    // выберем список
    $rsData = $cData::getList([
      'order' => array($by=>$order),
      'filter' => $arFilter
    ]);

    // преобразуем список в экземпляр класса CAdminResult
    $rsData = new CAdminResult($rsData, $sTableID);

    // аналогично CDBResult инициализируем постраничную навигацию.
    $rsData->NavStart();

    // отправим вывод переключателя страниц в основной объект $lAdmin
    $lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("currencycourses_nav")));

    // ******************************************************************** //
    //                ПОДГОТОВКА СПИСКА К ВЫВОДУ                            //
    // ******************************************************************** //
    $lAdmin->AddHeaders(array(
      array(
        "id"    =>"ID",
        "content"  =>"ID",
        "sort"     =>"id",
        "default"  =>true,
      ),
      array(
        "id"    =>"DATE",
        "content"  =>Loc::getMessage("currencycourses_date"),
        "sort"     =>"date",
        "default"  =>true,
      ),
      array(
        "id"    =>"CODE",
        "content"  =>Loc::getMessage("currencycourses_code"),
        "sort"     =>"code",
        "default"  =>true,
      ),
      array(
        "id"    =>"ACTIVE",
        "content"  =>Loc::getMessage("currencycourses_act"),
        "sort"     =>"act",
        "default"  =>true,
      ),
      array(
        "id"    =>"COURSE",
        "content"  =>Loc::getMessage("currencycourses_course"),
        "sort"     =>"course",
        "default"  =>true,
      ),
    ));

    while($arRes = $rsData->NavNext(true, "f_")) {
        // Создаем строку, результат - экземпляр класса CAdminListRow
        $f_ID = $arRes["ID"];
        $row =& $lAdmin->AddRow($f_ID, $arRes);
        // далее настроим отображение значений при просмотре и редактировании списка
        $row->AddViewField("CODE", $arRes['CODE']);
        $row->AddViewField("ACTIVE", $arRes['ACTIVE']);
        $row->AddViewField('COURSE', $arRes['COURSE']);

        // сформируем контекстное меню
        $arActions = array();
        // редактирование элемента
        $arActions[] = array(
          "ICON" => "edit",
          "DEFAULT" => true,
          "TEXT" => Loc::getMessage("currencycourses_edit"),
          "ACTION" => $lAdmin->ActionRedirect("khlystov_currencycourses_elem_edit.php?ID=" . $f_ID)
        );

        // удаление элемента
        if ($POST_RIGHT >= "W") {
            $arActions[] = array(
              "ICON" => "delete",
              "TEXT" => Loc::getMessage("currencycourses_del"),
              "ACTION" => "if(confirm('" . Loc::getMessage(
                  'currencycourses_del_conf'
                ) . "')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
            );
        }

        // применим контекстное меню к строке
        $row->AddActions($arActions);
    }

    // резюме таблицы
    $lAdmin->AddFooter(
      array(
        array("title"=>Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
        array("counter"=>true, "title"=>Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
      )
    );

    // групповые действия
    $lAdmin->AddGroupActionTable(Array(
      "delete"=>Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
      "activate"=>Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), // активировать выбранные элементы
      "deactivate"=>Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // деактивировать выбранные элементы
    ));

    // сформируем меню из одного пункта
    $aContext = array(
      array(
        "TEXT"=>Loc::getMessage("CURRENCY_COURSE_ADD"),
        "LINK"=>"khlystov_currencycourses_elem_edit.php?lang=".LANG,
        "TITLE"=>Loc::getMessage("POST_ADD_TITLE"),
        "ICON"=>"btn_new",
      ),
    );

    // и прикрепим его к списку
    $lAdmin->AddAdminContextMenu($aContext);

    // ******************************************************************** //
    //                ВЫВОД                                                 //
    // ******************************************************************** //
    // установим заголовок страницы
    $APPLICATION->SetTitle(Loc::getMessage("currencycourses_title"));

    // альтернативный вывод
    $lAdmin->CheckListMode();

    // не забудем разделить подготовку данных и вывод
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

    // ******************************************************************** //
    //                ВЫВОД ФИЛЬТРА                                         //
    // ******************************************************************** //
    // альтернативный вывод
    $lAdmin->CheckListMode();
    // создадим объект фильтра
    $oFilter = new CAdminFilter(
      $sTableID."_filter",
      array(
        "ID",
        Loc::getMessage("currencycourses_f_active"),
        Loc::getMessage("currencycourses_f_date"),
        Loc::getMessage("currencycourses_f_code"),
        Loc::getMessage("currencycourses_f_course"),
      )
    );
    ?>
    <form name="find_form" method="get" action="<?=$APPLICATION->GetCurPage();?>">
        <?php
            $oFilter->Begin();
        ?>
        <tr>
        	<td><?="ID"?>:</td>
        	<td>
        		<input type="text" name="find_id" size="47" value="<?=htmlspecialchars($find_id)?>">
        	</td>
        </tr>
        <tr>
        	<td><?=Loc::getMessage("currencycourses_f_active")?>:</td>
        	<td>
        		<?php
        		$arr = array(
        			"reference" => array(
        			Loc::getMessage("POST_YES"),
        			Loc::getMessage("POST_NO"),
        		),
        		"reference_id" => array(
        			"Y",
        			"N",
        		)
        	);

        	echo SelectBoxFromArray("find_active", $arr, $find_active, Loc::getMessage("POST_ALL"), "");
          ?>
        	</td>
        </tr>
        <tr>
        	<td><?=Loc::getMessage("currencycourses_f_date")?>:</td>
            <td><?=CalendarPeriod("find_date_from", $find_date_from ?? '', "find_date_to", $find_date_to  ?? '', "find_form", "Y")?></td>
        </tr>
        <tr>
        	<td><?=Loc::getMessage("currencycourses_f_code")?>:</td>
        	<td><input type="text" name="find_code" value="<?=htmlspecialchars($find_code)?>"></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage("currencycourses_f_course")?>:</td>
            <td>
                <?=Loc::getMessage("currencycourses_from");?>
                <input type="text" name="find_course_from" value="<?=floatval($find_course_from)?>">
                <?=Loc::getMessage("currencycourses_to");?>
                <input type="text" name="find_course_to" value="<?=floatval($find_course_to)?>">
            </td>
        </tr>
        <?php
            $oFilter->Buttons(array(
              "table_id"=>$sTableID,
              "url"=>$APPLICATION->GetCurPage(),
              "form"=>"find_form"
            ));
            $oFilter->End();
        ?>
    </form>
<?php
    // выведем таблицу списка элементов
    $lAdmin->DisplayList();

    // завершение страницы
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
