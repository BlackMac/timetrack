<html> 
<head> 
<title>Countdown</title> 
<script language="Javascript"> 
<!--
<?php
echo "var CountdownJahr = ".date("Y").";";
echo "var CountdownMonat = ".date("m").";";
echo "var CountdownTag = ".date("d").";";
echo "var CountdownStunde = ".$_GET['std'].";";
echo "var CountdownMinute = ".$_GET['min'].";";
echo "var CountdownSekunde = ".$_GET['sec'].";";
?>
var noalert = 0;
function CountdownAnzeigen()
{
	var Jetzt = new Date();
	var Countdown = new Date(CountdownJahr, CountdownMonat-1, CountdownTag, CountdownStunde, CountdownMinute, CountdownSekunde);
	var MillisekundenBisCountdown = Countdown.getTime()-Jetzt.getTime();
	var Rest = Math.floor(MillisekundenBisCountdown/1000);
	var CountdownTXT = ""; 
       	var Stunden = Math.floor(Rest/3600);
        Rest = Rest-Stunden*3600;
        if(Stunden > 9) { CountdownTXT += Stunden + ":"; }
        if(Stunden < 10) { CountdownTXT += "0" + Stunden + ":"; }
        var Minuten = Math.floor(Rest/60);
        Rest = Rest-Minuten*60;
        if(Minuten > 9) { CountdownTXT += Minuten + ":"; }
        if(Minuten < 10) { CountdownTXT += "0" + Minuten + ":"; }
	if(Rest > 9) { CountdownTXT += Rest; }
	if(Rest < 10) { CountdownTXT += "0" + Rest; }
	document.getElementById('Countdown').innerHTML = CountdownTXT;
	document.title = CountdownTXT;
	window.title = CountdownTXT;
	if(CountdownTXT=="00:00:00") { 
		alert("Feierabend");
		self.close();
	} else {
		window.setTimeout("CountdownAnzeigen()", 100);
	}
}
//-->
</script> 
</head> 
<body onLoad="CountdownAnzeigen();" bgcolor="#000000" text="white"> 
<center> 
<div id="Countdown" style="font-size:60px;"></div> 
</center> 
</body> 
</html> 