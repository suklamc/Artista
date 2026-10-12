/**
 * @version    $Id$
 * @package    SUN Framework
 * @subpackage Layout Builder
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

function JSN_Countdown_Timer(selector, params) {
	// Get target element.
	var elm = document.querySelector(selector);

	if (!elm) {
		return;
	}

	// Prepare parameters.
	params = params || {};
	params.deadline = params.deadline || elm.getAttribute('data-deadline');
	params.daySelector = params.daySelector || elm.getAttribute('data-daySelector') || '.day';
	params.hourSelector = params.hourSelector || elm.getAttribute('data-hourSelector') || '.hour';
	params.minuteSelector = params.minuteSelector || elm.getAttribute('data-minuteSelector') || '.minute';
	params.secondSelector = params.secondSelector || elm.getAttribute('data-secondSelector') || '.second';

	// Parse deadline.
	var deadline = new Date(params.deadline).getTime();

	// Get necessary elements.
	var day = elm.querySelector(params.daySelector);
	var hour = elm.querySelector(params.hourSelector);
	var minute = elm.querySelector(params.minuteSelector);
	var second = elm.querySelector(params.secondSelector);

	// Verify parameters.
	if (!deadline || !day || !hour || !minute || !second) {
		return;
	}

	// Start countdown timer.
	var timer = setInterval(function () {
		var diff = deadline - (new Date().getTime());
		var days = Math.floor(diff / (1000 * 60 * 60 * 24));
		var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((diff % (1000 * 60)) / 1000);

		day.textContent = (days < 10 ? '0' : '') + days;
		hour.textContent = (hours < 10 ? '0' : '') + hours;
		minute.textContent = (minutes < 10 ? '0' : '') + minutes;
		second.textContent = (seconds < 10 ? '0' : '') + seconds;

		if (diff < 0) {
			clearInterval(timer);

			day.textContent = '00';
			hour.textContent = '00';
			minute.textContent = '00';
			second.textContent = '00';
		}
	}, 1000);
}
