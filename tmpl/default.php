<?php
/**
 * @package		mod_qlcurl
 * @copyright	Copyright (C) 2016 ql.de All rights reserved.
 * @author 		Mareike Riegel mareike.riegel@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(1==$params->get('urlStorage',0)) echo '<div class="url">'.$params->get('url').'</div>';

if (1==$php) include_once($strFilenameTemp);
else echo $code;