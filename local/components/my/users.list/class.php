<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Context;
use \Bitrix\Main\Engine\Contract\Controllerable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loader::includeModule("iblock");

class UsersList extends CBitrixComponent implements Controllerable
{
    public static $filePath = "/upload/my-export/";

    private function getAllUsers() {
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
        return array('exportCsv' => array('prefilters' => array()));
    }

    public function exportCsvAction()
    {
        $fileName = date("Y-m-d_H-i-s").'_users.csv';
        $file = fopen($_SERVER["DOCUMENT_ROOT"].UsersList::$filePath.$fileName, 'w');

        if ($file) {

            $users = $this->getAllUsers();

            fputcsv($file, array("ID", "EMAIL", "LOGIN", "DATE_REGISTER"));

            while ($arRes = $users->fetch()) {
                $arCsv = array(
                   "ID"            => $arRes["ID"],
                   "EMAIL"         => $arRes["EMAIL"],
                   "LOGIN"         => $arRes["LOGIN"],
                   "DATE_REGISTER" => $arRes["DATE_REGISTER"]
                );

                fputcsv($file, $arCsv);
            }
        } else {
            return error_get_last();
        }

        fclose($file);

        return UsersList::$filePath.$fileName;
    }

    public function exportXmlAction()
    {
        $fileName = date("Y-m-d_H-i-s").'_users.xml';
        $file = fopen($_SERVER["DOCUMENT_ROOT"].UsersList::$filePath.$fileName, 'w');

        if ($file) {

            $users = $this->getAllUsers();
            $xml = new SimpleXMLElement('<users/>');

            while ($arRes = $users->fetch()) {
                $user = $xml->addChild('user');
                $user->addChild('id', $arRes["ID"]);
                $user->addChild('email', $arRes["EMAIL"]);
                $user->addChild('login', $arRes["LOGIN"]);
                $user->addChild('dateregister', $arRes["DATE_REGISTER"]);
            }

            fwrite($file, $xml->asXML());

        } else {
            return error_get_last();
        }

        fclose($file);

        return UsersList::$filePath.$fileName;
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

        $request = Context::getCurrent()->getRequest();

        $nav = new \Bitrix\Main\UI\PageNavigation("PAGE");
        $nav->allowAllRecords(true)->setPageSize(intval($this->arParams["USER_COUNT"]))->initFromUri();

        $users = \Bitrix\Main\UserTable::getList(
            array(
                "order" => array("ID" => "ASC"),
                "select" => array("*"),
                "count_total" => true,
                "limit" => $nav->getLimit(),
                "offset" => $nav->getOffset(),
                "cache" => array(
                    "ttl" => intval($this->arParams["CACHE_TIME"]),
                )
            )
        );

        $nav->setRecordCount($users->getCount());
        $this->arResult['PAGER'] = $nav;

        while ($arRes = $users->fetch()) {
           $this->arResult['USERS'][] = $arRes;
        }

        $this->initEvents();
        $this->includeComponentTemplate();

        return false;
    }
}
