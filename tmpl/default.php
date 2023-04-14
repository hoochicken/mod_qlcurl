<?php
/**
 * @package        mod_qlcurl
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/** @var Registry $params */
/** @var stdClass $module */
/** @var string $qlcurlData */
/** @var ?SimpleXMLElement $xml */
/** @var stdClass $json */
?>

<div class="qlcurl">
    <?php if ($params->get('url_display', 0)) : ?>
    <div class="url">
        <?php $params->get('url', ''); ?>
    </div>
<?php endif; ?>

<?php if ($params->get('display_textarea', 0)) : ?>
    <textarea class="xml">
<?php endif; ?>
<?php echo $qlcurlData; ?>
<?php if ($params->get('display_textarea', 0)) : ?>
    </textarea>
<?php endif; ?>
