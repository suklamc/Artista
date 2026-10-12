<?php
/**
 * @version    $Id$
 * @package    SUN Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');
$app = JFactory::getApplication();
$sitename = $app->get('sitename');
$class = isset($component['settings']['prefix-class']) ? $component['settings']['prefix-class'] : '';
$class = trim($class);
$image = isset($component['settings']['image']) ? $component['settings']['image'] : '';
$imageAlt = isset($component['settings']['alt']) ? $component['settings']['alt'] : $sitename;
$visible_in = isset($component['settings']['visible_in']) ? $component['settings']['visible_in'] : array();

if ($image != '')
{
	$pathRoot = JURI::root();
	$urlPattern = '/^(http|https)/';
	preg_match($urlPattern, $image, $m);
	if (count((array) $m))
	{
		$pathRoot = '';
	}
	$image = $pathRoot . $image;
}
?>
<div class="<?php echo "{$class} " . SunFwSiteBootstrap::showOn($visible_in); ?>">
	<img class="<?php echo SunFwSiteBootstrap::getClass('img-responsive'); ?>" alt="<?php echo $imageAlt; ?>"
		src="<?php echo $image; ?>">
</div>
