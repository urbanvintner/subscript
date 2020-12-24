<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="subs.css">
<script src="jquery-3.5.1.min.js"></script>
<script type="text/javascript">
var subs = <?php 	
	$myFile = "script.txt";
	$lines = file($myFile);
	echo json_encode($lines);;
?>;
var BGI = 0;
var sceneTag = new Array();
var pageTag = new Array();
var scene = 0;
var loc = {act:0,scene:0,page:0,line:0};
var speed = 80;
var progToken = 0;
var subl = subs.length;
var mod = 0;
var CisFor="";
var d = 0;
var skip = false;
var trigA = false;
document.onkeydown = function(evt) {
    if(d == 0){
	d = new Date();
	document.cookie="sTime="+d.getTime()+"; path=/";
    }
    evt = evt || window.event;
    for(k=0;k<sceneTag.length;k++)
    {
	if ((evt.keyCode == k+48 || evt.keyCode == k+96) && mod==0){					loc.line =sceneTag[k];
		scene = k;
		document.cookie="Line="+loc.line+"; path=/";
  		document.cookie="sCount="+scene+"; path=/";
		k=sceneTag.length;
		if(typeof deLay != "undefined"){
			clearTimeout(deLay);
		}
		myLoop();
	}
    }
    if (evt.keyCode == 37) {		//left arrow
	evt.preventDefault();
        if(mod>0){
		if(speed>5){			//decrease speed
			speed = speed - 5;
		}else speed = 1;
		document.cookie="speed="+speed+"; path=/";
	}else if(loc.line>1){
		previousLine();	
		//myLoop();
	}
    }else if(evt.keyCode == 38) {	//up arrow
	evt.preventDefault();
	setScene();
	if(scene>=1 && mod==0){
		console.log("Previous scene: "+loc.line+" " + sceneTag[scene])
		if(loc.line==sceneTag[scene]+1) loc.line=sceneTag[scene-1];
		else loc.line=sceneTag[scene];
		scene--;
		document.cookie="sCount="+scene+"; path=/";
		document.cookie="Line="+loc.line+"; path=/";
		myLoop();
	}
    }else if(evt.keyCode == 39) {	//right arrow
	evt.preventDefault();
	if(mod>0){
		speed = speed + 5;  	//increase speed
		document.cookie="speed="+speed+"; path=/";
	}else myLoop();		//go to next subtitle
    }else if(evt.keyCode == 40 && mod==0) {	//down arrow
	evt.preventDefault();
	setScene()
	if(scene<sceneTag.length-1){	//go to next scene
		console.log("Next scene: "+loc.line+" " + sceneTag[scene])
		scene++;
		loc.line = sceneTag[scene];
		document.cookie="sCount="+scene+"; path=/";
		document.cookie="Line="+loc.line+"; path=/";
		myLoop();
		
	}
    }else if(evt.keyCode == 32){	//change mode with m key
        evt.preventDefault();
	if(mod>0){
		mod = 0;		//manual
		document.cookie="mode="+mod+"; path=/";
		if(typeof deLay != "undefined"){
			clearTimeout(deLay);
		}
		console.log("mode: manual");
	}else {
		disPlay(subs[loc.line]);
		mod = subs[loc.line].length;	//auto
		document.cookie="mode="+mod+"; path=/";
		console.log("mode: auto");
		myLoop();
	}
    }
};
function deleteCookies() { 
	var cookies = document.cookie.split(";");
	for (var i = 0; i < cookies.length; i++) {
        	var cookie = cookies[i];
        	var eqPos = cookie.indexOf("=");
        	var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        	document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    	}
}
function previousLine(){
	if(loc.line<0){
		loc.line=0;
		document.cookie="Line="+loc.line+"; path=/";
	}else loc.line=loc.line-2;
	document.cookie="Line="+loc.line+"; path=/";		
	setScene();
	disPlay(subs[loc.line]);
	if(sceneTag[scene]!=loc.line){
		locX = sceneTag[scene]+1;
		doTheSlide(subs[locX]);
	}
}
function isVisible(element) {
    const $element = $(element);
    return (
        $element.css('display') !== 'none'
        && $element.css('visibility') !== 'hidden'
        && $element.css('opacity') !== 0
    );
}
function anyVisible(element) {
    const $element = $(element);
    return (
        $element.css('display') !== 'none'
        || $element.css('visibility') !== 'hidden'
        || $element.css('opacity') !== 0
    );
}
function setScene(){
   scene=sceneTag.length-1;
   if(loc.line!=0){
   	for(k=1;k<sceneTag.length;k++){
		if(sceneTag[k]>=loc.line){
   			scene = k-1;
			k=sceneTag.length;
		}
	}
   }else scene=0;
   console.log("Set Scene:"+scene + " " +loc.line);
   document.cookie="sCount="+scene+"; path=/";		
}
function setPage(){
   var page=pageTag.length-1;
   for(k=1;k<pageTag.length;k++){
	if(pageTag[k]>loc.line){
   		page = k-1;
		k=pageTag.length;	}
   }
   re = /\<(.*)\>/;
   tag = subs[pageTag[page]].match(re);
   if(tag != null){
	tagX = tag[1].split(" ");
	if(tagX[1].split(":")[0]=="act" && tagX[2].split(":")[0]=="scene" && tagX[3].split(":")[0]=="p"){
		loc.act=tagX[1].split(":")[1];
		loc.scene=tagX[2].split(":")[1];
		loc.page=tagX[3].split(":")[1];
	}else alert("XImproperly formatted tag in line:"+pageTag[page]);
   }
}
function tagScan() {
   for(k=0;k<subl;k++){
   	re = /<(.*?)>/g;			//FX tags use pointy brackets
   	tag = subs[k].match(re);
	if(tag != null)
	{
		for(l=0;l<tag.length;l++){
			tagX = tag[l].substring(1, tag[l].length-1).split(" ");
			if(tagX[0] == "BGI"){				//Background image
			   urlX = tagX[1];				//full url
			   urlY = urlX.split("/")[1].split(".")[0];//get file name
			   if(urlY != null){			//error check
				myTxt = "<div id='"+ urlY + "' class='content'><img src='"+urlX+"'></div>";
				$(".background").append(myTxt);	//populate slideshow
			   }else alert("YImproperly formatted tag in line " + k);	
			}else if(tagX[0] == "LOC"){
			   pageTag.push(k);
			}
		}
	}
	re = /\[(.*)\]/;				//Titles use square brackets
   	tag = subs[k].match(re);
	if(tag != null){
		sceneTag.push(k);			//populate scene array
	}
   }
}
function myLoop() {         //  create a loop function
   document.cookie="Page="+loc.page+"; path=/";
   document.cookie="Scene="+loc.scene+"; path=/";
   document.cookie="Act="+loc.act+"; path=/";
   if(mod>0){	//auto mode			
	deLay = setTimeout(function() { 		//set up timer  
      		if(disPlay(subs[loc.line]) && loc.line <= subl) {//check if finished          
         		myLoop();  			//next loop                      
    			mod = subs[loc.line].length;
			document.cookie="mode="+mod+"; path=/";	
		}
    	}, speed*mod);			//set delay
   }else {			//manual mode		
	disPlay(subs[loc.line]);				//display caption
   }
   setPage();
}
function sceneTagged(xTex){
   re = /\[(.*)\]/;					//look for title tags
   tag = xTex.match(re);
   if(tag != null)return(tag);
   else return(false);
}
function doTheSlide(xTex){
	re = /\<(.*)\>/;				//look for FX tags
   	tag = xTex.match(re);
   	if(tag != null){				// FX tag found
   		type = tag[1].split(" ")[0];
		if(type == "BGI"){			//Background image
			urlX = "#" + tag[1].split(" ")[1].split("/")[1].split(".")[0];
			if(!isVisible(urlX)){	
				$(".content").fadeOut("slow");	//hide visible images	
				$(urlX).fadeIn("slow");		//show current image
			}
		}
	}
}
function disPlay(xTex){
   var bool = true;
   var subtext="";
   tag=sceneTagged(xTex);    
   if(tag){
	$( "#subtitle" ).hide();
	$( "#pgtitle" ).html(tag[1]);			//empty subtitle block
	if(anyVisible(".content")) $(".content").fadeOut("slow", function() {
    		$( "#pgtitle" ).show();			//display title
	});
	subtext = tag[1];
	if(loc.line==0 || trigA || 
	  !(sceneTagged(subs[loc.line-1]) && sceneTagged(subs[loc.line])))
	{
		mod=0;
		bool=false;
		trigA=true;
	}else {
		trigA=false;
		bool=true;
	}
	loc.line++;
	document.cookie="mode="+mod+"; path=/";
	document.cookie="Line="+loc.line+"; path=/";	//set mode to manual
   }else {
   	doTheSlide(xTex);
	$( "#pgtitle" ).hide();		//clear page title
        $( "#subtitle" ).html(xTex);	//populate subtitle block
	if($( "#subtitle" ).text().length>1){
		subtext = xTex;
		$( "#subtitle" ).show();	//display if not empty
	}else $( "#subtitle" ).hide();
	loc.line++;
	document.cookie="Line="+loc.line+"; path=/";
   }
   document.cookie="subText="+subtext+"; path=/";
   return(bool);
}
$(document).ready(function() {
        var idleMouseTimer;
        var forceMouseHide = false;
	var cookieArr = document.cookie.split(";");
	deleteCookies();
	tagScan();
	myLoop(); 
	$(window).on("beforeunload", function() { 
		document.cookie="progToken=0; path=/";
		document.cookie="indxToken=0; path=/";
    	});
	document.cookie="Line=1; path=/";
	document.cookie="lMax="+subs.length+"; path=/";  
        document.cookie="sMax="+sceneTag.length+"; path=/";
	document.cookie="speed="+speed+"; path=/";
        $("body").css('cursor', 'none');
	$("#footerYes").click(function() {
		window.open("progress.php");
	});
	$("#footerNo").click(function() {
		document.cookie="progToken=3; path=/";
	});
        $("#sShow").mousemove(function(ev) {
                if(!forceMouseHide) {
                        $("body").css('cursor', '');
                        clearTimeout(idleMouseTimer);
                        idleMouseTimer = setTimeout(function() {
                                $("body").css('cursor', 'none');

                                forceMouseHide = true;
                                setTimeout(function() {
                                        forceMouseHide = false;
                                }, 200);
                        }, 1000);
                }
        });
	var timeR = setInterval(function(){
		if(document.cookie!=CisFor){
			CisFor=document.cookie;
			var cookieArr = CisFor.split(";");
			var block = false;
			document.cookie="indxToken=1; path=/";
			for(k = 0; k < cookieArr.length; k++) {
        			var cookiePair = cookieArr[k].split("=");
        			if(cookiePair[0].trim() == "Line" && loc.line !=0){
					var newLine = parseInt(cookiePair[1].trim());
					if(newLine != loc.line){
						if(loc.line-newLine==1){
							previousLine();
						}else if(newLine-loc.line==1){ 
							myLoop();
						}else{
							loc.line = newLine;
							disPlay(subs[loc.line]);
						}
						block=true;
					}
				}else if(cookiePair[0].trim() == "sCount"){
				     	var newScene=parseInt(cookiePair[1].trim());
					if(loc.line > 0) 
						var preScene = sceneTagged(subs[loc.line-1]);
					else var preScene = false;
					if(newScene!=scene && !block){
					   if(Math.abs(sceneTag[newScene]-loc.line)>1 
                                          || preScene){											console.log("xNew Scene: " + newScene + " " + preScene);
						if(preScene || newScene>scene ||Math.abs(sceneTag[newScene]-loc.line)>1){
							scene = newScene;
						}else 	document.cookie="sCount="+scene
							+"; path=/";
						loc.line=sceneTag[scene];
						document.cookie="Line="+loc.line+
						"; path=/";
						myLoop();
					   }else {
						console.log("yNew Scene: " + newScene 
						+ " Scene: " + scene);
						scene=newScene;
					   }
					}
					block=false;
				}else if(cookiePair[0].trim() == "progToken"){
					progToken = parseInt(cookiePair[1].trim());
					$(".footUp").removeClass("footUp");
				}else if(cookiePair[0].trim() == "mode") {
					var newMod=parseInt(cookiePair[1].trim());
					if(newMod==0 && mod != newMod){
						if(typeof deLay != "undefined") 								clearTimeout(deLay);
						mod = 0;			
						console.log("newmode: manual");
					}else if(newMod>0 && mod != newMod){
						mod = newMod;
						console.log("newmode: auto");
						myLoop();
					}
				}else if(cookiePair[0].trim() == "speed") {
					var newSpeed = parseInt(cookiePair[1].trim());
					if(newSpeed != speed){
						speed=newSpeed;
						console.log("newspeed");
					}
				}else if(cookiePair[0].trim() == "sameSc") {
					var sameSc = (cookiePair[1].trim() === 'true');
					if(sameSc){
						loc.line=sceneTag[scene];
						document.cookie="sameSc=false; path=/";
						sameSc=false;
						myLoop();
					}
				}
			}
   		}
		if(progToken == 0 && !$("#footer").hasClass("footUp")){
			$("#footer").addClass("footUp");
		}
	}, 5);
        setPage();
	setScene()
});
</script>
</head>
<body>
<div id="sShow" class="background"><div id="pgtitle" class="title"></div><div id="subtitle" class="subs"></div></div><div id="footer"><p style="text-align:right">Open Sub<sup>script</sup> control interface? <button class="roundRect" id="footerYes">Yes</button><button class="roundRect" id="footerNo">No</button></p></div>
</body>
</html>