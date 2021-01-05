/*
Chiama mediante ajax il file cookies.php, che si trova in public, il quale 
aggiorna il cookie "language" che memorizza la lingua; richiede che nella singola view sia istanziato dojo mediante:

    require(["dojo/parser",
        "dojo/data/ItemFileWriteStore"
    ]);

Argomenti:

- value: sigla della lingua
- path: url assoluto della cartella /public

Dopo aver aggiornato il cookie, determina il reload della pagina corrente

*/
function setLanguage(value, path, reload){
    dojo.xhrPost( {
        url: path,
        sync: true,
        content: {
        cookie: "language",
        value: value
        },
        load: function(response) {
        	if(reload == null){
            	window.location.reload();
            }else{
            	location.href=reload;
            }
        },
        error: function(error){
            alert(error);
        }
    });
}
