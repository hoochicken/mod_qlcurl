<?php
/**
 * @package		mod_qlcurl
 * @copyright	Copyright (C) 2016 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$php=$params->get('php',0);
$strCode=$params->get('code','');

require_once (dirname(__FILE__).'/helper.php');
$obj_helper=new modQlcurlHelper($module,$params);

$qlcurlData=$obj_helper->getExternalData();
if(1==$params->get('xmlTransform',0))$qlcurlData=$obj_helper->xmlTransform($qlcurlData);

if(0<$params->get('printData',0))
{
    $outputPrint=$qlcurlData;
    if(2==$params->get('printData',0) AND 0==$params->get('xmlTransform',0))$outputPrint=htmlspecialchars($outputPrint);
    $obj_helper->p($outputPrint);
}
switch($params->get('clean',0))
{
    case 1:
        $strCode=$obj_helper->cleanJs($strCode);
        break;
    case 2:
        $strCode=$obj_helper->cleanCss($strCode);
        break;
    case 3:
        $strCode=str_replace('<br />', '', $strCode);
        break;
}
if(1==$php)
{
    $codeParams=$obj_helper->addCodeParams($params->get('codeParams',''));
    $strFilenameTemp=tempnam(JPATH_SITE.'/tmp', 'mod_qlcurl_');
    $obj_helper->generateFile($strCode,$strFilenameTemp);
}

require JModuleHelper::getLayoutPath('mod_qlcurl', $params->get('layout', 'default'));
if(1==$php AND file_exists($strFilenameTemp))unlink($strFilenameTemp);