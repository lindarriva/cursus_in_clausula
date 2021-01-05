
/* gestione del messaggio di loading */
/*
var cursoreY = 0;
var nava = (document.layers);
var dom = (document.getElementById);
var iex = (document.all);
var cach = null;
if (nava) {
    cach = document.cache
}else if (dom) {
    cach = document.getElementById("wait").style
}else if (iex) {
    cach = cache.style
}
largeur = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
cach.left = Math.round((largeur/2)-100) + "px";
cach.visibility = "hidden";
function waitOn(y){
	if(y == null) y = 300;
    cach.top = (document.body.scrollTop + document.documentElement.scrollTop + y) + 'px';
    cach.visibility = "visible";
}
function waitOff(){
    cach.visibility = "hidden";
}
window.onunload = function (e) {
    waitOff();
}
*/
/* *********************************** */

/* gestione del messaggio di loading */
var cursoreY = 0;
var nava = (document.layers);
var dom = (document.getElementById);
var iex = (document.all);
var cach = null;
if (nava) {
    cach = document.cache
}else if (dom) {
    cach = document.getElementById("wait").style
}else if (iex) {
    cach = cache.style
}
cach.visibility = "hidden";
function waitOn(y){
    if(y == null) y = 300;
    largeur = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    cach.left = Math.round((largeur/2)-100) + "px";

    cach.top = (document.body.scrollTop + document.documentElement.scrollTop + y) + 'px';
    cach.visibility = "visible";
}
function waitOff(){
    cach.left = 0;
    cach.visibility = "hidden";
}
window.onunload = function (e) {
    waitOff();
}
/* *********************************** */

