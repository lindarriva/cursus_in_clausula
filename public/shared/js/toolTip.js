var cursoreX = 0;
var cursoreY = 0;
var blocca = 0;
var idPrec = "";
function tooltip_show(tooltipId){
	tooltip_show_para(tooltipId, 10, 10);
}

function tooltip_show_para(tooltipId, hor_shift, ver_shift){
	/* Con parametri di posizione:
	hor_shift: spostamento della posizione in orizzontale
	ver_shift: spostamento della posizione in verticale*/
	
	

    it = document.getElementById(tooltipId);
    if(tooltipId == idPrec){
        blocca = 1;
    }else{
        blocca = 0;
        idPrec = tooltipId;
    }    
    
    // gestione delle uscite dalla finestra: da collaudare; 
    // style.width e style.height sono empty se non sono stati definiti esplicitamente nello stile
	var l = it.style.width;
	if(l != ""){
		l = l.substring(0, l.length -2);
		l = parseInt(l);	
		var diff = (cursoreX + l) - window.innerWidth;
    	if(diff > 0){
        	cursoreX = cursoreX - (diff + 20);
        	if(cursoreX < 0) cursoreX = 0;
        	hor_shift = 0;
    	}
    }
	var h = it.style.height;
	if(h != ""){
		h = h.substring(0, h.length -2);
		h = parseInt(h);	
		diff = (cursoreY + h) - window.innerHeight;
    	if(diff > 0){
        	cursoreY = cursoreY - (diff + 20) ;
       	 	if(cursoreY < 0) cursoreY = 0;
       	 	ver_shift = 0;
    	}
    }
    it.style.top = (cursoreY + ver_shift) + 'px';
    it.style.left = (cursoreX + hor_shift) + 'px';
    it.style.visibility = 'visible';
}


function tooltip_hide(id){
    it = document.getElementById(id);
    it.style.visibility = 'hidden';
    blocca = 0;
}
function mouseCoor(evt){
    if(blocca == 0){
        if(!evt) var evt = window.event;
            cursoreX = evt.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            cursoreY = evt.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }
    }
document.onmousemove=mouseCoor;
