var blocca = 0;
function checkAree(valore){
    if(blocca == 1) return;
    if (valore == "m0"){
        if(document.toServer.m0.checked){
            blocca = 1;            
			for(var x = 0, objs = document.toServer.elements; x < objs.length; x++){
				for(var y = 1; y <= objs.length; y++){
                	if(objs[x].name == ("m" + y)){
                        objs[x].checked = false;
                    }
                }                        
            }            
            blocca = 0;
        }
    }else{
        if(document.toServer.m0.checked) document.toServer.m0.checked = false;
    }
}
function annullaGriglia(){
    document.toServer.a_1_1.value = "";
    document.toServer.a_1_2.value = "";
    document.toServer.a_2_1.value = "";
    document.toServer.a_2_2.value = "";
    document.toServer.a_3_1.value = "";
    document.toServer.a_3_2.value = "";
    document.toServer.logop_1.options[0].selected=true;
    document.toServer.logop_2.options[0].selected=true;
    document.toServer.logop_3.options[0].selected=true;
    annullaPiu();
    document.toServer.a_1_1.focus();
    
}
function annullaAutori(){
    var n = document.toServer.autori.options.length;
    for(var i=0; i < n; i++){
        document.toServer.autori.options[i].selected=false;
    }
}

