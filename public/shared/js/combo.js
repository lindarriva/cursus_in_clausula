
function popolaCombo(response, combo, idSelezionato){
    /* Popola un combobox via ajax.
     * La 'response' contiene i singoli valori di <option> separati da |;
     * ogni valore di <option> a sua volta contiene due valori (il value, cioè l'idparolechiave, e la parola che compare),
     * separati da @ (è essenziale che la stringa non termini con @!).
     * In questo modo si genera qui un array bidimensionale, jsonObj, che poi è associato all'elemento <select>, popolandolo
     * Questa funzione è necessaria perché l'innerHTML sul select non funziona
     * idSelezionato = è l'id (value) dell'item selezionato: se -1 nessuna voce è selezionata e il display è vuoto
     *
     * Importante: è indispensabile che il require della view contenga: "dojo/data/ItemFileWriteStore"
     */
    response = response.trim();
    if(response.indexOf("@") < 0) response = "@";
    var options = response.split("|");
    var display = "";
    var jsonObj = new Array();
    var storeData = {
        identifier: 'id',
        items: []
    }
    for (var i in options) {
        var tmp = options[i].split("@");
        jsonObj[i] = new Array();
        jsonObj[i]["id"] = tmp[0];      // 'id' non è un nome obbligatorio per l'identifier di storeData
        if(tmp[0] == idSelezionato){
            display = tmp[1];
        }
        jsonObj[i]["name"] = tmp[1];    // deve chiamarsi 'name'!
    }
    var data = new dojo.data.ItemFileWriteStore({ data: storeData });

    for (i = 0; i < jsonObj.length; i++) {
        data.newItem(jsonObj[i]);
    }
    combo.attr('store', data);
    // questo è il modo per selezionare l'item, mediante il suo identifier (cioè il value)
    if(response != "")  combo.set("value", idSelezionato);
    combo.set("displayedValue", display);
}