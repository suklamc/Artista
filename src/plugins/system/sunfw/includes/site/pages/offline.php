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
defined('_JEXEC') or die();

// Continue only if the current template supports custom coming soon.
$xml = SunFwHelper::getManifest($this->template);

if (!$xml || !( $xml = current($xml->xpath('//feature[@name="coming_soon"]')) ) || (string) $xml['enabled'] !== 'yes')
{
	return;
}

// Get coming soon settings.
$settings = isset($this->coming_soon_data) ? $this->coming_soon_data : array();

if (empty($settings['enabled']) || ! (int) $settings['enabled'])
{
	return;
}

// Get Joomla's global configuration.
$config = JFactory::getConfig();

// Get Joomla's document object.
$document = JFactory::getDocument();

// Prepare necessary data.
$title = empty($settings['page-title']) ? $config->get('sitename') : $settings['page-title'];
$content = empty($settings['page-content']) ? $config->get('offline_message') : $settings['page-content'];

if (!empty($settings['deadline']))
{
    // Load countdown timer script.
	$document->addScript("{$this->baseurl}/plugins/system/sunfw/assets/joomlashine/site/js/countdown-timer.js");
}

// Check if login with 2 factor methods is enabled?
require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

$twofactormethods = UsersHelper::getTwoFactorMethods();

// Get template's manifest cache.
$manifest = SunFwHelper::getManifestCache($this->template);

// If the current template has template file for coming soon page, just include that file.
if (is_file(JPATH_THEMES . "/{$this->template}/html/coming-soon.php"))
{
	return include JPATH_THEMES . "/{$this->template}/html/coming-soon.php";
}

// Otherwise, print HTML for coming soon page.
?>
<!DOCTYPE html>
<!-- <?php echo "{$manifest->name} {$manifest->version}"; ?> -->
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<base href="<?php echo JURI::base(); ?>" />

	<title><?php echo $title; ?></title>

	<?php foreach ($document->_styleSheets as $src => $attrs) : ?>
	<link href="<?php echo $src; ?>" type="<?php echo $attrs['type']; ?>" rel="stylesheet" />
	<?php endforeach; ?>

	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" rel="stylesheet" />
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/custom/custom.css" rel="stylesheet" />
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/bootstrap.css" rel="stylesheet" />

	<style type="text/css">
		<?php echo implode("\n", $document->_style); ?>
		#content {
			display: flex;
			height: calc(100vh);
			align-items: center;
			text-align: center;
			justify-content: center;
			<?php if (!empty($settings['background-image'])) : ?>
			background: url(<?php echo "{$this->baseurl}/{$settings['background-image']}"; ?>);
			<?php endif; ?>
		}
        .countdown-timer {
			display: flex;
            margin: 10px 0 20px;
			text-align: center;
			justify-content: center;
			font-size: 2em;
        }
        .countdown-timer > div {
			margin: 0 10px;
        }
        .countdown-timer .countdown {
            font-size: 2.5em;
        }
	</style>

	<?php if ($this->app->get('debug_lang', '0') == '1' || $this->app->get('debug', '0') == '1') : ?>
	<link href="<?php echo $this->baseurl; ?>/media/cms/css/debug.css" rel="stylesheet" />
	<?php endif; ?>

	<?php foreach ($document->_scripts as $src => $attrs) : ?>
	<script src="<?php echo $src; ?>" type="<?php echo $attrs['type']; ?>"></script>
	<?php endforeach; ?>

	<script type="text/javascript">
		<?php echo implode("\n", $document->_script); ?>
	</script>

	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
</head>
<body>
	<div id="content">
		<div class="content-inner">
			<?php if (!empty($settings['logo'])) : ?>
			<img src="<?php echo "{$this->baseurl}/{$settings['logo']}"; ?>" />
			<?php endif; ?>

			<h2><?php echo $title; ?></h2>

			<p><?php echo $content; ?></p>

            <?php if (!empty($settings['deadline'])) : ?>
			<div class="countdown-timer" data-deadline="<?php echo $settings['deadline']; ?>">
                <div class="day-container">
                    <div class="day countdown">&nbsp;&nbsp;</div>
                    <?php echo JText::_('SUNFW_DAYS'); ?>
                </div>
                <div class="hour-container">
                    <div class="hour countdown">&nbsp;&nbsp;</div>
					<?php echo JText::_('SUNFW_HOURS'); ?>
                </div>
                <div class="minute-container">
                    <div class="minute countdown">&nbsp;&nbsp;</div>
					<?php echo JText::_('SUNFW_MINUTES'); ?>
                </div>
                <div class="second-container">
                    <div class="second countdown">&nbsp;&nbsp;</div>
					<?php echo JText::_('SUNFW_SECONDS'); ?>
                </div>
            </div>
            <script type="text/javascript">
	            JSN_Countdown_Timer("#content .countdown-timer");
            </script>
            <?php endif; ?>

			<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login">
				<fieldset class="form-inline">
					<div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>
						<input class="form-control" name="username" id="username" type="text" title="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" />
					</div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        </div>
						<input class="form-control" type="password" name="password" id="password" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" />
					</div>

					<?php if (count($twofactormethods) > 1) : ?>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></span>
                        </div>
                        <input type="text" name="secretkey" id="secretkey" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" />
                    </div>
					<?php endif; ?>

					<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGIN'); ?>" />

					<input type="hidden" name="option" value="com_users" />
					<input type="hidden" name="task" value="user.login" />
					<input type="hidden" name="return" value="<?php echo base64_encode(JUri::base()); ?>" />
					<?php echo JHtml::_('form.token'); ?>
				</fieldset>
			</form>
		</div>
	</div>

	<?php echo $this->doc->getBuffer('modules', 'debug', array('style' => 'none')); ?>
</body>
</html>

