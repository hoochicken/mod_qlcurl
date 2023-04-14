<?php
/**
 * @package        mod_qlcurl
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */


// no direct access
defined('_JEXEC') or die('Restricted access');

class modQlcurlExternalData2
{
    private stdClass $module;
    private JRegistry $params;

    public function __construct($module, $params)
    {
        $this->module = $module;
        $this->params = $params;
    }

    /*
    * getExternalData defaulty
    */
    public function getData(): ?string
    {
        if (!isset($output)) {
            $output = false;
        }
        return $output;
    }
}