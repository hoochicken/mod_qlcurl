<?php
/**
 * @package        mod_qlcurl
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace QlcurlNamespace\Module\Qlcurl\Site\Helper;

// no direct access
use JModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/** @var Registry $params */
/** @var \stdClass $module */
/** @var string $qlcurlData */

require_once(dirname(__FILE__) . '/ModQlcurlHelper.php');
$QlcurlHelper = new ModQlcurlHelper($module, $params);
$QlcurlHelper->addScripts();

$url = $params->get('url', '');
$username = $params->get('user_name', '');
$userAgent = $params->get('user_agent', '');
$login = (bool)$params->get('login', false);
$password = $params->get('password', '');

// echo $QlcurlHelper->execAbsoluteDummieCode($url, $username, $password);

$qlcurlData = $QlcurlHelper->getDataByUrl($url, $userAgent, $login, $username, $password);
$xml = $params->get('xml_transform', false) ? $QlcurlHelper->xmlTransform($qlcurlData) : '';
$json = $QlcurlHelper->isJson($qlcurlData) ? $QlcurlHelper->asJson($qlcurlData) : new \stdClass;

$errors = $QlcurlHelper->getErrors();
if (0 < count($errors)) {
    foreach ($errors as $error) {
        Factory::getApplication()->enqueueMessage($error);
    }
}

require JModuleHelper::getLayoutPath('mod_qlcurl', $params->get('layout', 'default'));
