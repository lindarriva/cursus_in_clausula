// editor è un oggetto wysihtml5, collegato a una textarea; questa perde la capacità
// di essere ridimensionata dall'utente; con questa funzione la textarea si ridimensiona
// automaticamente all'inserimento del testo; se auto = true la textarea si ridimensiona
// anche al caricamento della pagina; è importante che sia posta false nel caso degli overlay;
// la min-height di default (36) corrisponde, se vuoto, all'altezza di circa due righe che il campo 
// assume al focus, mentre al caricamento ha l'altezza di una riga; se si vuole che l'altezza al caricamento
// sia la stessa che al focus, porre nella textarea height:50px (per il default 36)
function autoresizeTextarea(editor, auto, minHeight){
    if(auto == null) auto = true;
    if(minHeight == null) minHeight = 36;
    editor.observe("load", function () {
        var $iframe = $(this.composer.iframe);
        var $body = $(this.composer.element);
        if(auto){
            var height = Math.min($body[0].scrollHeight, $body.height());
			if(height < minHeight){
				height = minHeight
			}
            $iframe.height(height);
        }
        $body
          .css({
            'min-height': minHeight,
            'overflow': 'hidden'
          })
          .bind('keypress keyup keydown paste change focus', function(e) {
            var height = Math.min($body[0].scrollHeight, $body.height());
            $iframe.height(height);
          });
          /*
          .bind('keypress keyup keydown paste change focus blur', function(e) {
            var height = Math.min($body[0].scrollHeight, $body.height());
            var extra = e.type == "blur" ? 0 : 20 ;
            $iframe.height(height + extra);
          });
          */
    });
}

function apriModal(modale, input){
	// apre la modale e pone il focus nel campo di input (no editor vysihtml)
	waitOff();	
    $('#' + modale).on('shown.bs.modal', function(){
        $(this).find('#' + input).focus();
    });
    $('#' + modale).modal();
}

