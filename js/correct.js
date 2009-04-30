window.addEvent('domready', function() {
	var timeSlider = new TimeSlider({

		'container': {
			'gutter':    $$('#timeSlider .gutter')    [0],
			'range':     $$('#timeSlider .range')     [0],
			'startKnob': $$('#timeSlider .startKnob') [0],
			'endKnob':   $$('#timeSlider .endKnob')   [0],
			'startDate': $$('#timeSlider .startDate') [0],
			'endDate':   $$('#timeSlider .endDate')   [0]
		},

		'date': {
		  	'min':   new Date(date[0], date[1]-1, date[2], 7).getTime() / 1000,
		   	'max':   new Date(date[0], date[1]-1, date[2], 21).getTime() / 1000,
		  	'start': starttimestamp,
		  	'end':   endtimestamp
		},

		'callbacks': {
			'onChange': function (ts) {
				$('form_newstart').value = ts.getStart();
				$('form_newend').value = ts.getEnd();
			},
			'onDrag': function (ts) {
				ts.options.container.range.setStyle('backgroundPosition', '-' + ts.styles.knobStartLeft-1 + 'px 0');
			},
	//		'onKnobSwap': handleOnKnobSwap,
			'formatDate': function (date) {
				return date.format('%d.%m.%Y %H:%M');
			}
		}

	});

	timeSlider.options.container.range.setStyle('backgroundPosition', '-' + timeSlider.styles.knobStartLeft-1 + 'px 0');

});
