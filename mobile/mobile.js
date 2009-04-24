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

var wait_div=new Element('div', {'class':'wait_div','text':'Übertragen...'});

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

window.addEvent('unload', function() {
	wait_div.injectTop(document.body);
});

document.addEventListener("touchmove", function(e){e.preventDefault()}, false);

/* preload spinner */
new Element('img', {'src':'../img/mobile/wait.gif'});
//var spinnercache=new Asset.image('../img/mobile/wait.gif');

$('show_stats_link').addEvent('click', function() {
	this.addClass('dontshow');
	showPage('page2', 'left');
	$('show_home_link').removeClass('dontshow');
});

$('show_home_link').addEvent('click', function() {
	this.addClass('dontshow');
	showPage('page1', 'right');
	$('show_stats_link').removeClass('dontshow');
});

var activeEffect;
var newEffect;

function showPage(page, direction) {
	if ($(page).hasClass('active')) return;
	
	if (direction) {
		
		var activeElement=$(page).getParent().getElement('.active');
		
		activeEffect = new Fx.Morph(activeElement, {duration:200});
		newEffect = new Fx.Morph(page, {
			onComplete:function(){
				$(page).getParent().getElements('.active').removeClass('active');
				$(page).addClass('active');
			},
			duration:200
		});

		//height from 10 to 100 and width from 900 to 300
		if (direction=="left") {
			newEffect.set({'left':window.getWidth()});
			$(page).removeClass('dontshow');
			activeEffect.start({
					'left': [0, -1*window.getWidth()],
					'right': [0, window.getWidth()]
			});
			newEffect.start({
					'left': [window.getWidth(), 0],
					'right': [-1*window.getWidth(), 0]
			});
		}
		
		if (direction=="right") {
			newEffect.set({'left':-1*window.getWidth()});
			$(page).removeClass('dontshow');
			activeEffect.start({
					'left': [0, window.getWidth()],
					'right': [0, -1*window.getWidth()]
			});
			newEffect.start({
					'left': [-1*window.getWidth(), 0],
					'right': [window.getWidth(), 0]
			});
		}
		
		$(page).addClass('active');
		return;
	}
	$(page).setStyle('left',0);
	$(page).addClass('active');
}

