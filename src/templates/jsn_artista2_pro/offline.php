<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// Get coming soon settings.
$renderer = SunFwSite::getInstance();
$settings = isset($renderer->coming_soon_data) ? $renderer->coming_soon_data : array();

if (!empty($settings['enabled']) && (int) $settings['enabled'])
{
	// Let SUN Framework handle the rest.
	return $renderer->render( 'offline' );
}

// Joomla 4.2+ replaced the login secret-key field with Multi-factor Authentication,
// which runs after the password step, so the form never needs a secret key field.
$twofactormethods = array();
$app              = Factory::getApplication();
$doc              = Factory::getDocument();
$this->language   = $doc->language;
$this->direction  = $doc->direction;

// Output as HTML5
$doc->setHtml5(true);

$fullWidth = 1;

// Add JavaScript Frameworks
HTMLHelper::_('bootstrap.framework');

$doc->addScriptVersion($this->baseurl . '/templates/' . $this->template . '/js/template.js');

// Add Stylesheets
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/template.css');
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/bootstrap.css');

// Use of Google Font
if ($this->params->get('googleFont'))
{
	$doc->addStyleSheet('//fonts.googleapis.com/css?family=' . $this->params->get('googleFontName'));
	$doc->addStyleDeclaration("
	h1, h2, h3, h4, h5, h6, .site-title {
		font-family: '" . str_replace('+', ' ', $this->params->get('googleFontName')) . "', sans-serif;
	}");
}

// Template color
if ($this->params->get('templateColor'))
{
	$doc->addStyleDeclaration("
	body.site {
		border-top: 3px solid " . $this->params->get('templateColor') . ";
		background-color: " . $this->params->get('templateBackgroundColor') . ";
	}
	a {
		color: " . $this->params->get('templateColor') . ";
	}
	.nav-list > .active > a,
	.nav-list > .active > a:hover,
	.dropdown-menu li > a:hover,
	.dropdown-menu .active > a,
	.dropdown-menu .active > a:hover,
	.nav-pills > .active > a,
	.nav-pills > .active > a:hover,
	.btn-primary {
		background: " . $this->params->get('templateColor') . ";
	}");
}

// Check for a custom CSS file
$userCss = JPATH_SITE . '/templates/' . $this->template . '/css/user.css';

if (file_exists($userCss) && filesize($userCss) > 0)
{
	$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/user.css');
}

// Load optional RTL Bootstrap CSS
HTMLHelper::_('bootstrap.loadCss', false, $this->direction);

// Logo file or site title param
$sitename = $app->get('sitename');

if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . Uri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '" />';
}
elseif ($this->params->get('sitetitle'))
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . htmlspecialchars($this->params->get('sitetitle')) . '</span>';
}
else
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . $sitename . '</span>';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<jdoc:include type="head" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" />
</head>

<body class="site offline">
	<div id="content">
		<div class="outer jsn-offline content-inner">
			<div class="header">
			<?php if (!empty($logo)) : ?>
				<h2><?php echo $logo; ?></h2>
			<?php else : ?>
				<h1><?php echo htmlspecialchars($app->get('sitename')); ?></h1>
			<?php endif; ?>
			<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
				<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->get('sitename')); ?>" />
			<?php endif; ?>
			<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
				<p><?php echo $app->get('offline_message'); ?></p>
			<?php elseif ($app->get('display_offline_message', 1) == 2) : ?>
				<p><?php echo Text::_('JOFFLINE_MESSAGE'); ?></p>
			<?php endif; ?>
			</div>
			<jdoc:include type="message" />
			<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
				<fieldset class="form-inline">
					<div class="input-group">
						<!-- <label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label> -->
						<div class="input-group-prepend">
							<i class="fa fa-user" aria-hidden="true"></i>
						</div>
						<input class="form-control pd-left" name="username" id="username" type="text" placeholder="Username" title="<?php echo Text::_('JGLOBAL_USERNAME'); ?>" />
					</div>
					<div class="input-group">
						<!-- <label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label> -->
						<div class="input-group-prepend">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</div>
						<input class="form-control pd-left" type="password" name="password" id="password" placeholder="Password" title="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>" />
					</div>
					<?php if (count($twofactormethods) > 1) : ?>
					<label for="secretkey"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
					<input type="text" name="secretkey" id="secretkey" title="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>" />
					<?php endif; ?>

					<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo Text::_('JLOGIN'); ?>" />

					<input type="hidden" name="option" value="com_users" />
					<input type="hidden" name="task" value="user.login" />
					<input type="hidden" name="return" value="<?php echo base64_encode(Uri::base()); ?>" />
					<?php echo HTMLHelper::_('form.token'); ?>
				</fieldset>
			</form>
		</div>
	</div>
</body>
</html>
