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
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/** @var Registry $params */
/** @var \stdClass $module */
/** @var string $qlcurlData */

require_once(dirname(__FILE__) . '/ModQlcurlHelper.php');
$QlcurlHelper = new ModQlcurlHelper($module, $params);
$QlcurlHelper->addScripts();

$qlcurlData = $QlcurlHelper->getDataByUrl($params->get('url', ''));
$xml = $QlcurlHelper->xmlTransform($qlcurlData);
$json = $QlcurlHelper->isJson($qlcurlData) ? $QlcurlHelper->asJson($qlcurlData) : new \stdClass;

require JModuleHelper::getLayoutPath('mod_qlcurl', $params->get('layout', 'default'));
