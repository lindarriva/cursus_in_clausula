/* funzioni per il pannello METRI nella griglia di ricerca */    
    

    function annullaMetri(){
		document.toServer.metri1.checked = false;
		document.toServer.metri2.checked = false;
		document.toServer.metri3.checked = false;
		document.toServer.metri4.checked = false;
		document.toServer.metri5.checked = false;
		document.toServer.metri6.checked = false;
		document.toServer.metri7.checked = false;
		document.toServer.metri8.checked = false;
		document.toServer.metri9.checked = false;
		document.toServer.metri10.checked = false;
		document.toServer.metri11.checked = false;
		document.toServer.metri12.checked = false;
		document.toServer.metri13.checked = false;
		document.toServer.metri14.checked = false;
		document.toServer.metri15.checked = false;
		document.toServer.metri16.checked = false;
		document.toServer.metri17.checked = false;
		document.toServer.metri19.checked = false;
		document.toServer.metri20.checked = false;
		document.toServer.extratesto1.checked = true;
		document.toServer.extratesto2.checked = false;
		document.toServer.extratesto3.checked = false;
		apriChiudiPannello("_metri", "", true);
		openClose(document.getElementById('headingMetri'));
    }
    function dattilici(){
		if(document.toServer.metri19.checked){
            document.toServer.metri1.checked = true;
            document.toServer.metri2.checked = true;
            document.toServer.metri3.checked = true;
            document.toServer.metri4.checked = true;
            document.toServer.metri19.checked = true;
		}else{
            document.toServer.metri1.checked = false;
            document.toServer.metri2.checked = false;
            document.toServer.metri3.checked = false;
            document.toServer.metri4.checked = false;
            document.toServer.metri19.checked = false;
        }
    }
    function annullaDattilici(){
	if(!document.toServer.metri1.checked || !document.toServer.metri2.checked
        || !document.toServer.metri3.checked || !document.toServer.metri4.checked){
            document.toServer.metri19.checked = false;
	}
    }


    function eolici(){
		if(document.toServer.metri20.checked){
            document.toServer.metri4.checked = true;
            document.toServer.metri7.checked = true;
            document.toServer.metri8.checked = true;
            document.toServer.metri9.checked = true;
            document.toServer.metri11.checked = true;
            document.toServer.metri12.checked = true;
            document.toServer.metri20.checked = true;
		}else{
            document.toServer.metri4.checked = false;
            document.toServer.metri7.checked = false;
            document.toServer.metri8.checked = false;
            document.toServer.metri9.checked = false;
            document.toServer.metri11.checked = false;
            document.toServer.metri12.checked = false;
            document.toServer.metri20.checked = false;
        }
    }
    function annullaEolici(){
		if(!document.toServer.metri4.checked || !document.toServer.metri7.checked || !document.toServer.metri8.checked
        || !document.toServer.metri9.checked || !document.toServer.metri10.checked
        || !document.toServer.metri11.checked || !document.toServer.metri12.checked){
            document.toServer.metri20.checked = false;
		}
    }


