<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Context;
use \Bitrix\Main\Engine\Contract\Controllerable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loader::includeModule("iblock");

interface Save {
    public function insert($data, $file);
}

class saveFile implements Save
{
    public $filePath;
    public $file;

    function __construct($type)
    {
        $this->filePath = $_SERVER["DOCUMENT_ROOT"]."/upload/my-export/";
        $this->file = tempnam($this->filePath, $type.'_');
        rename($this->file, $this->file .= '.'.$type);
    }

    protected function openFile() {
        return fopen($this->file, 'w');
    }

    public function insert($data, $type) {

        if ($type && count($data) > 0) {

            $openedFile = $this->openFile();

            switch ($type) {
                case 'csv':
                    foreach ($data as $userItem) {
                        $arCsv = array(
                           "ID"            => $userItem["ID"],
                           "EMAIL"         => $userItem["EMAIL"],
                           "LOGIN"         => $userItem["LOGIN"],
                           "DATE_REGISTER" => $userItem["DATE_REGISTER"]
                        );

                        if (!fputcsv($openedFile, $arCsv)) {
                            return error_get_last();
                        }
                    }

                    break;

                case 'xml':
                    $xml = new SimpleXMLElement('<users/>');

                    foreach ($data as $userItem) {
                        $user = $xml->addChild('user');
                        $user->addChild('id', $userItem["ID"]);
                        $user->addChild('email', $userItem["EMAIL"]);
                        $user->addChild('login', $userItem["LOGIN"]);
                        $user->addChild('dateregister', $userItem["DATE_REGISTER"]);
                    }

                    if (!fwrite($openedFile, $xml->asXML())) {
                        return error_get_last();
                    }

                    break;
            }

            $this->closeFile($openedFile);

        } else {
            return false;
        }

        chmod($this->file, 644);
        return str_replace($_SERVER["DOCUMENT_ROOT"], "", $this->file);
    }

    protected function closeFile($file) {
        return fclose($file);
    }
}



class UsersList extends CBitrixComponent implements Controllerable
{

    protected function getAllUsers() {
        $users = \Bitrix\Main\UserTable::getList(
            array(
                "order" => array("ID" => "ASC"),
                "select" => array("*"),
            )
        );

        return $users;
    }

    public function configureActions()
    {
        return array('export' => array('prefilters' => array()));
    }

    public function exportAction($type)
    {
        if ($type) {

            $file = new saveFile($type);
            $users = $this->getAllUsers();
            $arUsers = [];

            while ($arRes = $users->fetch()) {
                $arUsers[] = $arRes;
            }
        }

        return $file->insert($arUsers, $type);
    }

    public function onPrepareComponentParams($params = array()){

		if ($params) {
			$params["PARAMS"] = json_encode($params);

			return $params;
		}

		return false;
	}

    private function initEvents() {

		EventManager::getInstance()->addEventHandler(
			"main",
			"OnBeforeEndBufferContent",
			function() {
				CJSCore::Init(
					array(
						"jquery"
					)
				);
			}
		);

		return false;
	}

    public function executeComponent() {

        $pageRequest = Context::getCurrent()->getRequest()->getQuery("PAGE");

        if (!$pageRequest)
            $pageRequest = "page-1";


        $cache_id = md5(serialize($this->arParams)."_".$pageRequest);
        $cache_dir = "/tagged_userslist";
        $obCache = new CPHPCache;

        if($obCache->InitCache(intval($this->arParams["CACHE_TIME"]), $cache_id, $cache_dir)) {
            $this->arResult = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $nav = new \Bitrix\Main\UI\PageNavigation("PAGE");
            $nav->allowAllRecords(true)->setPageSize(intval($this->arParams["USER_COUNT"]))->initFromUri();

            global $CACHE_MANAGER;
            $CACHE_MANAGER->StartTagCache($cache_dir);
            $CACHE_MANAGER->RegisterTag("users_list");



            $users = \Bitrix\Main\UserTable::getList(
                array(
                    "order" => array("ID" => "ASC"),
                    "select" => array("*"),
                    "count_total" => true,
                    "limit" => $nav->getLimit(),
                    "offset" => $nav->getOffset(),
                )
            );

            while ($arRes = $users->fetch()) {
                $CACHE_MANAGER->RegisterTag("user_id_".$arRes["ID"]);
                $this->arResult['USERS'][] = $arRes;
            }

            $nav->setRecordCount($users->getCount());
            $this->arResult['PAGER'] = $nav;

            $CACHE_MANAGER->EndTagCache();
            $obCache->EndDataCache($this->arResult);
        }

        $this->initEvents();
        $this->includeComponentTemplate();

        return false;
    }
}
