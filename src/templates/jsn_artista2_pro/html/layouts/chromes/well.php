<?php
/**
 * Module chrome "well" (converted from the template's Joomla 3 modChrome_well function).
 *
 * @package    Artista
 * @copyright  Copyright (C) 2012 JoomlaShine.com. Copyright (C) 2026 Artriva Studios.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

$moduleTag     = $params->get('module_tag', 'div');
$bootstrapSize = (int) $params->get('bootstrap_size', 0);
$moduleClass   = $bootstrapSize != 0 ? ' span' . $bootstrapSize : '';
$headerTag     = htmlspecialchars($params->get('header_tag', 'h3'));
$headerClass   = htmlspecialchars($params->get('header_class', 'box-title'));

if ($module->content)
{
	echo '<' . $moduleTag . ' class="well ' . htmlspecialchars((string) $params->get('moduleclass_sfx', '')) . $moduleClass . '">';

		if ($module->showtitle)
		{
			echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
		}

		echo $module->content;
	echo '</' . $moduleTag . '>';
}
