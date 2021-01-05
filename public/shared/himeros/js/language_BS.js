    function setLanguage(value, path, reload){
        $.ajax({
            url : path,
            sync: true,
            type: "POST",
            // passaggio dei parametri POST
            data: {
                cookie: "language",
                value: value
            },
            success : function (data, stato) {
        	if(reload == null){
                    window.location.reload(true);
                }else{
                    location.href=reload;
                }
            },
            error : function (richiesta,stato,errori) {
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
    }