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

// Get site name.
$sitename = JFactory::getApplication()->get('sitename');

// Prepare logo parameters.
$logo = isset($component['settings']['logo']) ? $component['settings']['logo'] : '';
$class = isset($component['settings']['class']) ? $component['settings']['class'] : '';
$visible_in = isset($component['settings']['visible_in']) ? $component['settings']['visible_in'] : array();

$link = !empty($component['settings']['link']) ? $component['settings']['link'] : 'index.php';
$logoAlt = !empty($component['settings']['alt']) ? $component['settings']['alt'] : $sitename;
$logoMobile = !empty($component['settings']['mobile-logo']) ? $component['settings']['mobile-logo'] : $logo;

// Prepare logo link.
if ($logo != '' && !preg_match('#^(https?:)?//#', $logo))
{
	$logo = JUri::root() . ltrim($logo, '/');
}

if ($logoMobile != '' && !preg_match('#^(https?:)?//#', $logoMobile))
{
	$logoMobile = JUri::root() . ltrim($logoMobile);
}
?>
<div class="sunfw-logo <?php echo SunFwSiteBootstrap::showOn($visible_in); ?>">
	<a href="<?php echo $link; ?>" title="<?php echo $sitename; ?>">
		<img
			class="<?php echo SunFwSiteBootstrap::getClass(
                "logo hidden-small-smartphone visible-md visible-lg img-responsive {$class}"
            ); ?>"
			alt="<?php echo $logoAlt; ?>" src="<?php echo $logo; ?>"
		/>
		<img
			class="<?php echo SunFwSiteBootstrap::getClass(
                "logo visible-small-smartphone hidden-md hidden-lg img-responsive {$class}"
            ); ?>"
			alt="<?php echo $logoAlt; ?>" src="<?php echo $logoMobile; ?>"
		/>
	</a>
</div>
