$('correct_action_link').addEvent('click', function() {
		$('correct_widow').toggleClass('visible');
		$('correction_value').focus();
});

$('changeuserdata_action_link').addEvent('click', function() {
		$('changeuserdata_widow').toggleClass('visible');
		$('changeuserdata_pass').focus();
});

var endofday = new Date();
endofday.set('Time', day_diff * 1000);

function countdownDayDiff() {

	var now = new Date();

	if(now.diff(endofday, 'day') != 0) return;

	var diff_second = now.diff(endofday, 'second');
	var diff_hour = (diff_second / 60 / 60).toInt();
	diff_second = diff_second - diff_hour*60*60;
	var diff_minute = (diff_second / 60).toInt();
	diff_second = diff_second - diff_minute*60;

	$('day_diff_hour').set('text', diff_hour);
	$('day_diff_minute').set('text', (diff_minute<10 ? '0' : '') + diff_minute);
	$('day_diff_seconds').set('text', (diff_second<10 ? '0' : '') + diff_second);
	
};

window.addEvent('domready', function() {

	SqueezeBox.assign($$('a.correctionLink'), {
		classWindow: 'correctionLightbox',
		size: {x: 600, y: 105}
	});

	countdownDayDiff.periodical(1000);

});

