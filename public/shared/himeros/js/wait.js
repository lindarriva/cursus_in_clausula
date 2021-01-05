var cursoreY = 0;
var nava = (document.layers);
var dom = (document.getElementById);
var iex = (document.all);
if (nava) { 
	cach = document.cache
}else if (dom) {
	cach = document.getElementById("cache").style
}else if (iex) {
	cach = cache.style
}

largeur = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
cach.left = Math.round((largeur/2)) + "px";
cach.visibility = "hidden";

/*
function cacheOn(){
    cach.top = (document.body.scrollTop + document.documentElement.scrollTop + 150) + 'px';
    cach.visibility = "visible";
}
*/

function waitOn(width, top){
	if(width == null){
		width = 100;
	}else{
		width = width/2;
	}
	if(top == null){
		top = 150;
	}
    cach.top = (document.body.scrollTop + document.documentElement.scrollTop + top) + 'px';
    cach.left = Math.round((largeur/2) - width) + "px";
    cach.visibility = "visible";
}
function waitOff(){
    cach.visibility = "hidden";
}
window.onunload = function (e) {
    cacheOff();
}
