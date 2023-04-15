<?php
/**
 * @package        mod_qlcurl
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace QlcurlNamespace\Module\Qlcurl\Site\Helper;

// no direct access
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use SimpleXMLElement;
use XMLReader;

defined('_JEXEC') or die('Restricted access');

class ModQlcurlHelper
{
    private \stdClass $module;
    private Registry $params;
    private array $errors = [];
    const MODULE = 'mod_qlcurl';
    const JOOMLA4 = 4;

    public function __construct($module, $params)
    {
        $this->module = $module;
        $this->params = $params;
    }

    function getDataByUrl(string $url, ?string $userAgent, bool $login, ?string $username, ?string $password): string
    {
        try {
            if (empty(trim($url))) {
                return '';
            }

            return match ($this->params->get('connection_type')) {
                'curl' => $this->getDataByCUrl($url, $userAgent, $login, $username, $password),
                // default, simple
                default => $this->getDataSimple($url, $userAgent, $login, $username, $password),
            };
        } catch (Exception $e) {
            $msg = sprintf('%s problems with url `%s`', self::MODULE, $url);
            $msg .= $e->getMessage();
            $this->addError($msg);
        }
    }

    private function addError(string $msg)
    {
        $this->errors[] = $msg;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function xmlTransform(string $xml): SimpleXMLElement|null
    {
        if (!$this->checkXmlIsValid($xml)) {
            return null;
        }
        return simplexml_load_string($xml);
    }

    public function checkXmlIsValid(string $xml): bool
    {
        libxml_use_internal_errors(true);
        $valid = @simplexml_load_string($xml);
        if (is_object($valid)) {
            return true;
        }

        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            // $this->addError($error->message);
        }
        return false;
    }

    public function asJson(string $xml): ?\stdClass
    {
        return json_decode($xml);
    }

    public function isJson(?string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getDataSimple(string $url, ?string $userAgent, bool $login, ?string $user, ?string $password): string
    {
        if (!empty($userAgent)) {
            ini_set('user_agent', $userAgent);
        }

        $context = $login
            ? stream_context_create(
                ['http' => ['header' => sprintf('Authorization: Basic %s', base64_encode($user . ':' . $password))]])
            : null;

        $output = @file_get_contents($url, false, $context);
        if (empty($output)) {
            throw new Exception(Text::_('MOD_QLCURL_MSG_ERROROCCURRED'));
        }
        return $output;
    }

    /*
    * getExternalData via Curl
    */
    public function getDataByCUrl(string $url, ?string $userAgent, bool $login, ?string $user, ?string $password): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if ($login) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $user, $password));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $output = curl_exec($ch);
        curl_close($ch);
        if (false === $output) {
            throw new Exception(Text::_('MOD_QLCURL_MSG_ERROROCCURRED'));
        }
        return $output;
    }

    public function addScripts()
    {
        if ($this->isJoomla4(JVERSION)) {
            $wam = Factory::getDocument()->getWebAssetManager();
            $wam->registerAndUseScript('mod_qlcurl', 'mod_qlcurl/qlcurl.js');
            $wam->registerAndUseStyle('mod_qlcurl', 'mod_qlcurl/qlcurl.css');
        } else {
            $document = Factory::getDocument();
            $document->addScript(\JUri::root() . 'media/mod_qlcurl/js/qlcurl.js');
            $document->addStyleSheet(\JUri::root() . 'media/mod_qlcurl/css/qlcurl.css');
        }
    }

    public function isJoomla4($jversion)
    {
        return (int)$jversion >= self::JOOMLA4;
    }

    public function execAbsoluteDummieCode(string $url, string $username, string $password): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $result = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
        curl_close($ch);
        return sprintf('Status %s<br />Data %s', $status_code, $result);
    }
}
