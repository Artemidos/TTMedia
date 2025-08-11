<?php
    /**
     * @global $DB
     */

    use Bitrix\Main\Loader,
        Bitrix\Main\Localization\Loc,
        Bitrix\Main\Type\DateTime,
        \Khlystov\CurrencyCourses\DataTable;

    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
    Loader::includeModule('khlystov.currencycourses');
    Loc::loadMessages(__FILE__);

    if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/khlystov.currencycourses/")) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/khlystov.currencycourses/prolog_admin.php");
    }

    if (is_dir($_SERVER["DOCUMENT_ROOT"] . "/local/modules/khlystov.currencycourses/")) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/local/modules/khlystov.currencycourses/prolog_admin.php");
    }

    $POST_RIGHT = $APPLICATION->GetGroupRight("khlystov.currencycourses");

    if ($POST_RIGHT == "D") {
        $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
    }

    $aTabs = [
      ["DIV" => "edit1", "TAB" => GetMessage("currencycourses_tab_edit"), "ICON" => "main_user_edit", "TITLE" => GetMessage("currencycourses_tab_title")],
    ];

    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $ID = intval($_REQUEST["ID"] ?? 0);
    $message = null;
    $bVarsFromForm = false;

    $currencyCourse = new DataTable;

    // ******************************************************************** //
    //                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
    // ******************************************************************** //
    $save = $_POST["save"] ?? "";
    $apply = $_POST["apply"] ?? "";

    if (
      $_SERVER["REQUEST_METHOD"] === "POST"
      && ($save !== "" || $apply !== "")
      && $POST_RIGHT == "W"
      && check_bitrix_sessid()
    ) {
        $ACTIVE = ($_POST["ACTIVE"] ?? "N") === "Y" ? "Y" : "N";
        $CODE   = trim($_POST["CODE"] ?? "");
        $COURSE = trim(str_replace(',', '.', $_POST["COURSE"]) ?? "");

        $arFields = [
          "ACTIVE" => $ACTIVE,
          "CODE"   => $CODE,
          "COURSE" => number_format(floatval($COURSE), 4, '.', ''),
        ];

        // Валидация обязательных полей
        $errors = [];
        if ($CODE === "") {
            $errors[] = GetMessage("currencycourses_code_required");
        }
        if ($COURSE === "" || !is_numeric($COURSE)) {
            $errors[] = GetMessage("currencycourses_course_invalid");
        }

        if (empty($errors)) {
            if ($ID > 0) {
                $result = $currencyCourse::update($ID, $arFields);
            } else {
                $result = $currencyCourse::add($arFields);
                if ($result->isSuccess()) {
                    $ID = $result->getId();
                }
            }

            if ($result->isSuccess()) {
                if ($apply !== "") {
                    LocalRedirect("/bitrix/admin/khlystov_currencycourses_elem_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
                } else {
                    LocalRedirect("/bitrix/admin/khlystov_currencycourses_list.php?lang=" . LANG);
                }
            } else {
                $message = new CAdminMessage([
                  "MESSAGE" => GetMessage("MAIN_ERROR"),
                  "DETAILS" => implode("<br>", $result->getErrorMessages()),
                  "TYPE"    => "ERROR"
                ]);
                $bVarsFromForm = true;
            }
        } else {
            $message = new CAdminMessage([
              "MESSAGE" => GetMessage("MAIN_ERROR"),
              "DETAILS" => implode("<br>", $errors),
              "TYPE"    => "ERROR"
            ]);
            $bVarsFromForm = true;
        }
    }

    // ******************************************************************** //
    //                ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ                     //
    // ******************************************************************** //
    $str_ACTIVE = "Y";
    $str_CODE   = "";
    $str_COURSE = "";

    if ($ID > 0) {
        $currencyCourseElem = $currencyCourse::getById($ID)->fetch();
        if ($currencyCourseElem) {
            $str_ACTIVE = $currencyCourseElem["ACTIVE"];
            $str_CODE   = $currencyCourseElem["CODE"];
            $str_COURSE = $currencyCourseElem["COURSE"];
        } else {
            $ID = 0;
        }
    }

    if ($bVarsFromForm) {
        $str_ACTIVE = ($_POST["ACTIVE"] ?? "N") === "Y" ? "Y" : "N";
        $str_CODE   = $_POST["CODE"] ?? "";
        $str_COURSE = $_POST["COURSE"] ?? "";
    }

    $APPLICATION->SetTitle(($ID > 0 ? GetMessage("currencycourses_title_edit") . $ID : GetMessage("currencycourses_title_add")));
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

    // ******************************************************************** //
    //                КОНФИГУРАЦИЯ МЕНЮ                                     //
    // ******************************************************************** //
    $aMenu = [
      [
        "TEXT"  => GetMessage("currencycourses_list"),
        "TITLE" => GetMessage("currencycourses_list_title"),
        "LINK"  => "khlystov_currencycourses_list.php?lang=" . LANG,
        "ICON"  => "btn_list",
      ]
    ];

    if ($ID > 0) {
        $aMenu[] = [
          "TEXT"  => GetMessage("currencycourses_add"),
          "TITLE" => GetMessage("currencycourses_mnu_add"),
          "LINK"  => "khlystov_currencycourses_elem_edit.php?lang=" . LANG,
          "ICON"  => "btn_new",
        ];
        $aMenu[] = [
          "TEXT"  => GetMessage("currencycourses_delete"),
          "TITLE" => GetMessage("currencycourses_mnu_del"),
          "LINK"  => "javascript:if(confirm('" . GetMessage("currencycourses_mnu_del_conf") . "'))window.location='khlystov_currencycourses_list.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
          "ICON"  => "btn_delete",
        ];
    }

    $context = new CAdminContextMenu($aMenu);
    $context->Show();

    // ******************************************************************** //
    //                ВЫВОД СООБЩЕНИЙ                                       //
    // ******************************************************************** //
    if ($_REQUEST["mess"] === "ok" && $ID > 0) {
        CAdminMessage::ShowMessage(["MESSAGE" => GetMessage("currencycourses_saved"), "TYPE" => "OK"]);
    }

    if ($message) {
        echo $message->Show();
    }
?>

    <form method="POST" action="<?=$APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
        <?=bitrix_sessid_post();?>
        <?php $tabControl->Begin(); ?>
        <?php $tabControl->BeginNextTab(); ?>
        <tr>
            <td width="40%"><?=GetMessage("currencycourses_act")?></td>
            <td width="60%">
                <input type="checkbox" name="ACTIVE" value="Y" <?php if ($str_ACTIVE == "Y") echo "checked"; ?>>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("currencycourses_code")?><span class="required">*</span></td>
            <td>
                <input type="text" name="CODE" value="<?=htmlspecialcharsbx($str_CODE)?>">
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("currencycourses_course")?><span class="required">*</span></td>
            <td>
                <input type="text" name="COURSE" value="<?=htmlspecialcharsbx($str_COURSE)?>" size="30" maxlength="100">
            </td>
        </tr>
        <?php
            $tabControl->Buttons([
              "disabled" => ($POST_RIGHT < "W"),
              "back_url" => "khlystov_currencycourses_list.php?lang=" . LANG,
            ]);
        ?>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <?php if ($ID > 0): ?>
        <input type="hidden" name="ID" value="<?=$ID?>">
        <?php endif; ?>
        <?php $tabControl->End(); ?>
        <?php $tabControl->ShowWarnings("post_form", $message); ?>

        <?php echo BeginNote(); ?>
        <span class="required">*</span><?=GetMessage("REQUIRED_FIELDS")?>
        <?php echo EndNote(); ?>
    </form>

<?php
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
