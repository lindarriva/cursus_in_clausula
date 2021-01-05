function showHelp(id, hor_shift, ver_shift){
	// preliminarmente chiude eventuali tooltip aperti
	tooltip_hideAll();
	if(hor_shift == null) hor_shift = 0;
	if(ver_shift == null) ver_shift = 0;
	
	tooltip_show_para(id, hor_shift, ver_shift); 
}
function tooltip_hideAll(){
	// chiude tutti i tooltip di help aperti
	for(i = 1; i <= 10; i ++){
		id = "help_" + i;
		obj = document.getElementById(id);
		if(obj != null){ 
			tooltip_hide(id);
		}else{
			break;
		}
	}
}