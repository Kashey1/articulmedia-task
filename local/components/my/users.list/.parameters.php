<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

Loader::includeModule("iblock");

$arComponentParameters = array(
	"GROUPS"          => array(
	),
	"PARAMETERS"      => array(
		"AJAX_MODE"   => array(),
		"USER_COUNT"  => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::getMessage("PARAM_USER_COUNT"),
			"TYPE"    => "STRING",
			"DEFAULT" => "2",
		),
		"CACHE_TIME"  => array("DEFAULT" => 36000000),
	),
);

CIBlockParameters::AddPagerSettings(
	$arComponentParameters,
	Loc::getMessage("T_IBLOCK_DESC_PAGER_NEWS"),
	true,
	true,
	true,
	$arCurrentValues["PAGER_BASE_LINK_ENABLE"] === "Y"
);
