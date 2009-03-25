$('correct_action_link').addEvent('click', function() {
		$('correct_widow').toggleClass('visible');
		$('correction_value').focus();
});

$('changeuserdata_action_link').addEvent('click', function() {
		$('changeuserdata_widow').toggleClass('visible');
		$('changeuserdata_user').focus();
});
