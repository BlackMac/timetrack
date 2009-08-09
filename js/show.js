$('correct_action_link').addEvent('click', function() {
		$('correct_widow').toggleClass('visible');
		$('correction_value').focus();
});

$('changeuserdata_action_link').addEvent('click', function() {
		$('changeuserdata_widow').toggleClass('visible');
		$('changeuserdata_pass').focus();
});

window.addEvent('domready', function() {

	SqueezeBox.assign($$('a.correctionLink'), {
		classWindow: 'correctionLightbox',
		size: {x: 600, y: 105}
	});

	SqueezeBox.assign($$('a.editorLink'), {
		parse: 'rel',
		classWindow: 'editorLightbox',
		size: {x: 600, y: 290}
	});

});
