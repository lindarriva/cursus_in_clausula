/* funzioni per la griglia di ricerca per Bootstrap*/    

    function apriChiudiPannello(id, campoFocus, chiudiSempre){
        if(chiudiSempre == null) chiudiSempre = false;        
        var classe = document.getElementById(id).className;
        var tmp = classe.split(" ");
        var stato = tmp[tmp.length - 1];
        if(stato == "in" || chiudiSempre){
    		$('#' + id).collapse('hide');
        }else{
    		$('#' + id).collapse('show');
            if(campoFocus != "") document.getElementById(campoFocus).focus();
        }
    }    
    function apriPannello(id){
    	$('#' + id).collapse('show');
    	id = "heading" + id.substring(1, 2).toUpperCase() + id.substring(2);
    	openClose(document.getElementById(id));
    } 
    
    function openClose(obj){
    	if(obj.className.indexOf("panel-title") > -1){
    		obj = obj.parentNode;
    	}
        if(obj.className.indexOf("panel-closed") > -1){
            obj.className = "panel-heading panel-opened";
        }else if(obj.className.indexOf("panel-opened") > -1){
            obj.className = "panel-heading panel-closed";
        }
    }
    