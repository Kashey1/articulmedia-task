<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?php
    use Bitrix\Main\Localization\Loc;
    Loc::loadMessages(__FILE__);
?>

<?if (count($arResult['USERS']) > 0) {?>

    <div class="users-functional-buttons">
        <a id="users-export-csv" href="#"><?=Loc::getMessage('EXPORT_CSV')?></a>
        <a id="users-export-xml" href="#"><?=Loc::getMessage('EXPORT_XML')?></a>
    </div>

    <div class="users-block-wr">
        <div class="users-block">
            <?foreach ($arResult['USERS'] as $user) {?>
                <div class="users-block-item">
                    <div class="users-block-item-field">
                        <?=$user["ID"]?>
                    </div>
                    <div class="users-block-item-field">
                        <?=$user["EMAIL"]?>
                    </div>
                    <div class="users-block-item-field">
                        <?=$user["LOGIN"]?>
                    </div>
                    <div class="users-block-item-field">
                        <?=$user["DATE_REGISTER"]?>
                    </div>
                </div>
            <?}?>
        </div>
    </div>
<?}?>


<?
$APPLICATION->IncludeComponent(
   "bitrix:main.pagenavigation",
   "",
   array(
        "NAV_OBJECT" => $arResult["PAGER"],
        "SEF_MODE" => "N",
   ),
   false
);
?>
