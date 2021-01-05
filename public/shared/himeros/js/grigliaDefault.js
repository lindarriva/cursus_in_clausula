function showCampoChiave(col){
    obj = document.getElementById("input" + col + "_1");
    obj.style.visibility = "visible";        
    obj = document.getElementById("input" + col + "_2");
    obj.style.visibility = "visible";    
    obj = document.getElementById("label" + col);
    obj.style.visibility = "visible";   
    obj = document.getElementById("logop_" + col);
    obj.style.visibility = "visible";        
    obj = document.getElementById("posv" + col);
    if(obj != null) obj.style.visibility = "visible";        
    obj = document.getElementById("piu" + col);
    obj.style.display = "none";   
    if(col == 2){
        obj = document.getElementById("piu3");
        obj.style.display = "inline";   
        
    }
}
function showHelp(id){
	// preliminarmente chiude eventuali tooltip aperti
	tooltip_hideAll();
	tooltip_show(id); 
}
function showHelpOR(){
	tooltip_hideAll();
    obj = document.getElementById("logop_1");
    tooltip_show('help_3_' + obj.selectedIndex);
}

function tooltip_hideAll(){
	// chiude tutti i tooltip di help aperti
	for(i = 1; i <= 5; i ++){
		id = "help_" + i;
		obj = document.getElementById(id);
		if(obj != null) tooltip_hide(id);
	}
	for(i = 0; i < 3; i ++){
		id = "help_3_" + i;
		obj = document.getElementById(id);
		if(obj != null) tooltip_hide(id);
	}	
}


function annullaPiu(){
    for(i = 2; i <= 3; i ++){        
        obj = document.getElementById("input" + i + "_1");
        if(obj != null) obj.style.visibility = "hidden";        
        obj = document.getElementById("input" + i + "_2");
        if(obj != null) obj.style.visibility = "hidden";        
        obj = document.getElementById("label" + i);
        if(obj != null) obj.style.visibility = "hidden";        
        obj = document.getElementById("logop_" + i);
        if(obj != null) obj.style.visibility = "hidden";        
    }
    obj = document.getElementById("piu2");
    if(obj != null) obj.style.display = "inline";   
    obj = document.getElementById("piu3");
    if(obj != null) obj.style.display = "none";   
}
