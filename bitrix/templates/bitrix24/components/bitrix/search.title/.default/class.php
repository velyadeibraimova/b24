<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Intranet\Integration\Socialnetwork\Chat\GroupChat;
use Bitrix\Intranet\Integration\Socialnetwork\Url\GroupUrl;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\Collab\CollabFeature;

final class CB24SearchTitle
{
	final public static function getUsers($searchString = false)
	{
		global $USER, $DB;

		$result = array();

		if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		$searchStringOriginal = $searchString;
//		$searchString = str_replace('%', '', $searchString)."%";

		$userNameTemplate = CSite::GetNameFormat(false);
		$userPageURLTemplate = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/', SITE_ID).'user/#user_id#/';

		$userFilter = array(
			'=ACTIVE' => 'Y',
			'!=EXTERNAL_AUTH_ID' => \Bitrix\Main\UserTable::getExternalUserTypes(),
		);

		$searchByEmail = false;
		if (!empty($searchString))
		{
			$matchesPhones = [];
			$phoneParserManager = \Bitrix\Main\PhoneNumber\Parser::getInstance();
			preg_match_all('/'.$phoneParserManager->getValidNumberPattern().'/i', $searchString, $matchesPhones);

			if (
				!empty($matchesPhones)
				&& !empty($matchesPhones[0])
			)
			{
				foreach($matchesPhones[0] as $phone)
				{
					$convertedPhone = \Bitrix\Main\PhoneNumber\Parser::getInstance()
						->parse($phone)
						->format(\Bitrix\Main\PhoneNumber\Format::E164);
					$searchString = str_replace($phone, $convertedPhone, $searchString);
				}
			}

			$findFilter = \Bitrix\Main\UserUtils::getAdminSearchFilter([
				'FIND' => $searchString
			]);
			if (!empty($findFilter))
			{
				$userFilter = array_merge($userFilter, $findFilter);
			}
/*
			$searchStringList = preg_split('/\s+/', trim(ToUpper($searchString)));
			array_walk(
				$searchStringList,
				function (&$val, $key)
				{
					$val = str_replace('%', '', $val) . '%';
				}
			);

			if (count($searchStringList) == 2)
			{
				$userFilter[] = array(
					'LOGIC' => 'OR',
					array('LOGIC' => 'AND', 'NAME' => $searchStringList[0], 'LAST_NAME' => $searchStringList[1]),
					array('LOGIC' => 'AND', 'NAME' => $searchStringList[1], 'LAST_NAME' => $searchStringList[0]),
				);
			}
			else
			{
				$subFilter = [
					'LOGIC' => 'OR',
					'NAME' => $searchString,
					'LAST_NAME' => $searchString,
					'WORK_POSITION' => $searchString,
				];

				if (check_email($searchStringOriginal, true))
				{
					$searchByEmail = true;
					$subFilter['=EMAIL_OK'] = 1;
				}

				$userFilter[] = $subFilter;
			}
*/
		}

		if (
			!\CSocNetUser::isCurrentUserModuleAdmin()
			&& \Bitrix\Main\Loader::includeModule('extranet')
		)
		{
			$myGroupUsersList = \CExtranet::getMyGroupsUsersSimple(\CExtranet::getExtranetSiteId());
			if (!empty($myGroupUsersList))
			{
				if (!\CExtranet::isIntranetUser())
				{
					$userFilter['@ID'] = $myGroupUsersList;
				}
				else
				{
					$userFilter[] = array(
						'LOGIC' => 'OR',
						'!UF_DEPARTMENT' => false,
						'@ID' => $myGroupUsersList
					);
				}
			}
			else
			{
				if (!\CExtranet::isIntranetUser())
				{
					$userFilter['=ID'] = $USER->getId();
				}
				else
				{
					$userFilter['!UF_DEPARTMENT'] = false;
				}
			}
		}

		$selectFields = [
			'ID', 'ACTIVE', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'WORK_POSITION', 'PERSONAL_PROFESSION',
		];

/*
		if ($searchByEmail)
		{
			$selectFields[] = new \Bitrix\Main\Entity\ExpressionField('EMAIL_OK', 'CASE WHEN UPPER(%s) = "'.$DB->ForSql(mb_strtoupper(str_replace('%', '%%', $searchStringOriginal))).'" THEN 1 ELSE 0 END', 'EMAIL');
		}
*/

		$res = \Bitrix\Main\UserTable::getList(array(
			'filter' => $userFilter,
			'select' => $selectFields
		));

		while($user = $res->fetch())
		{
			$image = CFile::ResizeImageGet(
				$user["PERSONAL_PHOTO"],
				array(
					"width" => 100,
					"height" => 100
				),
				BX_RESIZE_IMAGE_EXACT,
				false
			);

			$result[] = array(
				'ACTIVE' => $user['ACTIVE'],
				'NAME' => \CUser::formatName(
					$userNameTemplate,
					array(
						'ID' => $user['ID'],
						'NAME' => $user['NAME'],
						'LAST_NAME' => $user['LAST_NAME'],
						'SECOND_NAME' => $user['SECOND_NAME'],
						'LOGIN' => $user['LOGIN'],
					),
					true
				),
				'URL' => str_replace('#user_id#', $user['ID'], $userPageURLTemplate),
				'MODULE_ID' => '',
				'PARAM1' => '',
				'ID' => $user['ID'],
				'ITEM_ID' => 'U'.$user['ID'],
				'ICON' => empty($image['src'])? '': $image['src'],
				'TYPE' => 'users',
				'DESCRIPTION' => $user['WORK_POSITION'] ? $user['WORK_POSITION'] : ($user['PERSONAL_PROFESSION'] ? $user['PERSONAL_PROFESSION'] : '&nbsp;'),
				'IS_EXTRANET' => 'N',
				'IS_EMAIL' => 'N',
				'IS_CRM_EMAIL' => 'N'
			);
		}

		usort($result, array(__CLASS__, "resultCmp"));

		return $result;
	}

	final public static function getSonetGroups($searchString = false)
	{
		global $USER, $CACHE_MANAGER;

		$result = array();

		if (!$USER->isAuthorized())
		{
			return $result;
		}

		if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		static $extranetIncluded = null;
		static $extranetSiteId = null;
		static $extranetUser = null;

		if ($extranetIncluded === null)
		{
			$extranetIncluded = \Bitrix\Main\Loader::includeModule('extranet');
			$extranetSiteId = ($extranetIncluded ? CExtranet::getExtranetSiteID() : false);
			$extranetUser = ($extranetIncluded ? !CExtranet::isIntranetUser() : false);
		}

		$groupPageURLTemplate = \Bitrix\Main\Config\Option::get('socialnetwork', 'workgroups_page', SITE_DIR.'workgroups/', SITE_ID).'group/#group_id#/';

		$groupFilter = array();

		if (!empty($searchString))
		{
			$groupFilter['%NAME'] = $searchString;
		}

		if ($extranetUser)
		{
			$userGroupList = array();
			$res = \Bitrix\Socialnetwork\UserToGroupTable::getList(array(
				'filter' => array(
					'USER_ID' => $USER->getId(),
					'@ROLE' => \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember()
				),
				'select' => array('GROUP_ID')
			));

			while($relation = $res->fetch())
			{
				$userGroupList[] = intval($relation['GROUP_ID']);
			}

			if (empty($userGroupList))
			{
				return $result;
			}
			$groupFilter['@ID'] = $userGroupList;
		}
		elseif (!CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
		{
			$groupFilter['CHECK_PERMISSIONS'] = $USER->GetId();
		}

		$cacheResult = $obCache = false;

		if (empty($searchString))
		{
			$cacheTtl = 3153600;
			$cacheId = 'search_title_sonetgroups_'.md5(serialize($groupFilter).$extranetSiteId.$groupPageURLTemplate);
			$cacheDir = '/intranet/search/sonetgroups_v2/';

			$obCache = new CPHPCache;
			if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
			{
				$cacheResult = $result = $obCache->GetVars();
			}
		}

		if ($cacheResult === false)
		{
			if ($obCache)
			{
				$obCache->StartDataCache();
				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->StartTagCache($cacheDir);
				}
			}

			$res = CSocnetGroup::getList(
				array('NAME' => 'ASC'),
				$groupFilter,
				false,
				false,
				array("ID", "NAME", "IMAGE_ID", "DESCRIPTION", 'TYPE')
			);

			$groupList = $groupIdList = array();
			while ($group = $res->fetch())
			{
				if (!empty($group['NAME']))
				{
					$group['NAME'] = Emoji::decode($group['NAME']);
				}
				if (!empty($group['DESCRIPTION']))
				{
					$group['DESCRIPTION'] = Emoji::decode($group['DESCRIPTION']);
				}

				$groupIdList[] = $group["ID"];
				$groupList[$group["ID"]] = $group;
			}

			$memberGroupIdList = array();

			if ($extranetUser)
			{
				$memberGroupIdList = $groupIdList;
			}
			elseif (!empty($groupIdList))
			{
				$res = \Bitrix\Socialnetwork\UserToGroupTable::getList(array(
					'filter' => array(
						'USER_ID' => $USER->getId(),
						'@GROUP_ID' => $groupIdList,
						'@ROLE' => \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember()
					),
					'select' => array('GROUP_ID')
				));
				while($relation = $res->fetch())
				{
					$memberGroupIdList[] = $relation['GROUP_ID'];
				}
			}

			$chatIds = GroupChat::getChatIds($groupIdList);

			foreach($groupList as $group)
			{
				$image = CFile::ResizeImageGet(
					$group["IMAGE_ID"],
					array(
						"width" => 100,
						"height" => 100
					),
					BX_RESIZE_IMAGE_EXACT,
					false
				);

				$site = '';
				$isExtranet = false;
				$rsGroupSite = CSocNetGroup::GetSite($group["ID"]);
				while ($arGroupSite = $rsGroupSite->fetch())
				{
					if (
						empty($site)
						&& (
							!$extranetSiteId
							|| $arGroupSite["LID"] != $extranetSiteId
						)
					)
					{
						$site = $arGroupSite["LID"];
					}
					else
					{
						$isExtranet = true;
					}
				}

				$chatId = $chatIds[(int)$group['ID']] ?? 0;

				$result[] = array(
					'ID' => $group['ID'],
					'NAME' => htmlspecialcharsbx($group['NAME']),
					'URL' => GroupUrl::get(
						(int)$group['ID'],
						(string)$group['TYPE'],
						$chatId
					),
					'MODULE_ID' => '',
					'PARAM1' => '',
					'ITEM_ID' => 'G'.$group['ID'],
					'ICON' => empty($image['src'])? '': $image['src'],
					'TYPE' => 'sonetgroups',
					'IS_EXTRANET' => $isExtranet,
					'SITE' => $site,
					'IS_MEMBER' => in_array($group['ID'], $memberGroupIdList),
					'GROUP_TYPE' => $group['TYPE'],
					'GROUP_CHAT_ID' => $chatId,
				);
			}

			if ($obCache)
			{
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->RegisterTag("sonet_group");
					$CACHE_MANAGER->RegisterTag("sonet_user2group_U".$USER->getID());
					$CACHE_MANAGER->EndTagCache();
				}

				$obCache->EndDataCache($result);
			}
		}

		return $result;
	}

	final public static function convertAjaxToClientDb($arEntity, $entityType)
	{
		static $timestamp = false;

		if (!$timestamp)
		{
			$timestamp = time();
		}

		$result = [];
		if ($entityType === 'sonetgroups' || $entityType === 'collabs')
		{
			$result = [
				'id' => 'G'.$arEntity["ID"],
				'entityId' => $arEntity["ID"],
				'name' => $arEntity["NAME"],
				'avatar' => empty($arEntity['ICON'])? '': $arEntity['ICON'],
				'desc' => empty($arEntity['DESCRIPTION'])? '': (TruncateText($arEntity['DESCRIPTION'], 100)),
				'isExtranet' => ($arEntity['IS_EXTRANET'] ? "Y" : "N"),
				'site' => $arEntity['SITE'],
				'isMember' => (isset($arEntity['IS_MEMBER']) && $arEntity['IS_MEMBER'] ? "Y" : "N"),
				'groupType' => $arEntity['GROUP_TYPE'],
				'dialogId' => GroupUrl::getDialogId((int)$arEntity['GROUP_CHAT_ID'])
			];
			$result['checksum'] = md5(serialize($result));
			$result['timestamp'] = $timestamp;
		}
		elseif($entityType == 'menuitems')
		{
			$result = array(
				'id' => 'M'.$arEntity["URL"],
				'entityId' => $arEntity["URL"],
				'name' => $arEntity["NAME"]
			);
			if (
				!empty($arEntity["CHAIN"])
				&& is_array($arEntity["CHAIN"])
			)
			{
				$result['chain'] = $arEntity["CHAIN"];
			}
			$result['checksum'] = md5(serialize($result));
			$result['timestamp'] = $timestamp;
		}
		elseif($entityType == 'users')
		{
			$result = array(
				'id' => 'U'.$arEntity["ID"],
				'entityId' => $arEntity["ID"],
				'name' => $arEntity["NAME"],
				'avatar' => empty($arEntity['ICON'])? '': $arEntity['ICON'],
				'desc' => empty($arEntity['DESCRIPTION'])? '': $arEntity['DESCRIPTION'],
				'isExtranet' => 'N',
				'isEmail' => 'N',
				'active' => 'Y'
			);
			$result['checksum'] = md5(serialize($result));
			$result['login'] = '';
		}

		return $result;
	}

	final public static function getMenuItems($searchString = false)
	{
		global $APPLICATION;

		$result = array();

		$isBitrix24 = file_exists($_SERVER["DOCUMENT_ROOT"] . SITE_DIR . ".superleft.menu_ext.php");
		$menuTypes = $isBitrix24 ? ['superleft', 'left', 'sub'] : ['top', 'left', 'sub'];

		$arMenuResult = $APPLICATION->includeComponent(
			'bitrix:menu',
			'left_vertical',
			[
				'MENU_TYPES' => $menuTypes,
				'MENU_CACHE_TYPE' => 'Y',
				'MENU_CACHE_TIME' => '604800',
				'MENU_CACHE_USE_GROUPS' => 'N',
				'MENU_CACHE_USE_USERS' => 'Y',
				'CACHE_SELECTED_ITEMS' => 'N',
				'MENU_CACHE_GET_VARS' => [],
				'MAX_LEVEL' => '3',
				'USE_EXT' => 'Y',
				'DELAY' => 'N',
				'ALLOW_MULTI_SELECT' => 'N',
				'RETURN' => 'Y',
			],
			false,
			['HIDE_ICONS' => 'Y']
		);

		$itemCache = [];
		foreach($arMenuResult as $menuItem)
		{
			if (empty($menuItem['LINK']))
				continue;

			if (
				empty($searchString)
				|| mb_strpos(mb_strtolower($menuItem['TEXT']), mb_strtolower($searchString)) !== false
			)
			{
				$url = isset($menuItem['PARAMS']) && isset($menuItem['PARAMS']["real_link"]) ?
					$menuItem['PARAMS']["real_link"] :
					$menuItem['LINK']
				;

				$hash = md5($menuItem['TEXT'] . '~' . $url);
				if (isset($itemCache[$hash]))
				{
					continue;
				}

				$itemCache[$hash] = true;

				$chain = (
					!empty($menuItem['CHAIN']) && is_array($menuItem['CHAIN'])
						? $menuItem['CHAIN']
						: [ $menuItem['TEXT'] ]
				);

				$chain = array_map(static function($item) {
					return htmlspecialcharsback($item);
				}, $chain);

				$result[] = array(
					'NAME' => htmlspecialcharsbx($menuItem['TEXT']),
					'URL' => $url,
					'CHAIN' => $chain,
					'MODULE_ID' => '',
					'PARAM1' => '',
					'ITEM_ID' => 'M'.$menuItem['LINK'],
					'ICON' => '',
					'ON_CLICK' => $menuItem['PARAMS']['onclick'] ?? '',
				);
			}
		}

		usort($result, array(__CLASS__, "resultCmp"));

		return $result;
	}

	final public static function customSearch($searchString, $arParams, &$arResult)
	{
		static $bSocialnetworkIncluded = null;
		static $bExtranetSite = null;

		if ($bSocialnetworkIncluded === null)
		{
			$bSocialnetworkIncluded = \Bitrix\Main\Loader::includeModule('socialnetwork');
		}

		$extranetIncluded = Bitrix\Main\Loader::includeModule('extranet');

		if ($bExtranetSite === null)
		{
			$bExtranetSite = $extranetIncluded && CExtranet::IsExtranetSite();
		}

		for($i = 0; $i < $arParams["NUM_CATEGORIES"]; $i++)
		{
			$categoryCode = $arParams["CATEGORY_".$i];

			if (is_array($categoryCode))
			{
				$categoryCode = $categoryCode[0];
			}

			if (mb_strpos($categoryCode, 'custom_') === 0)
			{
				$categoryTitle = trim($arParams["CATEGORY_".$i."_TITLE"]);
				if(empty($categoryTitle))
					continue;

				$arResult["CATEGORIES"][$i] = array(
					"TITLE" => htmlspecialcharsbx($categoryTitle),
					"ITEMS" => array()
				);

				if (
					$categoryCode == 'custom_users'
					&& !$bExtranetSite
				)
				{
					$arResult["customUsersCategoryId"] = $i;
					$arResult["CATEGORIES"][$i]["ITEMS"] = CB24SearchTitle::getUsers($searchString);

					if ($arResult["customResultEmpty"] && !empty($arResult["CATEGORIES"][$i]["ITEMS"]))
					{
						$arResult["customResultEmpty"] = false;
					}

					foreach($arResult["CATEGORIES"][$i]["ITEMS"] as $key => $arItem)
					{
						$clientDbItem = CB24SearchTitle::convertAjaxToClientDb($arItem, 'users');
						$arResult["CATEGORIES"][$i]["ITEMS"][$key]['CHECKSUM'] = $clientDbItem['checksum'];
					}
				}
				elseif (
					$categoryCode == 'custom_sonetgroups'
					&& $bSocialnetworkIncluded
				)
				{
					$arResult["customSonetGroupsCategoryId"] = $i;
					$sonetgroups = array_filter(
						CB24SearchTitle::getSonetGroups($searchString),
						fn ($group) => $group['GROUP_TYPE'] !== 'collab'
					);
					$arResult["CATEGORIES"][$i]["ITEMS"] = array_values($sonetgroups);

					if ($arResult["customResultEmpty"] && !empty($arResult["CATEGORIES"][$i]["ITEMS"]))
					{
						$arResult["customResultEmpty"] = false;
					}

					foreach($arResult["CATEGORIES"][$i]["ITEMS"] as $key => $arItem)
					{
						$clientDbItem = CB24SearchTitle::convertAjaxToClientDb($arItem, 'sonetgroups');
						$arResult["CATEGORIES"][$i]["ITEMS"][$key]['CHECKSUM'] = $clientDbItem['checksum'];
					}
				}
				elseif (
					$categoryCode == 'custom_collabs'
					&& $bSocialnetworkIncluded
					&& $extranetIncluded
					&& CollabFeature::isOn()
				)
				{
					$collabs = array_filter(
						CB24SearchTitle::getSonetGroups($searchString),
						fn ($group) => $group['GROUP_TYPE'] === 'collab'
					);
					$arResult["CATEGORIES"][$i]["ITEMS"] = array_values($collabs);

					if ($arResult["customResultEmpty"] && !empty($arResult["CATEGORIES"][$i]["ITEMS"]))
					{
						$arResult["customResultEmpty"] = false;
					}

					foreach($arResult["CATEGORIES"][$i]["ITEMS"] as $key => $arItem)
					{
						$clientDbItem = CB24SearchTitle::convertAjaxToClientDb($arItem, 'collabs');
						$arResult["CATEGORIES"][$i]["ITEMS"][$key]['CHECKSUM'] = $clientDbItem['checksum'];
					}
				}
				elseif ($categoryCode == 'custom_menuitems')
				{
					$arResult["CATEGORIES"][$i]["ITEMS"] = CB24SearchTitle::getMenuItems($searchString);

					if ($arResult["customResultEmpty"] && !empty($arResult["CATEGORIES"][$i]["ITEMS"]))
					{
						$arResult["customResultEmpty"] = false;
					}

					foreach($arResult["CATEGORIES"][$i]["ITEMS"] as $key => $arItem)
					{
						$clientDbItem = CB24SearchTitle::convertAjaxToClientDb($arItem, 'menuitems');
						$arResult["CATEGORIES"][$i]["ITEMS"][$key]['CHECKSUM'] = $clientDbItem['checksum'];
					}
				}
			}
		}
	}

	final public static function resultCmp($a, $b)
	{
		if ($a['NAME'] == $b['NAME'])
		{
			return 0;
		}
		return ($a['NAME'] < $b['NAME']) ? -1 : 1;
	}
}
?>
