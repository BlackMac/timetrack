$('correct_action_link').addEvent('click', function() {
		$('correct_widow').toggleClass('visible');
		$('correction_value').focus();
});

$('changeuserdata_action_link').addEvent('click', function() {
		$('changeuserdata_widow').toggleClass('visible');
		$('changeuserdata_pass').focus();
});

var countdownTimer;
var endofday = new Date().set('Time', day_diff * 1000);
var pageLoad = $time();

function countdownDayDiff() {

	var now = new Date();

	if(now.diff(endofday, 'day') != 0 || laststateIn != "1") {
		$clear(countdownTimer);
		return;
	}

	if($time() - pageLoad > 60 * 60 * 1000) {
		location.href = location.href;
	}

	var diff_second = now.diff(endofday, 'second');
	var diff_hour = (diff_second / 60 / 60).toInt();
	diff_second = diff_second - diff_hour*60*60;
	var diff_minute = (diff_second / 60).toInt();
	diff_second = diff_second - diff_minute*60;

	if(diff_second < 0 || diff_minute < 0 || diff_hour < 0) {
		$('day_diff').removeClass('negative');
		$$('.day_diff_minus').setStyle('display', 'none');
		diff_hour = Math.abs(diff_hour);
		diff_minute = Math.abs(diff_minute);
		diff_second = Math.abs(diff_second);
	}

	$$('.day_diff_hour').set('text', diff_hour);
	$$('.day_diff_minute').set('text', (diff_minute<10 ? '0' : '') + diff_minute);
	$$('.day_diff_seconds').set('text', (diff_second<10 ? '0' : '') + diff_second);
	
};

window.addEvent('domready', function() {
	$('previous_months_link').addEvent('click', function (e) {
		e.preventDefault();
		$('additional_months').toggleClass('hidden');
	}.bindWithEvent());
	SqueezeBox.assign($$('a.correctionLink'), {
		classWindow: 'correctionLightbox',
		size: {x: 600, y: 105}
	});

	countdownTimer = countdownDayDiff.periodical(1000);

	SqueezeBox.assign($$('a.editorLink'), {
		parse: 'rel',
		classWindow: 'editorLightbox',
		size: {x: 600, y: 290}
	});

});

