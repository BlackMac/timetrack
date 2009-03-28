var wait_div=new Element('div', {'class':'wait_div','text':'Übertragen...'});

$$('.expressform').addEvent('submit', function(evnt) {
	wait_div.injectTop(document.body);
	evnt.stop();
	var ajaxparams = {h: evnt.target.getElement('input[name=h]').get('value') , d: evnt.target.getElement('button[name=d]').get('value') };
	var ajax = new Request.HTML({url:'../log.php', onComplete: function() { location.reload(); } }).get(ajaxparams);
}.bindWithEvent());

document.addEventListener("touchmove", function(e){e.preventDefault()}, false);

/* preload spinner */
new Element('img', {'src':'../img/mobile/wait.gif'});
//var spinnercache=new Asset.image('../img/mobile/wait.gif');