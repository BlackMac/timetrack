$$('.expressform').addEvent('submit', function(evnt) {
	evnt.stop();
	var ajaxparams = {h: evnt.target.getElement('input[name=h]').get('value') , d: evnt.target.getElement('button[name=d]').get('value') };
	var ajax = new Request.HTML({url:'../log.php', onComplete: function() { location.reload(); } }).get(ajaxparams);
}.bindWithEvent());

document.addEventListener("touchmove", function(e){e.preventDefault()}, false);