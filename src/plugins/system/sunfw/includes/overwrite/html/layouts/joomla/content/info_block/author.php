<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
$images     = json_decode(@ $displayData['item']->images);
$img        = '';

if (!empty($images->image_fulltext))
{
    $img = JUri::base() . $images->image_fulltext;
}
elseif (!empty($images->image_intro))
{
    $img = JUri::base() . $images->image_intro;
}
?>
<dd class="createdby" itemprop="author" itemscope itemtype="https://schema.org/Person">
	<?php $author = ($displayData['item']->created_by_alias ? $displayData['item']->created_by_alias : $displayData['item']->author); ?>
	<?php $tmpAuthor = $author;?>
	<?php $author = '<span itemprop="name">' . $author . '</span>'; ?>
	<i class="fa fa-user"></i>
	<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
		<?php echo JHtml::_('link', $displayData['item']->contact_link, $author, array('itemprop' => 'url')); ?>
	<?php else :?>
		<?php echo $author ?>
	<?php endif; ?>
</dd>
<span style="display: none;" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
	<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
		<meta itemprop="url" content="<?php echo $img; ?>">
		<meta itemprop="width" content="auto" />
		<meta itemprop="height" content="auto" />
	</span>
	<meta itemprop="name" content="<?php echo $tmpAuthor; ?>" />
</span>
