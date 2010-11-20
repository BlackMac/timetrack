$.jQTouch({
    icon: '../img/favicon.png',
    statusBar: 'black',
    startupScreen: '../img/startupimage.png',
	formSelector: '.nothing',
    preloadImages: [
//        '../img/mobile/button_red.png',
//        '../img/mobile/button_green.png'
        ]
	
});

function pad(number, digits) {
	if (!digits) digits=2;
	number=number+"";
	while(number.length<digits) number="0"+number;
	return number;
}

function toDate(thedate) {
	return pad(thedate.getDate())+'.'+pad(thedate.getMonth()+1)+'.'+pad(thedate.getFullYear());
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
				
				var date = new Date(date[0], date[1]-1, date[2], parseInt(time[0], 10), time[1], time[2]);

				$('.last_date')[0].innerHTML = toDate(date);
				$('.last_time')[0].innerHTML = toTime(date);
					
				if (ajaxparams.d=="in") {
					$('#change_button').attr({
						'value':'out',
						'class':'go'
					});
					$('#change_button')[0].innerHTML ='Abmelden';
					$('.direction_action')[0].innerHTML = 'GEKOMMEN';
				} else {
					$('#change_button').attr({
						'value':'in',
						'class':'come'
					});
					$('#change_button')[0].innerHTML ='Anmelden';
					$('.direction_action')[0].innerHTML = 'GEGANGEN';
				}
	        }
	    });
		
		return false;
	
	});
	
    $.ajax({
		url:"../json-rpc.php",
		data: '{"id":"23342423","method":"getLastDay","params":{"hash":"' + $('input[name=h]')[0].value + '"}}',
		type: 'POST',
		dataType: 'json',
		contentType: 'application/json',
        success: function (data) {
			if(data.error != null || !data.result) {
				return;
			}
			
			if(data.result.laststateIn === true) {
				$('#change_button').attr({
					'value':'out',
					'class':'go'
				});
				$('#change_button')[0].innerHTML ='Abmelden';
				$('.direction_action')[0].innerHTML = 'GEKOMMEN';
				
				var startstamp = new Date(data.result.startstamp * 1000);
				$('.last_date')[0].innerHTML = toDate(startstamp);
				$('.last_time')[0].innerHTML = toTime(startstamp);
				
			} else {
				$('#change_button').attr({
					'value':'in',
					'class':'come'
				});
				$('#change_button')[0].innerHTML ='Anmelden';
				$('.direction_action')[0].innerHTML = 'GEGANGEN';
				
				var endstamp = new Date(data.result.endstamp * 1000);
				$('.last_date')[0].innerHTML = toDate(endstamp);
				$('.last_time')[0].innerHTML = toTime(endstamp);				
			}
			
			$('#stats_startstamp').text(data.result.start);
			var pause = new Date(data.result.pause * 1000);
			$('#stats_pause').text([pause.getUTCHours(), pad(pause.getUTCMinutes()), pad(pause.getUTCSeconds())].join(':'));
			var finishingtime = new Date((data.result.startstamp+data.result.pause+60*60*8.75)*1000);
			$('#stats_finishingtime').text([finishingtime.getHours(), pad(finishingtime.getMinutes()), pad(finishingtime.getSeconds())].join(':'));
			var diff = new Date(Math.abs(data.result.diff) * 1000);
			$('#stats_diff').text((data.result.diff < 0 ? '-' : '') + [diff.getUTCHours(), pad(diff.getUTCMinutes()), pad(diff.getUTCSeconds())].join(':'));
			var monthdiff = new Date(data.result.monthdiff * 1000);
			$('#stats_monthdiff').text([monthdiff.getUTCHours(), pad(monthdiff.getUTCMinutes()), pad(monthdiff.getUTCSeconds())].join(':'));
        }
    });
	
//	$('#page2').bind('pageTransitionStart', function(e, info){
//		console.log('foobar');
//	});

	$('#graphs').bind('pageTransitionStart', function(e, info){
		if($('#graphs img').length == 0) {
			$.ajax({
				url:"../json-rpc.php",
				data: '{"id":"23342423","method":"generateGraphUrls","params":{"hash":"' + $('input[name=h]')[0].value + '"}}',
				type: 'POST',
				dataType: 'json',
				contentType: 'application/json',
				success:
			        function(data){
						if(data.error != null || !data.result) {
							return;
						}
						
						if($('#differenceGraph').length == 0)
							$('#graphtoolbar').after('<img style="width: 100%" id="differenceGraph" src="' + data.result.difference + '">');

						if($('#presenceGraph').length == 0)
							$('#graphtoolbar').after('<img style="width: 100%" id="presenceGraph" src="' + data.result.presence + '">');
							
						console.log(data.result);
				}
		    });
		}
	});

});