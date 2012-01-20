$('correct_action_link').addEvent('click', function() {
		$('correct_widow').toggleClass('visible');
		$('correction_value').focus();
});

$('changeuserdata_action_link').addEvent('click', function() {
		$('changeuserdata_widow').toggleClass('visible');
		$('changeuserdata_pass').focus();
});

var countdownTimer;
var pageLoad = $time();

function countdownDayDiff() {

	var now = new Date();
	var endofday = new Date().set('Time', day_diff * 1000);
	var earliest_endofday = new Date().set('Time', earliest_diff * 1000);

	if(endofday.getUTCDate() != now.getUTCDate() || laststateIn != "1") {
		$clear(countdownTimer);
		return;
	}

	if($time() - pageLoad > 60 * 60 * 1000) {
		location.href = location.href;
	}
	
	var day_countdown = getTimeDiffSplitted(now, endofday);
	var month_countdown = getTimeDiffSplitted(now, earliest_endofday);

	if(day_countdown.second < 0 || day_countdown.minute < 0 || day_countdown.hour < 0) {
		$('day_diff').removeClass('negative');
		$$('.day_diff_minus').setStyle('display', 'none');
		day_countdown.hour = Math.abs(day_countdown.hour);
		day_countdown.minute = Math.abs(day_countdown.minute);
		day_countdown.second = Math.abs(day_countdown.second);
	}

	$$('.day_diff_hour').set('text', day_countdown.hour);
	$$('.day_diff_minute').set('text', zeroize(day_countdown.minute, 2));
	$$('.day_diff_seconds').set('text', zeroize(day_countdown.second, 2));

	if(month_countdown.second < 0 || month_countdown.minute < 0 || month_countdown.hour < 0) {
		$('month_diff').removeClass('negative');
		$$('.month_diff_minus').setStyle('display', 'none');
		month_countdown.hour = Math.abs(month_countdown.hour);
		month_countdown.minute = Math.abs(month_countdown.minute);
		month_countdown.second = Math.abs(month_countdown.second);
	}
	
	$$('.month_diff_hour').set('text', month_countdown.hour);
	$$('.month_diff_minute').set('text', zeroize(month_countdown.minute, 2));
	$$('.month_diff_seconds').set('text', zeroize(month_countdown.second, 2));

};

function getTimeDiffSplitted(now, countto) {
	var diff_second = now.diff(countto, 'second');
	var diff_hour = (diff_second / 60 / 60).toInt();
	diff_second = diff_second - diff_hour*60*60;
	var diff_minute = (diff_second / 60).toInt();
	diff_second = diff_second - diff_minute*60;

	return {
		hour: diff_hour,
		minute: diff_minute,
		second: diff_second
	};
}

var zeroize = function(what, length){
	return '0'.repeat(length - what.toString().length) + what;
};

window.addEvent('domready', function() {
	new Element('link', {rel:'stylesheet', type: 'text/css', href: 'static/js/SqueezeBox/assets/SqueezeBox.css'}).inject(document.head);
	
	$('previous_months_link').addEvent('click', function (e) {
		e.preventDefault();
		$('additional_months').toggleClass('hidden');
	}.bindWithEvent());
	SqueezeBox.assign($$('a.correctionLink'), {
		classWindow: 'correctionLightbox',
		size: {x: 600, y: 105}
	});

	countdownTimer = countdownDayDiff.periodical(1000);

	var options = {
			
		parse: 'rel',
		classWindow: 'editorLightbox',
		size: {x: 600, y: 290}
	};
	
	SqueezeBox.assign($$('a.editorLink'), options);

	$('dropBoxLink').href = $('dropBoxLink').href + location.search;

	options.size.y = 600;
	
	SqueezeBox.assign($('dropBoxLink'), options);
	
	if(location.search.test('dropbox=1'))
	{
		SqueezeBox.open($('dropBoxLink'), options);
	}

	SqueezeBox.assign($$('a.iframeLink'), {parse:'rel', classWindow: 'editorLightbox'});
	
	$$('span.notificationSetting').addEvent('mouseenter', function(e) {
		var pos = this.getPosition();
		$('options_' + this.id).getElements('li').each(function(el) {
			if(el.get('text').trim() == e.target.get('text').trim()) {
				el.addClass('active');
			} else {
				el.removeClass('active');
			}				
		});
		$('options_' + this.id).removeClass('hidden').setStyles({top: pos.y, left: pos.x});
	});
	
	$$('div.notificationSettingBox').addEvent('mouseleave', function(e) {
		this.addClass('hidden');
	});
	
	$$('li.notificationOption').addEvent('click', function(e) {
		if(e.target.get('tag') != 'li') return;
		var name = e.target.getElement('input').get('name');
		var value = e.target.getElement('input').get('value');
		
		var params = {
			id: $time(),
			method: "updateNotification",
			params: {
				'hash': hash,
				'option': name,
				'value': value,
				'target': ''
			}
		};
		var settingBox = e.target.getParent('div.notificationSettingBox');
		var save = function() {
			new Request.JSON({
				url: 'json-rpc.php',
		        headers: {
		            'Content-Type': 'application/json',
		            'Accept': 'application/json, text/x-json, application/x-javascript'
		        },
				onComplete: function(res) {
		        	if(!res || !res.result || res.result.save != "ok") {
			    		$(settingBox.id.replace('options_', '')).highlight('#880000', '#414E55');
		        		return;
		        	}
		    		$(settingBox.id.replace('options_', '')).set('text', e.target.get('text').trim()).highlight('#008800', '#414E55');
				}
			}).send(JSON.encode(params));
		};
		settingBox.addClass('hidden');
				
		if(name=="how" && ["mail", "iphone"].indexOf(value) !== -1) {
			SqueezeBox.open($('options_notificationHow_email'), {
				classWindow: 'editorLightbox',
				handler: 'clone',
				size: {x: 300, y: 120},
				onUpdate: function() {
					this.content.getElement('input').select();
				},
				onOpen: function() {
					this.content.getElement('div.hidden').removeClass('hidden');
					this.content.getElement('button').addEvent('click', function() {
						var email = this.content.getElement('input').get('value');
						params.params.target = email;
						save();
						this.close();
					}.bind(this));
				}
			});
		} else {
			save();
		}		
	});
	
	$('notificationEnabled').addEvent('change', function(e) {
		var params = {
				id: $time(),
				method: "updateNotification",
				params: {
					'hash': hash,
					'option': 'enabled',
					'value': e.target.get('checked')
				}
			};
			var settingBox = e.target.getParent('div.notificationSettingBox');
			new Request.JSON({
				url: 'json-rpc.php',
		        headers: {
		            'Content-Type': 'application/json',
		            'Accept': 'application/json, text/x-json, application/x-javascript'
		        },
				onComplete: function(res) {
		        	if(!res || !res.result || res.result.save != "ok") {
			    		$('dd_notifications').highlight('#880000', '#414E55');
			    		e.target.set('checked', !e.target.get('checked'));
		        		return;
		        	}
		        	$('dd_notifications').highlight('#008800', '#414E55');
				}
			}).send(JSON.encode(params));		
	});
	
	$$('a.unavail_remove').addEvent('click', function(evnt) {
		var clickedOn = $(evnt.target);
		var cell = clickedOn.getParent("td");
		var date = cell.getElement("input[name=unavail_date]").get('value');
		var params = {
			id: $time(),
			method: "removeDaySubject",
			params: {
				'hash': hash,
				'date': date
			}
		};
		new Request.JSON({
			url: 'json-rpc.php',
	        headers: {
	            'Content-Type': 'application/json',
	            'Accept': 'application/json, text/x-json, application/x-javascript'
	        },
			onComplete: function(res) {
	        	if(!res || !res.result || res.result.save != "ok") {
	        		cell.highlight('#880000', '#dddddd');
	        		return;
	        	}
	        	location.reload();
			}
		}).send(JSON.encode(params));		
		
	});
	
	
	$$('span.unavail').addEvent('click', function(evnt) {
		var clickedOn = $(evnt.target);
		
		var cell = clickedOn.getParent("td");
		var date = cell.getElement("input[name=unavail_date]").get('value');
		var subject = "";
		
		if(clickedOn.hasClass("unavail_illness"))
		{
			subject = "illness";
		}
		else if(clickedOn.hasClass("unavail_vacation"))
		{
			subject = "vacation";
		}
		
		var params = {
			id: $time(),
			method: "changeDaySubject",
			params: {
				'hash': hash,
				'date': date,
				'subject': subject
			}
		};
		new Request.JSON({
			url: 'json-rpc.php',
	        headers: {
	            'Content-Type': 'application/json',
	            'Accept': 'application/json, text/x-json, application/x-javascript'
	        },
			onComplete: function(res) {
	        	if(!res || !res.result || res.result.save != "ok") {
	        		cell.highlight('#880000', '#dddddd');
	        		return;
	        	}
	        	location.reload();
			}
		}).send(JSON.encode(params));		

	});
	
});

