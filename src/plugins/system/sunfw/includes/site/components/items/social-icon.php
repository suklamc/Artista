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

$items = isset($component['settings']['items']) ? $component['settings']['items'] : array();
$color = isset($component['settings']['color']) ? $component['settings']['color'] : '';
$colorHover = isset($component['settings']['color_hover']) ? $component['settings']['color_hover'] : '';

$size = isset($component['settings']['size']) ? $component['settings']['size'] : '';
$target = !empty($component['settings']['target']) ? $component['settings']['target'] : '_blank" rel="nofollow noopener noreferrer';

$class = isset($component['settings']['class']) ? $component['settings']['class'] : '';
$visible_in = isset($component['settings']['visible_in']) ? $component['settings']['visible_in'] : array();

$style = '';
$colorHoverStyle = '';

if ($color != '')
{
	$style = 'color: ' . $color . ';';
}

if ($size != '')
{
	$style .= ' font-size: ' . $size . 'px;';
}

if ($colorHover != '' && $color != '')
{
	$colorHoverStyle = ' onMouseOver="this.style.color=\'' . $colorHover .'\'" onMouseOut="this.style.color=\'' . $color . '\'"';
}
if (empty($items))
	return '';
?>
<div class="sunfw-social <?php echo SunFwSiteBootstrap::showOn($visible_in); ?>">
	<ul class="social <?php echo SunFwSiteBootstrap::getClass("margin-bottom-0 list-inline {$class}"); ?>">
		<?php
		foreach ($items as $item)
		{
			$linkIcon = '';

			if (isset($item['link']))
			{
				$linkIcon = $item['link'];
			}

			if (isset($linkIcon) && $linkIcon != '')
			{
				$linkIcon = ' href="' . $linkIcon . '"';
			}
			else
			{
				$linkIcon = ' href="#"';
			}

			$title = '';

			if (isset($item['title']))
			{
				$title = $item['title'];
			}
			?>
			<li class="<?php echo SunFwSiteBootstrap::getClass('list-inline-item'); ?>">
                <a<?php echo $linkIcon;?><?php echo $colorHoverStyle;?> target="<?php echo $target;?>" class="btn-social-icon" style="<?php if ( $style != '' ) { echo $style; }  ?>">
				<i class="<?php echo $item['icon']; ?>"></i>
					<?php
                    if ($title != '')
                    {
                        echo '<span>' . $title . '</span>';
                    }
                    ?>
				</a>
            </li>
		<?php } ?>
	</ul>
</div>
