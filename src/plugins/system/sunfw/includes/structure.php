<?php
/**
 * @package    Artista (Sun Framework)
 * @copyright  Copyright (C) 2012 JoomlaShine.com. Copyright (C) 2026 Artriva Studios.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Installs a template's bundled default structure (structures/<id>.json) into a template style.
 *
 * This used to live in the sample data installer. Everything it needs ships inside the
 * template folder, so it works without contacting any server.
 *
 * @package  Artista
 * @since    3.0.0
 */
#[\AllowDynamicProperties]
class SunFwStructure
{
	/**
	 * Install a structure into a template style.
	 *
	 * @param   string  $templateName  Template folder name, e.g. jsn_artista2_pro.
	 * @param   int     $styleId       Template style ID.
	 * @param   string  $structureId   Structure name; matches templates/<template>/structures/<name>.json.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public static function install($templateName, $styleId, $structureId)
	{
		if ($structureId === '')
		{
			throw new Exception('Invalid Request');
		}

		$path = JPATH_ROOT . '/templates/' . $templateName . '/structures/' . basename($structureId) . '.json';

		if (is_file($path))
		{
			$params = file_get_contents($path);

			if (!$params)
			{
				throw new Exception(Text::_('SUNFW_READ_FILE_FAIL'));
			}
		}
		else
		{
			// No structure file: start from an empty layout with only the niche style set.
			$params = json_encode(array(
				'template'            => $templateName,
				'layout_builder_data' => '',
				'appearance_data'     => '',
				'mega_menu_data'      => '',
				'system_data'         => json_encode(array('niche-style' => $structureId)),
			));
		}

		if (substr($params, 0, 1) != '{' || substr($params, -1) != '}')
		{
			throw new Exception(Text::_('SUNFW_INVALID_FILE_CONTENT'));
		}

		$dbo    = Factory::getDbo();
		$style  = SunFwHelper::getSunFwStyle($styleId);
		$params = json_decode($params, true);

		unset($params['id']);

		$params['style_id']       = (int) $styleId;
		$params['template']       = $templateName;
		$params['mega_menu_data'] = '';

		// Strip any Google Tag Manager snippets the bundled structure carries in its system params.
		$systemData = isset($params['system_data']) ? $params['system_data'] : array();
		$systemData = is_array($systemData) ? $systemData : (array) json_decode($systemData, true);

		foreach ($systemData as $k => $v)
		{
			if (!is_string($v) || $v === '')
			{
				continue;
			}

			foreach (array('<!-- Google Tag Manager -->' => '<!-- End Google Tag Manager -->',
				'<!-- Google Tag Manager (noscript) -->' => '<!-- End Google Tag Manager (noscript) -->') as $open => $close)
			{
				if (strpos($v, $open) !== false)
				{
					list($before, $rest) = explode($open, $v, 2);
					$rest = explode($close, $rest, 2);
					$v    = $before . (isset($rest[1]) ? $rest[1] : '');
				}
			}

			$systemData[$k] = $v;
		}

		$params['system_data'] = json_encode($systemData);

		// Drop any key that is not a column of the styles table.
		$columns = $dbo->getTableColumns('#__sunfw_styles');

		foreach (array_keys($params) as $column)
		{
			if (!array_key_exists($column, $columns))
			{
				unset($params[$column]);
			}
		}

		if (!empty($style))
		{
			$object = (object) $params;
			$dbo->updateObject('#__sunfw_styles', $object, 'style_id');
		}
		else
		{
			$query = $dbo->getQuery(true)
				->insert('#__sunfw_styles')
				->columns($dbo->quoteName(array_keys($params)))
				->values(implode(',', $dbo->quote(array_values($params))));

			$dbo->setQuery($query)->execute();
		}

		// Compile the style's CSS.
		include_once SUNFW_PATH . '/libraries/3rd-party/scssphp/scss.inc.php';

		$render = new SunFwScssrender();
		$render->compile($styleId, $templateName);
		$render->compile($styleId, $templateName, 'layout');
	}
}
