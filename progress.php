<DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="jquery-ui.min.css">
<link rel="stylesheet" href="progress.css">
<script src="jquery-3.5.1.min.js"></script>
<script src="jquery-ui.min.js"></script> 
<script type="text/javascript">
var subs = <?php 	
	$myFile = "script.txt";
	$lines = file($myFile);
	echo json_encode($lines);;
?>;
var subl = subs.length;
var CisFor="";
var indxToken = 0;
var loc = {act:0,scene:0,page:0,line:0};
var sLine = 0;
var lMax=0;
var SFXtoken=null;
var tThen=0;
var scene=0;
var speed=80;
var sMax=0;
var mod=0;
var sceneFlag = false;
var interActflag = false;
var cap="";
var SFXarray = new Array();

var setInteract = function(){
	interActflag=true;
}
function toHMS(x){
	var tDelta = {s:0,m:0,h:0}
	var delta = Math.abs(x) / 1000;
	tDelta.h = Math.floor(delta / 3600) % 24;
	delta -= tDelta.h * 3600;
	tDelta.m = Math.floor(delta / 60) % 60;
	delta -= tDelta.m * 60;
	if(tDelta.m<10)tDelta.m = "0"+ tDelta.m;
	tDelta.s = Math.floor(delta % 60);
	if(tDelta.s<10){
		tDelta.s = "0"+tDelta.s;
	}else tDelta.s = tDelta.s;
	return(tDelta);
}
function timeString(x){
	return(x.h+":"+x.m+":"+x.s);
}
function restartToggle(tID, audID){
	console.log(tID);
	if($(tID).hasClass("disable"))
		$(tID).removeClass("disable");
	else if(!$(tID).hasClass("disable") && $(audID).get(0).currentTime==SFXarray[SFXtoken].startT)
		$(tID).addClass("disable");
}	
		
function pToggle(tID){
	console.log(tID);
	if($(tID).hasClass("playAud")){
		$(tID).html("pause");
		$(tID).removeClass("playAud");
		$(tID).addClass("pauseAud");
	}else{
		$(tID).html("play_arrow");
		$(tID).removeClass("pauseAud");
		$(tID).addClass("playAud");
	}
}
function togglePlay(){
	if(mod>0 && loc.line<lMax){
		$(".pause").html("play_arrow");
		$(".pause").addClass("play");
		$(".play").removeClass("pause");
		$("#preS").removeClass("disAble");
		$("#nexS").removeClass("disAble");
		$("#preB").removeClass("disAble");
		$("#next").removeClass("disAble");
		$( "#slowUp" ).addClass("disAble");
		$( "#speedUp" ).addClass("disAble");
		$( "#speed" ).addClass("greyOut");
		mod = 0;
		document.cookie="mode="+mod+"; path=/";
		console.log("mode: manual");
	}else if(loc.line<lMax){
		$(".play").html("pause");
		$(".play").addClass("pause");
		$(".pause").removeClass("play");
		$("#preS").addClass("disAble");
		$("#nexS").addClass("disAble");
		$("#preB").addClass("disAble");
		$("#next").addClass("disAble");
		$( "#slowUp" ).removeClass("disAble");
		$( "#speedUp" ).removeClass("disAble");
		$( "#speed" ).removeClass("greyOut");
		mod = cap.length;	//auto
		document.cookie="mode="+mod+"; path=/";
		console.log("mode: auto");
	}
}
function setPlayState(){
	if(mod==0 && $("#pIcon").hasClass("pause")){
		$(".pause").html("play_arrow");
		$(".pause").addClass("play");
		$(".play").removeClass("pause");
		$("#preS").removeClass("disAble");
		$("#nexS").removeClass("disAble");
		$("#preB").removeClass("disAble");
		$("#next").removeClass("disAble");
		$( "#slowUp" ).addClass("disAble");
		$( "#speedUp" ).addClass("disAble");
		$( "#speed" ).addClass("greyOut");
	}else if($("#pIcon").hasClass("play")){
		$(".play").html("pause");
		$(".play").addClass("pause");
		$(".pause").removeClass("play");
		$(".play").removeClass("pause");
		$("#preS").addClass("disAble");
		$("#nexS").addClass("disAble");
		$("#preB").addClass("disAble");
		$("#next").addClass("disAble");
		$( "#slowUp" ).removeClass("disAble");
		$( "#speedUp" ).removeClass("disAble");
		$( "#speed" ).removeClass("greyOut");
	}
}
function getRGB(RGBstring){
	re = /\((.*?)\)/
	tag = RGBstring.match(re);
	if(tag != null)
	{	
		RGBarray = tag[1].split(", ");
		if(RGBarray.length>3)alph=parseInt(RGBarray[3]);
		else alph = 1;
		RGBobj = {
				red:parseInt(RGBarray[0]),
				green:parseInt(RGBarray[1]),
				blue:parseInt(RGBarray[2]),
				alpha:alph
		};
		return RGBobj;
	}else return false;
}
function tagScan() {
   var sfx = {
	url:"",
	id:"",
	type:"",
	fadeUp:0,
	startT:0,
	duration:0,
	delay:0,
	fadeDown:0,
	volume:1,
	loop:false,
	line:0
   };
   for(k=0;k<subl;k++){
   	re = /<(.*?)>/g;			//FX tags use pointy brackets
   	tag = subs[k].match(re);
	if(tag != null)
	{
		for(l=0;l<tag.length;l++){
			tagX = tag[l].substring(1, tag[l].length-1).split(" ");
			if(tagX[0] == "SFX")
			{					
				for(j=1;j<tagX.length;j++)
				{
					tagY = tagX[j].split("=");
					if(tagY != tagX[j])
					{
						if(tagY[0] == "url")
						{
							sfx.url=tagY[1];
							fileN = sfx.url.split("/")
							if(fileN != sfx.url)
								fileN = fileN[1];
							sfx.type =fileN.split(".")[1];
							sfx.id = fileN.split(".")[0];
							if(sfx.type == "mp3")sfx.type = "mpeg";
						}

						else if(tagY[0] == "fadeUp")		
						{
							sfx.fadeUp=parseInt(tagY[1]);
						}
						else if(tagY[0] == "fadeDown")
						{
							sfx.fadeDown=parseInt(tagY[1]);
						}
						else if(tagY[0] == "loop")
						{
							sfx.loop = tagY[1] === "true";
						}
						else if(tagY[0] == "startT")
						{
							sfx.startT = parseInt(tagY[1]);
						}
						else if(tagY[0] == "duration")
						{
							sfx.duration = parseInt(tagY[1]);
						}
						else if(tagY[0] == "delay")
						{
							sfx.delay = parseInt(tagY[1]);
						}
						else if(tagY[0] == "volume")
						{
							sfx.volume = parseInt(tagY[1])/100;
						}
					}
					else alert("Improperly formatted tag in line " + k);
				}
				if(sfx.url.length==0)
				{
				console.log("Missing url in SFX tag in line " + k);
				}
				else
				{
					sfx.line = k;
					altText = "Your browser does not support the audio tag."
					len=SFXarray.length;
					myHTML="<audio class='audioTrack' id='"+sfx.id+"' src='"+sfx.url+"' type='audio/" + sfx.type + "'>" + altText + "</audio>";
					$("#audioLib").append(myHTML);
					myHTML="<div class='wrapper'><input id='collapsible"+len+"' class='toggle' type='checkbox'><label for='collapsible"+len+"' id='audio"+sfx.id+"' class='lbl-toggle'>"+sfx.id+"</label><div class='collapsible-content'><div class='content-inner'><button id='"+len+"reStart' class='round reStarter disable'><i class='material-icons previous'>skip_previous</I></button><button id='"+len+"Play' class='round audPlay'><i id='pTog"+len+"' class='material-icons  playAud'>play_arrow</i></button><div class='AudProgress' id='myProg"+len+"'><div class='progress-label'>Press play to start...</div></div><button id='"+len+"mute' class='round mute'><i class='material-icons muter'>volume_off</I></button><button id='"+len+"Prefs' class='round audPrefs'><i class='material-icons settings'>settings</I></button></div></div></div>"
					$("#audioWrapper").append(myHTML);
					setTimeout(function(pToken){
                                          myProg = "#myProg" + pToken
    					  $(myProg).progressbar({
      						value: false,
      						change: function() {
						   cur = Math.floor( $(myProg).progressbar( "value" ))
						   dur = $(myProg).progressbar( "option", "max");
						   myLabel = cur + "/" + dur;
        					   $(myProg + "> .progress-label").text(myLabel);
          				        },
      					        complete: function() {
        				           $(myProg + "> .progress-label").text( "Complete!" );
      					        }
    					  });
  					},20,len);
					SFXarray.push(sfx);
				}
				sfx = {
					url:"",
					id:"",
					type:"",
					fadeUp:0,
					startT:0,
					duration:0,
					delay:0,
					fadeDown:0,
					volume:1,
					loop:false,
					line:0,
  				};
 			}
				
		}
	}
   }
}
function tracksetUp(myID,j){
	console.log("tracksetUp");
	$(myID).get(0).play();
	dur = Math.floor($(myID).get(0).duration);
	$(myID).get(0).currentTime = SFXarray[j].startT;
	setTimeout(function($start, $end){
		$( "#myProg"+j ).progressbar( "option", "max", $end);
		$( "#myProg"+j ).progressbar( "option", "value", $start );
	},20,SFXarray[j].startT,dur);
	if(SFXarray[j].loop && !SFXarray[j].duration){
		$(myID).get(0).addEventListener("ended", 
			function(){
				$(myID).get(0).play();
				$(myID).get(0).currentTime = SFXarray[j].startT;
			});
		}else if(SFXarray[j].duration){
			setTimeout(function(){
				console.log("bing");
				stopAudio();
		},SFXarray[j].duration*1000);
	}
	if(SFXarray[j].fadeUp){
		$(myID).get(0).volume=0;
		fade=SFXarray[j].fadeUp*1000;
		console.log("xvol:"+SFXarray[j].volume);
		fader(j,SFXarray[j].volume,fade);
	}
}
function soundTool(){
	flag = false;
	tempToken = null;
	for(j=0;j<SFXarray.length;j++)
	{
		if(SFXarray[j].line == loc.line-1){
			myID = "#" + SFXarray[j].id;
			if(interActflag){
				if(SFXarray[j].delay){
					setTimeout(function(sToken){
						tracksetUp(myID,sToken);
					}, SFXarray[j].delay*1000, j);
				}else {
					tracksetUp(myID,j);
				}
			}else setTimeout(function (){
				if(indxToken == 0 && !$("#footer").hasClass("footUp")){
					$("#footer").addClass('footUp');
				}else if(!$("#footer").hasClass("footUp")){
					$('.cd-popup').addClass('is-visible');
					$("#footer").removeClass('footUp');
				}
			},5);
			SFXtoken = tempToken = j;
			j=SFXarray.length;
			if(SFXtoken!=null){
				myID="#collapsible"+SFXtoken;
				$(myID).prop('checked', true);
			}		
		}else if(SFXarray[j].line >= loc.line){
			tempToken=j;
			j=SFXarray.length;
		}
	}
	labelGradient(tempToken);
	if(interActflag){
		restartToggle("#"+SFXtoken+"reStart",myID);			togID="#pTog"+SFXtoken;
		pToggle(togID);	
	}		
}
function labelGradient(myToken){
	redBG = {
		red:233,
		green:116,
		blue:81
	};
	blueBG = {
		red:24,
		green:90,
		blue:157
	}
	thisBG = {
		red: 0, 
		green: 0, 
		blue: 0
	}
	dCol = {
		red: 95, 
		green: 158, 
		blue: 160
	}
	for(s=0;s<SFXarray.length;s++)
	{
		if(s<myToken && myToken != 0 && myToken < SFXarray.length-1){
			thisBG.red = Math.floor(dCol.red+(redBG.red - dCol.red)/myToken*(1+myToken -s));
			thisBG.green = Math.floor(dCol.green+(redBG.green - dCol.green)/myToken*(1+myToken -s));
			thisBG.blue = Math.floor(dCol.blue+(redBG.blue - dCol.blue)/myToken*(1+myToken -s));
		}else if (k>myToken && myToken != 0 && myToken != null && myToken < SFXarray.length-1){
			thisBG.red = Math.floor(dCol.red+(blueBG.red - dCol.red)/s*(s-myToken));
			thisBG.blue = Math.floor(dCol.blue+(blueBG.blue - dCol.blue)/s*(s-myToken));
			thisBG.green = Math.floor(dCol.green+(blueBG.green - dCol.green)/s*(s-myToken));
		}else if(myToken == 0){
			thisBG.red = Math.floor(dCol.red+(blueBG.red - dCol.red)/SFXarray.length*s);
			thisBG.green = Math.floor(dCol.green+(blueBG.green - dCol.green)/SFXarray.length*s);
			thisBG.blue = Math.floor(dCol.blue+(blueBG.blue - dCol.blue)/SFXarray.length*s);
		}else if(myToken >= SFXarray.length-1 || myToken == null){
			thisBG.red = Math.floor(dCol.red+(redBG.red - dCol.red)/SFXarray.length*(SFXarray.length-s));
			thisBG.green = Math.floor(dCol.green+(redBG.green - dCol.green)/SFXarray.length*(SFXarray.length-s));
			thisBG.blue = Math.floor(dCol.blue+(redBG.blue - dCol.blue)/SFXarray.length*(SFXarray.length-s));
		}else{	
			thisBG = dCol;
		}
		$("#audio"+SFXarray[s].id).css("background-color","rgb("+thisBG.red+","+thisBG.green+","+thisBG.blue+")");
	}
}
function stopAudio (){
	if(SFXtoken != null){
		myID = "#" + SFXarray[SFXtoken].id;
		if(SFXarray[SFXtoken].fadeDown){
			fade=SFXarray[SFXtoken].fadeDown*1000;
			console.log("zvol:0");
			fader(SFXtoken,0,fade);
		}else {
			$(myID).get(0).currentTime = 0;
			$(myID).get(0).pause();
		}
		myProg = "#myProg"+SFXtoken;
		if($(myProg).progressbar( "instance")){
			$(myProg).progressbar( "option", "value", false);
			$(myProg+ "> .progress-label").text("Press play to start...")
			togID="#pTog"+SFXtoken;
			pToggle(togID);

		}
		$("#"+SFXtoken+"reStart").addClass("disable");
		checkID="#collapsible"+SFXtoken;
		$(checkID).prop('checked', false);
	}
}

function fader (j,vol,dur){
	console.log("voly:"+vol);
	myID = "#" + SFXarray[j].id;
	$(myID).animate({volume: vol},{
		duration: dur,
		complete: function (){
			if($(this).get(0).volume==0){
				$(this).get(0).volume = SFXarray[SFXtoken].volume;
				$(this).get(0).pause();
			};
			console.log("vol:"+$(this).get(0).volume);
		}
	});
}
function soundCheck(){
	if(SFXtoken!=null && interActflag){
		myID = "#" + SFXarray[SFXtoken].id;
		console.log("Paused:"+$(myID).get(0).paused);
		if((newBGI() || sceneFlag) && !$(myID).get(0).paused){
			stopAudio();
			if(sceneFlag)sceneFlag=false;
		}
	}
	soundTool();
}
function newBGI(){
	myTxt = $("#subText").text();
	len = myTxt.length;
	if(len<1 && SFXarray[SFXtoken].line != loc.line)
		return true;
	else return false;
}
$(document).ready(function() {
	var d=new Date();
	var reStart = d.getTime();
	var progress = 0;
	$( "#volSlider" ).slider({
  		min: 0,
		max: 1,
		step: 0.01,
		slide: function( event, ui ) 
		{
			myID = "#" + SFXarray[t].id;
			$(myID).get(0).volume = $("#volSlider").slider( "option", "value");
		}
	});
	wid = $("#myProgress").width();
	$("#myBar").css("background-size",wid+"px");
	tagScan();
	$( window ).resize(function() {
		wid = $("#myProgress").width();
		$("#myBar").css("background-size",wid+"px");
  	});
	$('body').click(setInteract);
	$(window).scroll(setInteract);
	$(window).on("beforeunload", function() { 
		document.cookie="progToken=0; path=/";
		document.cookie="indxToken=0; path=/";
    	});
	document.onkeydown = function(evt) {
   	   evt = evt || window.event;
	   interActflag = true;
	   if($("input:focus").length<=0){
    		for(k=0;k<sMax;k++)
    		{
			if ((evt.keyCode == k+48 || evt.keyCode == k+96) && mod==0){	
				if(k==scene){
					sceneFlag=true;
					document.cookie = "sameSc = true; path=/";
				}else{
					scene = k;
					sceneFlag=true;
					document.cookie ="sCount ="+scene+"; path=/";
					document.cookie = "sameSc=false; path=/";
				}
				k=sMax;
			}
    		}
	    }
    	    if (evt.keyCode == 37) {		//left arrow
		evt.preventDefault();
        	if(mod>0){
			if(speed>25){		//decrease speed
				speed = speed - 25;
			}else speed = 0;
				document.cookie = "speed= "+speed+"; path=/";
		}else if(loc.line>=0){
			loc.line--;
			stopAudio()
			document.cookie = "Line=" +loc.line+"; path=/";
		}
    	    }else if(evt.keyCode == 38) {	//up arrow
		evt.preventDefault();
		if(scene>0 && mod==0){							scene--;
			sceneFlag=true;
			document.cookie= "sCount=" +scene+"; path=/";
			document.cookie= "Line=" +loc.line+"; path=/";
		}
   	    }else if(evt.keyCode == 77) {
		console.log(document.cookie);
    	    }else if(evt.keyCode == 39) {	//right arrow
		evt.preventDefault();
		if(mod>0){
			speed = speed + 25;
				document.cookie ="speed=" +speed+"; path=/"; 	//increase speed
		}else if(loc.line<lMax){
			loc.line++;
				document.cookie= "Line=" + loc.line + "; path=/";
		}				
   	   }else if(evt.keyCode == 40 && mod==0) {//down arrow
		evt.preventDefault();
		if(scene<sMax-1){	//go to next scene
			scene++;
			sceneFlag=true;
			document.cookie = "sCount=" +scene+"; path=/";
			document.cookie= "Line=" + loc.line + "; path=/";
		}
   	   }else if(evt.keyCode == 32){	//change mode with space key
       		evt.preventDefault();
		togglePlay();
    	   }
	};
        $('.numText').keypress(function(e) {
    		var a = [];
    		var k = e.which;
		for (i = 48; i < 58; i++)
        		a.push(i);
    		if (!(a.indexOf(k)>=0))
       			e.preventDefault();
	});
	$('.limitTen').bind('input propertychange', function() {
		if(parseInt(this.value)>10){
			this.value=10;
		}
	});
	$('#frmStart').bind('input propertychange', function() {
		myID="#" + SFXarray[t].id;
		if(this.value>$(myID).get(0).duration){
			this.value=$(myID).get(0).duration.toFixed(2)-1;
		}
		if(parseInt($('#frmEnd').val())>=$(myID).get(0).duration - this.value){
			endT = $(myID).get(0).duration.toFixed(2);
			$('#frmEnd').val(endT - this.value);
		}
	});
	$('#frmEnd').bind('input propertychange', function() {
		myID="#" + SFXarray[t].id;
		if(this.value>$(myID).get(0).duration){
			this.value=$(myID).get(0).duration.toFixed(2);
		}
		if(parseInt($('#frmStart').val())>=$(myID).get(0).duration - this.value){
			endT = $(myID).get(0).duration.toFixed(2);
			$('#frmStart').val(endT - this.value);
		}
	});
	$('.cd-popup').on('click', function(event){
		interActflag = true;
		if( $(event.target).is('.cd-popup-close') 
		|| $(event.target).is('.cd-popup') || $(event.target).is('#bCancel')) {
			event.preventDefault();
			$(this).removeClass('is-visible');
		}else if($(event.target).is('#bConfirm')){
			soundTool();
			$(this).removeClass('is-visible');
		}
	});
	$( "#pButt" ).click(function() {
		togglePlay();
  	});
	$( ".reStarter" ).click(function() {
		clikToken=this.id.charAt(0);
		myID = "#" + SFXarray[clikToken].id;
		if($(myID).get(0).currentTime>SFXarray[clikToken].startT && !$(this).hasClass("disabled")){
			myProg = "#myProg"+clikToken;
			$(myProg).progressbar( "option", "value", SFXarray[clikToken].startT);
			$(myID).get(0).currentTime = SFXarray[clikToken].startT;
			if($(myID).get(0).paused)
				restartToggle("#"+this.id,myID);
		}
	});
	$( ".mute" ).click(function() {
		clikToken=this.id.charAt(0);
		myID = "#" + SFXarray[clikToken].id;
		if($("i",this).html()=="volume_off"){
			$(myID).stop();
			$(myID).get(0).volume=0;
			$("i",this).html("volume_up")
		}else{
			$(myID).stop();
			$(myID).get(0).volume=SFXarray[clikToken].volume;
			$("i",this).html("volume_off");
		}	
	});
	$( ".audplay" ).click(function() {
		clickID="#pTog"+this.id.charAt(0);
		pToggle(clickID);
		if($(".pauseAud", this).length>0){
			pTogID="#pTog"+SFXtoken;
			if(pTogID!=clickID){
				pToggle(pTogID);
				myID = "#" + SFXarray[SFXtoken].id;
				$(myID).get(0).pause();
			}
			SFXtoken = this.id.charAt(0);
			myID = "#" + SFXarray[SFXtoken].id;
			restartToggle("#"+this.id.charAt(0)+"reStart",myID);
			if($(myID).get(0).currentTime>0)
				$(myID).get(0).play();
			else tracksetUp(myID,SFXtoken);
		}else{
			myID = "#" + SFXarray[SFXtoken].id;
			$(myID).get(0).pause();
		}
  	});
	$( "#preB" ).click(function() {							 
		console.log("previous");
		if(scene>0 && mod==0){
			scene--;
			sceneFlag=true;	
			document.cookie="sCount="+scene+"; path=/";
		}
	});
	$( "#nexS" ).click(function() {							 
		console.log("next slide");
		if(mod==0 && loc.line<lMax){
			loc.line++;
			document.cookie="Line="+loc.line+"; path=/";
		}
	});
	$( "#preS" ).click(function() {							
		console.log("previous slide");
		if(loc.line>=0 && mod==0){
			loc.line--;
			stopAudio ();
			document.cookie="Line="+loc.line+"; path=/";
		} 
	});
	$( "#next" ).click(function() {							
		if(scene<sMax-1 && mod==0){	//go to next scene
			scene++;
			console.log(scene);
			sceneFlag=true;
			document.cookie="sCount="+scene+"; path=/";
		} 
	});
	$( "#slowUp" ).click(function() {						
		if(mod>0){
			if(speed>5){	//decrease speed
				speed=speed-5;
				console.log("New Speed-");
			}else speed=1;
			document.cookie="speed="+speed+"; path=/";
		}
	});
	$( "#slowUp" ).click(function() {						
		if(mod>0){
			if(speed>5){	//decrease speed
				speed=speed-5;
				console.log("New Speed-");
			}else speed=1;
			document.cookie="speed="+speed+"; path=/";
		}
	});
	$( ".audprefs" ).click(function() {
		t = this.id.charAt(0);
		$("h2",".slideOut").html("Settings for: "+ SFXarray[t].url);
		$("h2",".slideOut").id = t + "Dialogue"
		$("#frmStart").val(SFXarray[t].startT);
		myID = "#" + SFXarray[t].id;
		if(SFXarray[t].duration){
			endT=SFXarray[t].startT + SFXarray[t].duration;
		}else {
			endT = $(myID).get(0).duration - $("#frmStart").val();
			endT = endT.toFixed(2);
		}
		$("#frmEnd").val(endT);
		$("#frmDelay").val(SFXarray[t].delay);
		$("#frmFadeOut").val(SFXarray[t].fadeDown);
		$("#frmFadeIn").val(SFXarray[t].fadeUp);
		$("#frmLoop").prop('checked', SFXarray[t].loop);
		$( "#volSlider" ).slider( "option", "value", SFXarray[t].volume);
		$(".slideOut").addClass("slid");	
	});
	$( "#savePrefs" ).click(function() {
		myID = "#" + SFXarray[t].id;
		SFXarray[t].startT=parseInt($("#frmStart").val());
		dur = parseFloat($("#frmEnd").val()) + SFXarray[t].startT;
		if(parseFloat(dur).toFixed(2) >= $(myID).get(0).duration.toFixed(2)) dur = 0;
		else dur = parseInt($("#frmEnd").val());
		SFXarray[t].duration=dur;
		SFXarray[t].delay=parseInt($("#frmDelay").val());
		SFXarray[t].fadeDown=parseInt($("#frmFadeOut").val());
		SFXarray[t].fadeUp=parseInt($("#frmFadeIn").val());
		SFXarray[t].loop=$("#frmLoop").val();
		SFXarray[t].volume = $("#volSlider").slider( "option", "value");
		$(myID).get(0).volume = SFXarray[t].volume;
		$(".slideOut").removeClass("slid");	
	});
	$( "#cancelPrefs" ).click(function() {
		myID = "#" + SFXarray[t].id;
		$(myID).get(0).volume = SFXarray[t].volume;
		$(".slideOut").removeClass("slid");	
	});
	$("#footerYes").click(function() {
		interActflag=true;
		indxToken = 3
		soundTool();
		window.open("index.php");
	});
	var timeR = setInterval(function(){
		d=new Date();
		document.cookie="progToken=1; path=/";
		var rElapse = d.getTime()-reStart;
		var elapsed = d.getTime()-tThen;
		if(document.cookie!=CisFor){
			console.log(CisFor);
			CisFor=document.cookie;
			var cookieArr = CisFor.split(";");
			for(k = 0; k < cookieArr.length; k++) {
        			var cookiePair = cookieArr[k].split("=");
        			if(lMax==0 && cookiePair[0].trim() == "lMax") {
					lMax=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "Line") {
					newLine = parseInt(cookiePair[1].trim());
					if(newLine!=loc.line){
						loc.line=newLine;
						if(sLine == 0 || sLine - loc.line >= 0){
							sLine=loc.line;
						}
					}
				}else if(cookiePair[0].trim() == "Act") {
					loc.act=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "Scene") {
					loc.scene=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "Page") {
					loc.page=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "sTime") {
					tThen=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "sCount") 
				{
					scene=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "sMax") {
					sMax=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "speed") {
					speed=parseInt(cookiePair[1].trim());
				}else if(cookiePair[0].trim() == "indxToken"){
					indxToken = parseInt(cookiePair[1].trim());
					$(".footUp").removeClass("footUp");
				}else if(cookiePair[0].trim() == "mode") {
					newMod=parseInt(cookiePair[1].trim());
					if(newMod!=mod){
						console.log("mod:"+mod);
						mod=newMod;
						setPlayState();
					}
				}else if(cookiePair[0].trim() == "subText" && cap!=cookiePair[1].trim()) 
				{	
					cap=cookiePair[1].trim();
					$("#subText").html(cap);
					soundCheck();
				}
			}
		}	
		var perc = loc.line/lMax*100;
        	progress = perc - sLine/lMax*100;
		var progX = toHMS((100-perc)/progress*rElapse);
		var elapX = toHMS(elapsed);
		$("#myBar").width(perc+"%");
		$("#stats").html("<span id='progBloc'>Line " + loc.line + " of " + lMax + " (" + perc.toFixed(2)+"%)</span><span id='pBloc'>Page " + loc.page + " (Act " +loc.act+ ", Scene " + loc.scene + ")</span><div id='eTime'>Elapsed time: " + timeString(elapX) + " <span id='rTime'>Estimated time remaining: " + timeString(progX)+"</span></div>");
		$("#speed").html("Speed: " + speed);
		if(SFXtoken!=null){
			myProg = "#myProg"+SFXtoken;
			myID = "#" + SFXarray[SFXtoken].id;
			if($(myProg).progressbar( "instance" ) && $(myID).get(0).currentTime>0 && !$(myID).get(0).paused){
				currT = $(myID).get(0).currentTime;
				$(myProg).progressbar( "option", "value", currT);
			}
		}
		if(indxToken == 0 && !$("#footer").hasClass("footUp")){
			$("#footer").addClass("footUp");
		}
	}, 5);
        setPlayState();
});
</script>
</head>
<body>
<div id="bodywrapper">
<h1>Sub<sup>script</sup> controller</h1>
<div class="subs" id="subText"></div>
<table class="controls" id="cUnit1">
<tr id="controlBlock">
<td id="prevSc" class="roundButt">
<button id="preB" class="round">
<i class="material-icons  previous">skip_previous</I>
</button>
</td>
<td id="playB" class="roundButt">
<button id="pButt" class="round">
<i id="pIcon" class="material-icons  pause">pause</i>
</button>
</td>
<td id="nextSc" class="roundButt">
<button id="next" class="round">
<i class="material-icons  next">skip_next</i>
</button>
</td>
<td id="empty"></td>
<td id="slower" class="roundButt">
<button id="slowUp" class="round">
<i class="material-icons  previous">navigate_before</I>
</button>
</td>
<td id="speedTxt">
<span id="speed"></span>
</td>
<td id="nxtSc" class="roundButt">
<button id="speedUp" class="round">
<i class="material-icons  previous">navigate_next</I>
</button>
</td>
</tr>
</table>
<table id="cUnit0">
<tr>
<td class="roundButt" id="pSlide">
<button id="preS" class="round">
<i class="material-icons  previous">navigate_before</I>
</button>
</td>
<td id="pBar">
<div id="myProgress">
<div id="myBar"></div>
</div>
</td>
<td class="roundButt" id="nSlide">
<button id="nexS" class="round">
<i class="material-icons  previous">navigate_next</I>
</button>
</td>
</tr>
<tr id="statsBloc">
<td colspan="3" id="stats">0%</td>
</tr>
</table>
<h2>Sound Cues</h2>
<div class="Audwrapper" id="audioWrapper"></div>
<div id="audioLib"></div>
</div>
<div class="slideOut">
<h2>Track Preferences</h2>
<table id="audStats">
<tr>
<td>
<label for="frmStart">Start Time:</label>
<input type="text" class="numText" id="frmStart" name="frmStart">
</td>
<td>
<label for="frmEnd">Duration:</label>
<input type="text" class="numText" id="frmEnd" name="frmEnd">
</td>
<td>
<label for="frmDelay">Delay:</label>
<input type="text" class="numText limitTen" id="frmDelay" name="frmDelay">
</td>
</tr>
<tr>
<td>
<label for="frmFadeIn">Fade In:</label>
<input type="text" class="numText limitTen" id="frmFadeIn" name="frmFadeIn">
</td>
<td>
<label for="frmFadeOut">Fade Out:</label>
<input type="text" class="numText limitTen" id="frmFadeOut" name="frmFadeOut">
</td>
<td>
</td>
</tr>
<tr>
<td>	
Loop:<div class="onoffswitch">
    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="frmLoop" tabindex="0">
    <label class="onoffswitch-label" for="frmLoop">
        <span class="onoffswitch-inner"></span>
        <span class="onoffswitch-switch"></span>
    </label>
</div>
</td>
<td colspan="2">
Volume:<div  id="volSlide"><div id="volSlider" class="volume"></div><div>
</td>
</tr>
</table>
<p style="text-align:right"><button class="roundRect" id="savePrefs">Save</button><button class="roundRect" id="cancelPrefs">Cancel</button></p>	
</div>
<div class="cd-popup" role="alert">
	<div class="cd-popup-container">
		<p>You have a music cue pending.  Do you want to play it?</p>
		<ul class="cd-buttons">
			<li><a id="Bconfirm" href="#0">Yes</a></li>
			<li><a id="Bcancel" href="#0">No</a></li>
		</ul>
		<a href="#0" class="cd-popup-close img-replace">Close</a>
	</div> <!-- cd-popup-container -->
</div>
<div id="footer"><p style="text-align:right">Open subtitles? <button class="roundRect" id="footerYes">Yes</button></p></div>			
</body>
</html>