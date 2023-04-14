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

defined('_JEXEC') or die('Restricted access');

class ModQlcurlHelper
{
    private \stdClass $module;
    private Registry $params;
    private array $error;
    const MODULE = 'mod_qlcurl';
    const JOOMLA4 = 4;

    public function __construct($module, $params)
    {
        $this->module = $module;
        $this->params = $params;
    }

    function getDataByUrl(string $url): string
    {
        try {
            if (empty(trim($url))) {
                return '';
            }

            $userAgent = $this->params->get('user_agent');
            $login = (bool)$this->params->get('login');
            $user = $this->params->get('user');
            $passwort =  $this->params->get('password');

            return match ($this->params->get('connection_type')) {
                'curl' => $this->getDataByCUrl($url, $userAgent, $login, $user, $passwort),
                // default, simple
                default => $this->getDataSimple($url),
            };
        } catch (Exception $e) {
            $msg = sprintf('%s problems with url `%s`', self::MODULE, $url);
            $msg .= $e->getMessage();
            $this->addError($msg);
        }
    }

    private function addError(string $msg)
    {
        $this->error[] = $msg;
    }

    private function getErrors(): array
    {
        return $this->error;
    }

    public function xmlTransform(string $xml): SimpleXMLElement|false
    {
        return simplexml_load_string($xml);
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

    public function getDataSimple($url): string
    {
        if (!empty($this->params->get('user_agent'))) {
            ini_set('user_agent', $this->params->get('user_agent'));
        }

        $context = (bool)$this->params->get('login')
            ? stream_context_create(
                ['http' => ['header' => sprintf('Authorization: Basic %:%s', base64_encode($this->params->get('user')), $this->params->get('password'))]])
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
    public function getDataByCUrl(string $url, ?string $userAgent, bool $login, ?string $user, ?string $passwort): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if ($login) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $user, $passwort));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        $output = curl_exec($ch);
        curl_close($ch);
        if (false === $output) {
            throw new Exception(Text::_('MOD_QLCURL_MSG_ERROROCCURRED'));
        }
        return $output;
    }

    /**
     *
     */
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

    /**
     *
     */
    public function isJoomla4($jversion)
    {
        return (int)$jversion >= self::JOOMLA4;
    }
}
