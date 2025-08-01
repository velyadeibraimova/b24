<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Main\UI;
use Bitrix\Main\Loader;
use Bitrix\UI\Buttons\AirButtonStyle;
use Bitrix\UI\Buttons\Button;

CUtil::InitJSCore([ 'popup', 'ajax', 'marketplace', 'clipboard' ]);

UI\Extension::load([
	'socialnetwork.common',
	'ui.icons.b24',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.notification',
	'ui.info-helper',
	'ui.hint',
]);

if (Loader::includeModule('bitrix24'))
{
	CBitrix24::initLicenseInfoPopupJS();
}

$isProjectAccessEnabled = \Bitrix\Socialnetwork\Helper\Workgroup::isProjectAccessFeatureEnabled();

$groupMember = in_array($arResult['CurrentUserPerms']['UserRole'], UserToGroupTable::getRolesMember());

$this->addExternalCss(SITE_TEMPLATE_PATH . "/src/css/standalone/profile-menu.css");
$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."profile-menu-mode");

$group = Workgroup::getById($arResult['Group']['ID']);
$isScrumProject = ($group && $group->isScrumProject());

if (!$arResult['inIframe'] || $arResult['IS_CURRENT_PAGE_FIRST'])
{
	$this->SetViewTarget($arResult['inIframe'] ? 'above_pagetitle' : "page_menu", 100);
}

if (
	!empty($arResult["bShowRequestSentMessage"])
	&& $arResult["bShowRequestSentMessage"] === UserToGroupTable::INITIATED_BY_USER
	&& !CSocNetUser::isCurrentUserModuleAdmin()
)
{
	?><script>
		BX.ready(function() {
			(new BX.Socialnetwork.UI.RecallJoinRequest({
				RELATION_ID: <?= (int)$arResult["UserRelationId"] ?>,
				GROUP_ID: <?= (int)$arResult['Group']['ID'] ?>,
				URL_REJECT_OUTGOING_REQUEST: '<?= CUtil::JSEscape($arResult["Urls"]["UserRequests"]) ?>',
				URL_GROUPS_LIST: '<?= CUtil::JSEscape($arResult["Urls"]["GroupsList"]) ?>',
				PROJECT: <?= ($arResult["Group"]["PROJECT"] === "Y" ? 'true' : 'false') ?>,
				SCRUM: <?= ($arResult['isScrumProject'] ? 'true' : 'false') ?>,
			})).showPopup();
		});
	</script><?php
}

?><script>
	BX.ready(function() {
		BX.message({
			SGMPathToRequestUser: '<?=CUtil::JSUrlEscape(
				!empty($arResult["Urls"]["Invite"])
					? $arResult["Urls"]["Invite"]
					: $arResult["Urls"]["Edit"].(mb_strpos($arResult["Urls"]["Edit"], "?") !== false ? "&" : '?')."tab=invite"
			)?>',
			SGMPathToUserRequestGroup: '<?=CUtil::JSUrlEscape($arResult["Urls"]["UserRequestGroup"])?>',
			SGMPathToUserLeaveGroup: '<?=CUtil::JSUrlEscape($arResult["Urls"]["UserLeaveGroup"])?>',
			SGMPathToRequests: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequests"])?>',
			SGMPathToRequestsOut: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequestsOut"])?>',
			SGMPathToMembers: '<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupUsers"])?>',
			SGMPathToEdit: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Edit"].(mb_strpos($arResult["Urls"]["Edit"], "?") !== false ? "&" : '?')."tab=edit")?>',
			SGMPathToDelete: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Delete"])?>',
			SGMPathToFeatures: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Features"])?>',
			SGMPathToCopy: '<?=CUtil::JSUrlEscape($arResult["Urls"]["Copy"])?>',
			SONET_SGM_T_CONTROL_NOTIFICATION_COPYURL: '<?= htmlspecialcharsbx(Loc::getMessage('SONET_SGM_T_CONTROL_NOTIFICATION_COPYURL')) ?>',
			SONET_SGM_T_CONTROL_HINT_FAVORITES_ADD: '<?= htmlspecialcharsbx(Loc::getMessage('SONET_SGM_T_CONTROL_HINT_FAVORITES_ADD')) ?>',
			SONET_SGM_T_CONTROL_HINT_FAVORITES_REMOVE: '<?= htmlspecialcharsbx(Loc::getMessage('SONET_SGM_T_CONTROL_HINT_FAVORITES_REMOVE')) ?>',
			SONET_SGM_T_MORE_MENU_SUBSCRIBE: '<?= htmlspecialcharsbx(Loc::getMessage('SONET_SGM_T_MORE_MENU_SUBSCRIBE')) ?>',
			SONET_SGM_T_MORE_MENU_UNSUBSCRIBE: '<?= htmlspecialcharsbx(Loc::getMessage('SONET_SGM_T_MORE_MENU_UNSUBSCRIBE')) ?>',
			SONET_SGM_T_MORE_MENU_BINDING: '<?= htmlspecialcharsbx(Loc::getMessage('SONET_SGM_T_MORE_MENU_BINDING')) ?>',
		});

		new BX.Intranet.GroupMenu({
			currentUserId: BX.message('USER_ID'),
			groupId: <?=(int)$arResult["Group"]["ID"]?>,
			groupType: '<?= CUtil::JSEscape($arResult['Group']['TypeCode']) ?>',
			projectTypeCode: '<?= CUtil::JSEscape($arResult['Group']['ProjectTypeCode']) ?>',
			isProject: <?=($arResult["Group"]["PROJECT"] === "Y" ? 'true' : 'false')?>,
			isScrumProject: <?= ($arResult['isScrumProject'] ? 'true' : 'false') ?>,
			isRoleControlDisabled: <?= Option::get('tasks', 'tasks_disable_role_control', 'N', '-') === 'Y' ? 'true' : 'false' ?>,
			isOpened: <?=($arResult["Group"]["OPENED"] === "Y" ? 'true' : 'false')?>,
			favoritesValue: <?=($arResult["FAVORITES"] ? 'true' : 'false')?>,
			subscribedValue: <?= ($arResult['isSubscribed'] ? 'true' : 'false') ?>,

			canInitiate: <?=($arResult["CurrentUserPerms"]["UserCanInitiate"] && !$arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
			canProcessRequestsIn: <?=(($arResult["CurrentUserPerms"]["UserCanProcessRequestsIn"] ?? null) && !$arResult["HideArchiveLinks"] ? 'true' : 'false')?>,
			canModify: <?=($arResult["CurrentUserPerms"]["UserCanModifyGroup"] ? 'true' : 'false')?>,

			userRole: '<?=$arResult["CurrentUserPerms"]["UserRole"]?>',
			userIsMember: <?=($arResult["CurrentUserPerms"]["UserIsMember"] ? 'true' : 'false')?>,
			userIsAutoMember: <?=(isset($arResult["CurrentUserPerms"]["UserIsAutoMember"]) && $arResult["CurrentUserPerms"]["UserIsAutoMember"] ? 'true' : 'false')?>,
			userIsScrumMaster: <?= (isset($arResult['CurrentUserPerms']['UserIsScrumMaster']) && $arResult['CurrentUserPerms']['UserIsScrumMaster'] ? 'true' : 'false') ?>,

			editFeaturesAllowed: <?= CUtil::phpToJSObject($isProjectAccessEnabled) ?>,
			copyFeatureAllowed: <?=(\Bitrix\Socialnetwork\Helper\Workgroup::isGroupCopyFeatureEnabled() ? 'true' : 'false')?>,
			canPickTheme: <?= (
				$arResult['inIframe']
				&& \Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker::isAvailable()
				&& $arResult['CurrentUserPerms']['UserCanModifyGroup']
				&& !$arResult['HideArchiveLinks']
					? 'true'
					: 'false'
			) ?>,
			urls: <?= CUtil::PhpToJSObject($arResult['Urls']) ?>,
			pageId: '<?= $arParams['PAGE_ID'] ?>',
			avatarPath: '<?= CUtil::JSEscape(isset($arResult['Group']['IMAGE_FILE']['src']) ? (string)$arResult['Group']['IMAGE_FILE']['src'] : '') ?>',
			avatarType: '<?= CUtil::JSEscape(isset($arResult['Group']['AVATAR_TYPE']) ? \Bitrix\Socialnetwork\Helper\Workgroup::getAvatarTypeWebCssClass($arResult['Group']['AVATAR_TYPE']) : '') ?>',
			bindingMenuItems: <?= CUtil::PhpToJSObject($arResult['bindingMenuItems']) ?>,
			inIframe: <?= ($arResult['inIframe'] ? 'true' : 'false') ?>,
		});
	});
</script><?php

?><div class="profile-menu profile-menu-group<?= ($arResult['inIframe'] ? ' profile-menu-iframe' : '') ?>">
	<div class="profile-menu-inner">
		<div class="profile-menu-top<?= ($arResult['Group']['IS_EXTRANET'] === 'Y' ? ' profile-menu-top-extranet' : '') ?>"><?php

			$avatarStyle = (
				!empty($arResult['Group']['IMAGE_FILE']['src'])
					? 'style="background:url(\'' . Uri::urnEncode($arResult['Group']['IMAGE_FILE']['src']) . '\') no-repeat center center; background-size: cover;"'
					: ''
			);

			$classList = [];

			if (
				empty($arResult['Group']['IMAGE_FILE']['src'])
				&& !empty($arResult['Group']['AVATAR_TYPE'])
			)
			{
				$classList[] = 'profile-menu-avatar';
				$classList[] = 'sonet-common-workgroup-avatar';
				$classList[] = '--' . \Bitrix\Socialnetwork\Helper\Workgroup::getAvatarTypeWebCssClass($arResult['Group']['AVATAR_TYPE']);
			}
			else
			{
				$classList[] = 'ui-icon';
				$classList[] = 'ui-icon-common-user-group';
				$classList[] = 'profile-menu-avatar';
			}

			if (!$arResult['inIframe'])
			{
				?><a href="<?= $arResult['Urls']['View'] ?>" class="<?= implode(' ', $classList) ?>"><i <?= $avatarStyle ?>></i></a><?php
			}
			else
			{
				?><span class="<?= implode(' ', $classList) ?>"><i <?= $avatarStyle ?>></i></span><?php
			}
			?><div class="profile-menu-group-info ui-icon-set__scope">
				<div class="profile-menu-name-box"><?php
					if (!$arResult['inIframe'])
					{
						?><a href="<?= $arResult['Urls']['View'] ?>" class="profile-menu-name"><?= $arResult['Group']['NAME'] ?></a><?php
					}
					else
					{
						?><span class="profile-menu-name"><?= $arResult['Group']['NAME'] ?></span><?php
					}
					?>
				</div>
				<?php

				if ($arResult["Group"]["CLOSED"] === "Y")
				{
					?><span class="profile-menu-description"><?= Loc::getMessage('SONET_UM_ARCHIVE_GROUP') ?></span><?php
				}

				switch (mb_strtolower($arResult['Group']['ProjectTypeCode']))
				{
					case 'scrum':
						$aboutTitle = Loc::getMessage('SONET_SGM_T_LINKS_ABOUT_SCRUM');
						break;
					case 'project':
						$aboutTitle = Loc::getMessage('SONET_SGM_T_LINKS_ABOUT_PROJECT');
						break;
					default:
						$aboutTitle = Loc::getMessage('SONET_SGM_T_LINKS_ABOUT');
				}

				?><span class="profile-menu-links"><?php

					if ($isScrumProject && $arResult['CanView']['tasks'])
					{
						$chatButton = new Button([
							'text' => Loc::getMessage('SONET_TASKS_SCRUM_MEETINGS_LINK'),
							'air' => true,
							'icon' => Bitrix\UI\Buttons\Icon::CAMERA,
							'noCaps' => true,
							'size' => Bitrix\UI\Buttons\Size::SMALL,
						]);

						$chatButton->addAttribute('id', 'tasks-scrum-meetings-button');
						echo $chatButton->render();

						$methodologyButton = new Button([
							'text' => Loc::getMessage('SONET_TASKS_SCRUM_METHODOLOGY_LINK'),
							'air' => true,
							'noCaps' => true,
							'size' => Bitrix\UI\Buttons\Size::SMALL,
						]);

						$methodologyButton->setStyle(AirButtonStyle::SELECTION);
						$methodologyButton->addAttribute('id', 'tasks-scrum-methodology-button');
						echo $methodologyButton->render();
					}

					if ($arResult['bUserCanRequestGroup'])
					{
						if (
							$arResult['Group']['OPENED'] === 'Y'
							|| (
								$arResult['CurrentUserPerms']['UserRole'] === UserToGroupTable::ROLE_REQUEST
								&& $arResult['CurrentUserPerms']['InitiatedByType'] === UserToGroupTable::INITIATED_BY_GROUP
							)
						)
						{
							$joinButton = new Button([
								'text' => Loc::getMessage('SONET_SGM_T_BUTTON_JOIN'),
								'air' => true,
								'noCaps' => true,
								'size' => Bitrix\UI\Buttons\Size::SMALL,
							]);

							$joinButton->addAttribute('id', 'bx-group-menu-join');
							$joinButton->addAttribute('bx-request-url', $arResult["Urls"]["UserRequestGroup"]);
							echo $joinButton->render();
						}
						else
						{
							$joinButton = new Button([
								'text' => Loc::getMessage('SONET_SGM_T_BUTTON_JOIN'),
								'link' => $arResult["Urls"]["UserRequestGroup"],
								'air' => true,
								'noCaps' => true,
								'size' => Bitrix\UI\Buttons\Size::SMALL,
							]);

							echo $joinButton->render();
						}
					}

					if ($arResult['CanView']['chat'])
					{
						?><span id="group-menu-control-button-cont" class="profile-menu-button-container <?= $arResult['isScrumProject'] ? '--scrum' : ''?>"></span><?php
					}

					$projectButton = new Button([
						'text' => $aboutTitle,
						'air' => true,
						'noCaps' => true,
						'link' => $arResult['Urls']['Card'],
						'dataset' => [
							'slider-ignore-autobinding' => 'true',
							'workgroup' => Json::encode($arResult['projectWidgetData']),
						],
						'size' => Bitrix\UI\Buttons\Size::SMALL,
					]);
					$projectButton->setStyle(AirButtonStyle::SELECTION);
					$projectButton->addAttribute('id', 'project-widget-button');
					echo $projectButton->render();

					if (
						!empty($arResult['bindingMenuItems'])
						|| in_array($arResult['CurrentUserPerms']['UserRole'], UserToGroupTable::getRolesMember(), true)
					)
					{
						$moreButton = new Button([
							'air' => true,
							'noCaps' => true,
							'icon' => Bitrix\UI\Buttons\Icon::DOTS,
							'size' => Bitrix\UI\Buttons\Size::SMALL,
						]);

						$moreButton->setStyle(AirButtonStyle::SELECTION);
						$moreButton->setCollapsed();
						$moreButton->addAttribute('id', 'group-menu-more-button');
						echo $moreButton->render();
					}
				?></span><?php

			?></div>
		</div>
		<div class="profile-menu-bottom">
			<div class="profile-menu-items-new"><?php
				$menuItems = [];
				foreach ($arResult["CanView"] as $key => $val)
				{
					if (!$val || $key === "content_search")
					{
						continue;
					}

					if ($key === 'general')
					{
						$menuItems[] = [
							'TEXT' => Loc::getMessage('SONET_UM_NEWS2'),
							'URL' => !empty($arResult['Urls']['General']) ? $arResult['Urls']['General'] : ($arResult['Urls']['View'] ?? ''),
							'ID' => 'general',
							'IS_ACTIVE' => in_array($arParams['PAGE_ID'], ['group', 'group_general'], true),
						];
					}
					else
					{
						$isDisabled = false;
						if (!in_array($key, ['general', 'tasks', 'calendar', 'files'], true))
						{
							$isDisabled = true;
						}

						$item = [
							"TEXT" => $arResult["Title"][$key],
							"ID" => $key,
							"IS_ACTIVE" => ($arParams['PAGE_ID'] === "group_{$key}"),
							"IS_DISABLED" => $isDisabled,
						];

						if ($key === 'calendar')
						{
							/** @see \Bitrix\Calendar\Internals\Counter\CounterDictionary::COUNTER_GROUP_INVITES */
							$item['COUNTER'] = $arResult['Calendar']['Counters']['calendar_group_invites'] ?? 0;
							/** @see \Bitrix\Calendar\Internals\Counter\CounterDictionary::COUNTER_GROUP_INVITES_TPL */
							$item['COUNTER_ID'] = sprintf('calendar_group_invites_%d', $arResult['Group']['ID']);
						}

						if (
							!empty($arResult["OnClicks"])
							&& !empty($arResult["OnClicks"][$key])
						)
						{
							$item["ON_CLICK"] = $arResult["OnClicks"][$key];
						}
						else
						{
							$item["URL"] = $arResult["Urls"][$key];
						}

						if ($key !== 'tasks')
						{
							$menuItems[] = $item;
							continue;
						}

						// tasks by role
						$isActive = ($arParams["PAGE_ID"] === "group_{$key}");
						$defaultRoleId = $arResult['Tasks']['DefaultRoleId'];

						$item['URL'] = (new Uri($arResult["Urls"][$key]))->addParams([
							'F_CANCEL' => 'Y',
							'F_STATE' => 'sR',
						])->getUri();
						$item['IS_ACTIVE'] = ($isActive && ($defaultRoleId === 'view_all' || !$defaultRoleId));
						$item['CLASS'] = 'tasks_role_link';
						$item['ID'] = 'view_all';
						$item['COUNTER'] = $arResult['Tasks']['Counters']['view_all'];
						$menuItems[] = $item;

						if ($isScrumProject)
						{
							continue;
						}

						$defaultRoleId = $arResult['Tasks']['DefaultRoleId'];

						// tasks 23.500.0
						if (Option::get('tasks', 'tasks_disable_role_control', 'N', '-') === 'Y')
						{
							continue;
						}

						$menuItems[] = [
							'TEXT' => Loc::getMessage('SONET_TASKS_PRESET_I_DO'),
							'URL' => (new Uri($arResult["Urls"][$key]))->addParams([
								'F_CANCEL' => 'Y',
								'F_STATE' => 'sR400',
								'clear_filter' => 'Y',
							])->getUri(),
							'ID' => 'view_role_responsible',
							'CLASS' => 'tasks_role_link',
							'IS_ACTIVE' => ($isActive && $defaultRoleId === 'view_role_responsible'),
							'PARENT_ITEM_ID' => 'view_all',
							'COUNTER' => $arResult['Tasks']['Counters']['view_role_responsible'],
						];
						$menuItems[] = [
							'TEXT' => Loc::getMessage('SONET_TASKS_PRESET_I_ACCOMPLICES'),
							'URL' => (new Uri($arResult["Urls"][$key]))->addParams([
								'F_CANCEL' => 'Y',
								'F_STATE' => 'sR800',
								'clear_filter' => 'Y',
							])->getUri(),
							'ID' => 'view_role_accomplice',
							'CLASS' => 'tasks_role_link',
							'IS_ACTIVE' => ($isActive && $defaultRoleId === 'view_role_accomplice'),
							'PARENT_ITEM_ID' => 'view_all',
							'COUNTER' => $arResult['Tasks']['Counters']['view_role_accomplice'],
						];
						$menuItems[] = [
							'TEXT' => Loc::getMessage('SONET_TASKS_PRESET_I_ORIGINATOR'),
							'URL' => (new Uri($arResult["Urls"][$key]))->addParams([
								'F_CANCEL' => 'Y',
								'F_STATE' => 'sRg00',
								'clear_filter' => 'Y',
							])->getUri(),
							'ID' => 'view_role_originator',
							'CLASS' => 'tasks_role_link',
							'IS_ACTIVE' => ($isActive && $defaultRoleId === 'view_role_originator'),
							'PARENT_ITEM_ID' => 'view_all',
							'COUNTER' => $arResult['Tasks']['Counters']['view_role_originator'],
						];
						$menuItems[] = [
							'TEXT' => Loc::getMessage('SONET_TASKS_PRESET_I_AUDITOR'),
							'URL' => (new Uri($arResult["Urls"][$key]))->addParams([
								'F_CANCEL' => 'Y',
								'F_STATE' => 'sRc00',
								'clear_filter' => 'Y',
							])->getUri(),
							'ID' => 'view_role_auditor',
							'CLASS' => 'tasks_role_link',
							'IS_ACTIVE' => ($isActive && $defaultRoleId === 'view_role_auditor'),
							'PARENT_ITEM_ID' => 'view_all',
							'COUNTER' => $arResult['Tasks']['Counters']['view_role_auditor'],
						];
					}
				}

				if (!empty($menuItems))
				{
					if (count(array_filter($menuItems, function($item) { return !(bool)($item['IS_DISABLED'] ?? null); })) <= 0)
					{
						$menuItems[0]['IS_DISABLED'] = false;
					}

					$APPLICATION->IncludeComponent(
						"bitrix:main.interface.buttons",
						"",
						array(
							"ID" => $arResult["menuId"],
							"ITEMS" => $menuItems,
							"THEME" => "air",
							"THEME_VARS" => [
								'--mib-margin-bottom' => 0,
							],
						)
					);
				}

			?></div>
		</div>
	</div>
</div><?php

if (!$arResult['inIframe'] || $arResult['IS_CURRENT_PAGE_FIRST'])
{
	$this->EndViewTarget();
}
