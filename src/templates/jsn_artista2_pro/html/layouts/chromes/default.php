<?php
/**
 * Module chrome "default" (converted from the template's Joomla 3 modChrome_default function).
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
$headerClass   = htmlspecialchars($params->get('header_class', ''));
$icon 			= '';
if (preg_match('/^(.+)?(fa fa-[^\s]+)(.+)?$/', $headerClass, $match)) {
	$headerClass = $match[1];
	$icon .= $match[2];
}

if ($module->content)
{
	echo '<' . $moduleTag . ' class="module-style ' . htmlspecialchars((string) $params->get('moduleclass_sfx', '')) . $moduleClass . '">';

		if ($module->showtitle)
		{
			echo '<div class="module-title"><' . $headerTag . ' class="box-title ' . $headerClass . '">';
			if ($icon != '')
			{
				echo '<i class="'. $icon .'"></i>';
			}
			echo '<span>'. $module->title . '</span></' . $headerTag . '></div>';
		}

		echo '<div class="module-body">';
			echo $module->content;
		echo "</div>";

	echo '</' . $moduleTag . '>';
}
