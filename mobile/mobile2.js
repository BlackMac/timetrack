$.jQTouch({
    icon: '../img/favicon.png',
    statusBar: 'black',
    startupScreen: '../img/startupimage.png',
	formSelector: '.nothing',
    preloadImages: [
        '../img/mobile/button_red.png',
        '../img/mobile/button_green.png'
        ]
	
});

function pad(number, digits) {
	if (!digits) digits=2;
	number=number+"";
	while(number.length<digits) number="0"+number;
	return number;
}

function toDate(thedate) {
	return pad(thedate.getDate())+'.'+pad(thedate.getMonth())+'.'+pad(thedate.getFullYear());
}

function toTime(thedate) {
	return pad(thedate.getHours())+':'+pad(thedate.getMinutes());
}

$(function(){
	
	$('.expressform').submit(function() {
	
		var ajaxparams = {h: $(this).children('input[name=h]')[0].value , d: $(this).children('button[name=d]')[0].value };
		
	    $.ajax({
	        url:'../log.php',
	        data: ajaxparams,
	        type: 'GET',
	        success: function (res) {
				var timestring=res.substr(2,19);
				var datetime=timestring.split('T');
				var date=datetime[0].split('-');
				var time=datetime[1].split(':');
				
				var date = new Date(date[0], date[1], date[2], parseInt(time[0], 10), time[1], time[2]);

				$('.last_date').text(toDate(date));
				$('.last_time').text(toTime(date));
					
				if (ajaxparams.d=="in") {
					$('#change_button').attr({
						'value':'out',
						'class':'go'
					});
					$('.direction_action').text('GEKOMMEN');
				} else {
					$('#change_button').attr({
						'value':'in',
						'class':'come'
					});
					$('.direction_action').text('GEGANGEN');
				}
	        }
	    });
		
		return false;
	
	});

    $('#graphs').bind('pageTransitionEnd', function(e, info){
/*			$('#presenceGraph').attr('src', presenceGraph); */

			if($('#differenceGraph').length == 0)
				$('#graphtoolbar').after('<img style="width: 100%" id="differenceGraph" src="' + differenceGraph + '">');

			if($('#presenceGraph').length == 0)
				$('#graphtoolbar').after('<img style="width: 100%" id="presenceGraph" src="' + presenceGraph + '">');
				
        });
	

});

/*

$$('.expressform').addEvent('submit', function(evnt) {
	wait_div.inject(document.body);
	evnt.stop();
	var ajaxparams = {h: evnt.target.getElement('input[name=h]').get('value') , d: evnt.target.getElement('button[name=d]').get('value') };
	var ajax = new Request({url:'../log.php', onComplete: function(res) { 
		var timestring=res.substr(2,19);
		var datetime=timestring.split('T');
		var date=datetime[0].split('-');
		var time=datetime[1].split(':');
		
		var date = new Date(date[0], date[1], date[2], parseInt(time[0]), time[1], time[2]);
		
		$$('.last_date').set('text', toDate(date));
		$$('.last_time').set('text', toTime(date));
		
		if ($('change_button').value=="in") {
			$('change_button').set({
				'value':'out',
				'class':'go'
			});
			$$('.direction_action').set('text','GEKOMMEN');
		} else {
			$('change_button').set({
				'value':'in',
				'class':'come'
			});
			$$('.direction_action').set('text','GEGANGEN');
		}
		wait_div.dispose();
	} }).get(ajaxparams);
}.bindWithEvent());

*/
