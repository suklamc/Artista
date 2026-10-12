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

// Get Joomla document object.
$doc = JFactory::getDocument();

// Prepare current nodes for JSTree.
if ($current = str_replace(array(
	'/',
	'\\'
), '-DS-', trim(str_replace(realpath(JPATH_ROOT), '', $this->work_dir), '/\\')))
{
	for ($i = 0, $n = count($tmp = explode('-DS-', $current)); $i < $n; $i++)
	{
		is_array($current) or $current = array();
		
		for ($j = 0; $j <= $i; $j++)
		{
			$current[$i][] = $tmp[$j];
		}
		
		$current[$i] = implode('-DS-', $current[$i]);
	}
}

// Prepend root to current nodes if needed.
if ($this->root == '/')
{
	if (is_array($current))
	{
		array_unshift($current, str_replace(array(
			'/',
			'\\'
		), '-DS-', $this->root));
	}
	else
	{
		$current = array(
			str_replace(array(
				'/',
				'\\'
			), '-DS-', $this->root)
		);
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $doc->language; ?>"
	dir="<?php echo $doc->direction; ?>">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<?php
	// Load and render document head.
	$head = $doc->loadRenderer('head');
	
	echo $head->render('');
	?>
	<style type="text/css">
body {
	border: 1px solid #bbb;
}

/* Panels */
.ui-layout-pane-west, .ui-layout-pane.outer-center, .ui-layout-pane.inner-center,
	.ui-layout-pane.ui-layout-south {
	border: 0;
	padding: 0;
}

.ui-layout-pane-west>div, .ui-layout-pane.inner-center>div,
	.ui-layout-pane.ui-layout-south>div {
	padding: 8px;
}

.ui-layout-pane.inner-center, .ui-layout-pane.ui-layout-south>div {
	text-align: center;
}

.ui-layout-pane.ui-layout-south>div>div {
	display: inline-block;
	margin: 12px 0;
}

/* Directory tree */
.jstree-default .jstree-icon.jstree-themeicon {
	background: transparent;
}

.jstree-icon.jstree-themeicon:before {
	content: "\f114";
	font-family: FontAwesome;
	font-style: normal;
}

/* Thumbnails */
.jsn-bootstrap ul.thumbnails {
	margin: 0;
}

.jsn-bootstrap ul.thumbnails>li {
	margin: 9px;
}

.jsn-bootstrap ul.thumbnails li a.thumbnail {
	width: 128px;
	height: 96px;
	overflow: auto;
}

.jsn-bootstrap ul.thumbnails li.selected a.thumbnail {
	border-color: orange;
}

.jsn-bootstrap ul.thumbnails li a.thumbnail .icon-file {
	display: block;
	margin: 24px auto;
}

.jsn-bootstrap ul.thumbnails li a.thumbnail:hover .icon-file {
	text-decoration: none;
}

/* Upload form */
.ajax-upload-dragdrop .ajax-file-upload {
	background-color: #f7f7f7;
	background-image: -webkit-gradient(linear, left top, left bottom, from(#f7f7f7),
		to(#e7e7e7));
	background-image: -webkit-linear-gradient(top, #f7f7f7, #e7e7e7);
	background-image: -moz-linear-gradient(top, #f7f7f7, #e7e7e7);
	background-image: -ms-linear-gradient(top, #f7f7f7, #e7e7e7);
	background-image: -o-linear-gradient(top, #f7f7f7, #e7e7e7);
	font-size: 12px;
	font-weight: lighter;
	letter-spacing: 0;
	line-height: 27px;
	box-shadow: none;
	padding: 0 15px 0;
	border: 1px solid rgba(102, 102, 102, 0.4);
	color: #616161;
}

.ajax-upload-dragdrop {
	width: 90%;
}

div.ajax-file-upload-red, div.ajax-file-upload-green {
	padding: 4px 20px;
	line-height: 28px;
}

div.ajax-file-upload-red {
	box-shadow: none;
}

.ajax-upload-dragdrop {
	border-width: 1px;
	padding: 5px 5px 0;
}

#start-upload+.ajax-upload-dragdrop {
	margin-top: 9px;
}

.ajax-file-upload {
	margin: 0px 10px 6px 0;
	padding: 3px 10px 0;
}

.ajax-file-upload-statusbar {
	margin: 0;
	border: 0;
	padding: 0;
	width: auto;
}

.ajax-file-upload-statusbar>div {
	display: inline-block;
}

.ajax-file-upload-filename {
	margin: 0 6px;
	width: auto;
	line-height: 40px;
}

.ajax-file-upload-progress {
	margin: 8px 10px 0 4px;
	vertical-align: top;
}

.ajax-file-upload-red, .ajax-file-upload-green {
	line-height: 33px;
}
</style>
</head>
<body>
	<div class="panelize">
		<div class="outer-center">
			<div class="inner-center jsn-bootstrap">
				<div id="jsn-media-image-list" class="content-container">
					<?php include dirname( __FILE__ ) . '/list.php'; ?>
				</div>
			</div>
			<div class="ui-layout-south">
				<div>
					<div id="jsn-media-upload-form">
						<?php echo JText::_('SUNFW_SELECT_FILE'); ?>
					</div>
					<div id="start-upload" class="ajax-file-upload-green"><?php echo JText::_('SUNFW_START_UPLOAD'); ?></div>
				</div>
			</div>
		</div>
		<div class="ui-layout-west">
			<div id="jsn-media-directory-tree" class="content-container"><?php echo JText::_('SUNFW_LOADING'); ?></div>
		</div>
	</div>

	<div class="sunfw-loading-mask">
		<span class="sunfw-loading-indicator"></span>
	</div>

	<script type="text/javascript">
		jQuery(function($) {
			var self = {};

			// Get necessary elements
			self.$tree = $('#jsn-media-directory-tree');
			self.$media = $('#jsn-media-image-list');
			self.$mask = $('.sunfw-loading-mask');

			self.$uForm = $('#jsn-media-upload-form');
			self.$uStat = $('#jsn-media-upload-status');
			self.$uMsg = $('#jsn-media-upload-status-message');

			// Initialize necessary variables for browsing and selecting image
			var server = '<?php echo $this->list_url; ?>', root = '<?php echo $this->root; ?>',

			expandNode = function(id, cb) {
				var node = $('#' + id + ' > .jstree-ocl');

				if ( ! node.length ) {
					return setTimeout(function() {
						expandNode(id, cb);
					}, 50);
				}

				node.trigger('click');

				if (typeof cb == 'function') {
					cb(node);
				}
			},

			selectNode = function(id, cb) {
				var node = $('#' + id + ' > .jstree-anchor');

				if ( ! node.length ) {
					return setTimeout(function() {
						selectNode(id, cb);
					}, 50);
				}

				node.addClass('jstree-clicked');

				if (typeof cb == 'function') {
					cb(node);
				}
			},

			getActive = function(node) {
				var deep = node.id != '#' ? node.id.split('-DS-') : [];

				return deep.join('/');
			},

			getList = function(active) {
				// Show loading indicator.
				self.$mask.show();

				self.$media.load(server, 'selected=' + getActive(active), function() {
					handleSelect();

					// Hide loading indicator.
					self.$mask.hide();
				});
			},

			handleSelect = function() {
				// Register event handler for selecting image
				self.$media.find('a.thumbnail').unbind('click').bind('click', function() {
					// Set selected state.
					$(this).parent().addClass('selected').siblings().removeClass('selected');

					// Generate path to selected image
					var selected = self.$tree.find('.jstree-clicked').parent().attr('id');

					selected = (selected ? selected.replace(/-DS-/g, '/') : '') + '/' + $(this).find('img, span.jsn-file-thumb').attr('alt');

					if ( selected.substr(0, 1) == '/' ) {
						selected = selected.replace(/^\/+/, '', selected);
					}

					// Call handler to update
					if (window.parent && typeof window.parent['<?php echo $this->handler; ?>'] == 'function') {
						window.parent['<?php echo $this->handler; ?>'](selected, '#<?php echo $this->element; ?>');
					}
				});
			};

			// Initialize directory tree
			self.$tree.jstree({
				core: {
					data: {
						url: server,
						data: function(node) {
							return {
								action: 'dir',
								selected: getActive(node),
							};
						},
						complete: function() {
							// Load initially open
							var jstree = self.$tree.data('jstree');

							if (jstree.settings.initially_open.length) {
								if (jstree.opening_initial === undefined) {
									jstree.opening_initial = 0;

									return expandNode(jstree.settings.initially_open[0], function() {
										jstree.opening_initial++;
									});
								}

								if (jstree.opening_initial < jstree.settings.initially_open.length) {
									expandNode(jstree.settings.initially_open[jstree.opening_initial], function() {
										jstree.opening_initial++;
									});
								} else {
									setTimeout( function() {
										selectNode(jstree.settings.initially_open[ jstree.opening_initial - 1 ]);
									}, 200 );
								}
							}

							// Trigger initialized event
							self.initialized || $(document.body).trigger('initialized');
						}
					}
				},
				initially_open: [<?php echo @is_array($current) ? "'" . implode("', '", $current) . "'" : ''; ?>]
			}).bind("select_node.jstree", function(event, data) {
				// Load image files insides a directory
				getList(data.node);
			});

			// Initialize layout
			$(document.body).bind('initialized', function() {
				$(document.body).children('.panelize')
				.css({
					width: (window.innerWidth - 2) + 'px',
					height: (window.innerHeight - 2) + 'px'
				})
				.layout({
					center__paneSelector: '.outer-center',
					center__childOptions: {
						center__paneSelector: '.inner-center',
						south: {
							size: 'auto',
							minSize: 80
						}
					},
					west: {
						size: 'auto',
						minSize: 250
					}
				});

				// Handle window resize event
				$(window).resize(function() {
					// Reload the media selector window to re-initialize layout
					document.JSNMediaReloadForm.submit();
				});

				// Hide loading indicator.
				self.$mask.hide();

				self.initialized = true;
			});

			// Initialize Ajax File Upload
			window.JSNMediaUpload = $('#jsn-media-upload-form').uploadFile({
				url: '<?php echo $this->upload_url; ?>',
				multiple: false,
				autoSubmit: false,
				showFileCounter: false,
				allowedTypes: '<?php echo $this->extensions; ?>',
				fileName: 'file',
				cancelStr: '<?php echo JText::_('SUNFW_CLEAR'); ?>',
				onSelect: function(files) {
					// Remove previously selected file
					$('.ajax-file-upload-statusbar').remove();

					// Show start upload button
					$('#start-upload').show();

					// Make selected file and upload button visible.
					setTimeout(function() {
						if ($('.ajax-file-upload-statusbar').length) {
							// Move file selector to bottom.
							$('#jsn-media-upload-form').parent().append($('.ajax-upload-dragdrop'));

							$('.ajax-file-upload-statusbar .ajax-file-upload-red').click(function() {
								// Move file selector to top.
								$('#jsn-media-upload-form').parent().prepend($('.ajax-upload-dragdrop'));

								// Hide start upload button
								$('#start-upload').hide();
							});
						} else {
							// Check if there is any error.
							var errorLog = $('#jsn-media-upload-form').next();

							if (errorLog.children().length) {
								var errorMsg = errorLog.children().text();

								// Clear error log.
								errorLog.html('');

								setTimeout(function() {
									alert(errorMsg);
								}, 5);

								// Hide start upload button
								$('#start-upload').hide();
							}
						}
					}, 5);
				},
				onSubmit: function(files) {
					// Hide start upload button
					$('#start-upload').hide();

					// Get selected directory
					var selected = self.$tree.find('.jstree-clicked').parent().attr('id');

					// Update upload URL.
					this.url += '&selected=' + (selected ? selected.replace(/-DS-/g, '/') : '');
				},
				onSuccess: function(files, data, xhr) {
					if (data == '') {
						// Get selected directory
						var selected = self.$tree.find('.jstree-clicked').parent().attr('id');

						// Update image list
						getList({id: selected ? selected : '#'});
					} else {
						// Show error message
						$('.ajax-file-upload-statusbar').children().hide().filter('.ajax-file-upload-filename').html(data).show();
					}
				},
			});

			$('#start-upload').click(function() {
				JSNMediaUpload.startUpload();
			}).hide();

			// Register event handle to select media file
			handleSelect();
		});
	</script>
</body>
</html>
