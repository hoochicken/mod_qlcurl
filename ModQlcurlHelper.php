<?php
/**
 * @package        mod_qlcurl
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */


// no direct access
defined('_JEXEC') or die('Restricted access');

class ModQlcurlHelper
{
    private stdClass $module;
    private JRegistry $params;

    public function __construct($module, $params)
    {
        $this->module = $module;
        $this->params = $params;
    }

    public function generateFile($str, $strFilenameTemp)
    {
        $handle = fopen($strFilenameTemp, "w");
        fwrite($handle, $str, strlen($str));
        fclose($handle);
    }

    public function cleanJs($str)
    {
        preg_match('/<script(.*)>(.*)<\/script>/i', $str, $matches);
        if (is_array($matches) and 0 < count($matches)) foreach ($matches as $k => $v) {
            $strClean = str_replace('<br />', '', $v);
            $str = str_replace($v, $strClean, '');
        }
        return $str;
    }

    public function cleanCss($str)
    {
        preg_match('/<style(.*)>(.*)<\/style>/i', $str, $matches);
        if (is_array($matches) and 0 < count($matches)) foreach ($matches as $k => $v) {
            $strClean = str_replace('<br />', '', $v);
            $str = str_replace($v, $strClean, '');
        }
        return $str;
    }

    function addCodeParams($codeParams)
    {
        if ('' == trim($codeParams)) return;
        if (1 == $this->params->get('codeReplaceQuotes', 0)) $codeParams = str_replace('\'', '"', $codeParams);
        if (is_string($codeParams)) $codeParams = json_decode($codeParams);
        return $codeParams;
    }

    /*
 * getExternalData
 */
    function getExternalData()
    {
        $url = $this->params->get('url', '');
        if ('' == trim($url)) return false;
        switch ($this->params->get('connectionType')) {
            case '0':
                return json_encode(array());
            case 'curl' :
                $output = $this->getExternalDataViaCurl($url);
                break;
            case '3rd_way' :
                $output = $this->getExternalDataVia3();
                break;
            case '4th_way' :
                $output = $this->getExternalDataVia4();
                break;
            case 'default' :
            default :
                $output = $this->getExternalDataViaDefault($url);
        }
        return $output;
    }

    /*
    * getExternalData defaulty
    */
    function getExternalDataViaDefault($url)
    {
        if ('' != $this->params->get('user_agent')) ini_set('user_agent', $this->params->get('user_agent'));
        if (1 == $this->params->get('login')) {
            $context = stream_context_create(array('http' => array('header' => "Authorization: Basic " . base64_encode($this->params->get('user') . ':' . $this->params->get('password')))));
            $output = @file_get_contents($url, false, $context);
        } else $output = @file_get_contents($url, false);
        if (false == $output) JFactory::getApplication()->enqueueMessage(JText::_('MOD_QLCURL_MSG_ERROROCCURRED'));
        return $output;
    }

    /*
    * getExternalData via Curl
    */
    function getExternalDataViaCurl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (1 == $this->params->get('login')) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $this->params->get('user') . ':' . $this->params->get('password'));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $this->params->get('user_agent'));
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $output = curl_exec($ch);
        curl_close($ch);
        if (false == $output) JFactory::getApplication()->enqueueMessage(JText::_('MOD_QLCURL_MSG_ERROROCCURRED'));
        return $output;
    }

    /*
    * getExternalData via Curl
    */
    function getExternalDataVia3()
    {
        $output = false;
        $file = 'modQlcurlExternalData.php';
        $file = '/modules/mod_qlcurl/php/classes/' . $file;
        if (!file_exists(JPATH_ROOT . $file)) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('MOD_QLCURL_MSG_RENAMEFILE', $file));
            return false;
        }
        require_once(JPATH_ROOT . $file);
        $obj_external = new modQlcurlExternalData($this->module, $this->params);
        $output = $obj_external->getData();
        if (false == $output) JFactory::getApplication()->enqueueMessage(JText::_('MOD_QLCURL_MSG_ERROROCCURRED'));
        return $output;
    }

    /*
    * getExternalData via Curl
    */
    function getExternalDataVia4()
    {
        $file = 'modQlcurlExternalData2.php';
        $file = '/modules/mod_qlcurl/php/classes/' . $file;
        if (!file_exists(JPATH_ROOT . $file)) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('MOD_QLCURL_MSG_RENAMEFILE', $file));
            return false;
        }
        require_once(JPATH_ROOT . $file);
        $obj_external = new modQlcurlExternalData2($this->module, $this->params);
        $output = $obj_external->getData();
        if (false == $output) JFactory::getApplication()->enqueueMessage(JText::_('MOD_QLCURL_MSG_ERROROCCURRED'));
        return $output;
    }

    function xmlTransform($data)
    {
        return simplexml_load_string($data);
    }

    function p($data, $die = 0)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if (1 == $die) die;
    }
}