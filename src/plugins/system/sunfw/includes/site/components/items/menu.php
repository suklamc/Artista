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

$id = isset($component['id']) ? $component['id'] : '';
$class = isset($component['settings']['class']) ? $component['settings']['class'] : '';
$menuType = isset($component['settings']['menu-type']) ? $component['settings']['menu-type'] : '';
$visible_in = isset($component['settings']['visible_in']) ? $component['settings']['visible_in'] : array();

$html = SunFwMenu::render($menuType, $id, $component);
?>
<div class="<?php echo "{$class} " . SunFwSiteBootstrap::showOn($visible_in); ?>">
	<?php echo $html; ?>
</div>
