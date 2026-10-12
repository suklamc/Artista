<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  JSNTPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

// Get media files.
$this->get_media();

if (isset($this->media) and count($this->media))
:
	?>
<ul class="thumbnails">
	<?php
	foreach ($this->media as $file)
	:
		
		$img = '<span class="jsn-file-thumb icon-file" alt="' . basename($file) . '"></span><span class="caption">' . basename($file) . '</span>';
		
		if ($dimension = @getimagesize(str_replace(JUri::root(), JPATH_ROOT . '/', $file)))
		{
			// Prepare image dimension
			if (( $dimension[0] / $dimension[1] ) > ( 128 / 96 ))
			{
				$width = 128;
				$height = (int) 128 / ( $dimension[0] / $dimension[1] );
			}
			elseif (( $dimension[0] / $dimension[1] ) < ( 128 / 96 ))
			{
				$height = 96;
				$width = (int) 96 * ( $dimension[0] / $dimension[1] );
			}
			else
			{
				$width = 128;
				$height = 96;
			}
			
			// Generate image tag
			$img = '<img src="' . $file . '" alt="' . basename($file) . '" width="' . $width . '" height="' . $height . '" />';
		}
		?>
	<li
		class="<?php if (realpath( str_replace(JUri::root(), JPATH_ROOT, $file) ) == $this->selected) echo 'selected'; ?>">
		<a class="thumbnail" href="javascript:void(0)"><?php echo $img; ?></a>
	</li>
	<?php endforeach; ?>
</ul>
<?php else : ?>
<div class="alert"><?php echo JText::_('SUNFW_NO_MEDIA_ITEM'); ?></div>
<?php
endif;

// Prepare variables for rendering reload form.
list($request_uri, $query_string) = explode('?', $this->baseUrl);
?>
<form name="JSNMediaReloadForm" action="<?php echo $request_uri; ?>"
	method="GET">
	<input type="hidden" name="action" value="index" />
	<?php foreach (explode('&', $query_string) as $pair) : list($k, $v) = explode('=', $pair); ?>
	<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
	<?php endforeach; ?>
	<input type="hidden" name="handler"
		value="<?php echo $this->handler; ?>" /> <input type="hidden"
		name="element" value="<?php echo $this->element; ?>" /> <input
		type="hidden" name="selected"
		value="<?php echo str_replace($this->abs_root, '', $this->selected); ?>" />
</form>
