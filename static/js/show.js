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
				'value': value
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
						console.log(email);
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
});

