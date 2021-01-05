function evidenzia(obj, tableName, backgroundColor){
    var hidden = document.getElementById("EVI" + tableName);
    var stringa;
    var id = hidden.value;

    if(id != ""){
    	var row = document.getElementById(hidden.value);

		if(row != null){

    		// toglie evidenziazione precedente
    		stringa = row.getAttribute("class");
    		if(stringa.indexOf(" SCURO") > -1){
        		stringa = "HI HIlink HIscuro"
    		}else{
        		stringa = "HI HIlink"
    		}
    		row.setAttribute("class", stringa);
    	}
	}
    stringa = "evi";
    if(backgroundColor != "") stringa += " " + backgroundColor;
    obj.setAttribute("class", stringa);
 
    // memorizza nell'elemento hidden l'id della riga
    hidden.value = obj.id;
}

function evidenziaMulti(obj, tableName, backgroundColor){
	// Gestisce la selezione multipla: in HIdatagrid si deve porre setMultiSelection(true);
	var stringa = obj.getAttribute("class");
	if(stringa.indexOf("evi") == 0){
		// la riga era già evidenziata: si toglie
    	if(stringa.indexOf(" SCURO") > -1){
        	stringa = "HI HIlink HIscuro"
    	}else{
        	stringa = "HI HIlink"
    	}
    	obj.setAttribute("class", stringa);
	}else{
		// si evidenzia
    	stringa = "evi";
    	if(backgroundColor != "") stringa += " " + backgroundColor;
    	obj.setAttribute("class", stringa);
	}	
    document.getElementById("EVI" + tableName).value = obj.id;
}




function getSelectedId(nomeGrid){
    if(document.getElementById("EVI" + nomeGrid) == null){
    	return null;
    }
    var id = document.getElementById("EVI" + nomeGrid).value;
    id = id.substring(nomeGrid.length);
    return id;
}
function getScrollValue(nomeGrid){
    var obj = document.getElementById("HIdivtable_" + nomeGrid);
    var result = 0;
    if(obj != null) result = obj.scrollTop;
    return result;
}
function setScrollValue(nomeGrid, value){
    var obj = document.getElementById("HIdivtable_" + nomeGrid);
    if(obj != null) obj.scrollTop = value;
}