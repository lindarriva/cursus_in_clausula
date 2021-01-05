var this_urlProgressBar;
function startProgressBar(url){
    this_urlProgressBar = url;
    var obj = document.getElementById("progressBar");
    var l = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    obj.style.left = Math.round((l/2) - 200) + "px";
    obj.style.visibility = "visible"; 
    document.getElementById("progress").style.width = "25px";
    setInterval("aumentaProgressBar()", 100);        
}
function aumentaProgressBar(){
    var barra = 0;
    dojo.xhrPost( {
        url: this_urlProgressBar,
        // funzione eseguita in risposta
        load: function(response) {
            var n = response.indexOf("@")
            barra = response.substring(n + 1);
            n = barra.indexOf("#")
            barra = barra.substring(0, n);
            barra = barra + "px";
            var obj = document.getElementById("progress");
            obj.style.width = barra;
        }
        // funzione eseguita in caso di errore
//        error: function(error){
//            alert(error);
//        }
    });    	        
}

