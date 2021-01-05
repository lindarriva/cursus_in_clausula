function showDiv(tab){
    // rende tutti i div non visibili e i tab non scelti 
    document.getElementById("divPresentazione").style.display="none";
    document.getElementById("tabPresentazione").className="tab";
    document.getElementById("divNota").style.display="none";
    document.getElementById("tabNota").className="tab";
    document.getElementById("divAbbreviazioni").style.display="none";
    document.getElementById("tabAbbreviazioni").className="tab";
    document.getElementById("divProgetto").style.display="none";
    document.getElementById("tabProgetto").className="tab";
    var stringa = tab.id.substring(3);
    document.getElementById("div" + stringa).style.display="block";
    
    
    document.getElementById("tab" + stringa).className="tab tabScelto";
}
