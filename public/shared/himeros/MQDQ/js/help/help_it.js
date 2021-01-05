
require(["dojo/ready", "dijit/TooltipDialog", "dijit/form/DropDownButton"], function(ready, TooltipDialog, DropDownButton){
    ready(function(){
        var inizio = "<div style='width:350px;padding-bottom:0px'>";
        var testo = inizio
            + "<p class='help_dojo'>&Egrave; ammesso l'uso dei simboli <b>?</b> e <b>*</b> per indicare rispettivamente caratteri singoli o gruppi. "
            + "Nella digitazione della chiave:</p>"
            + "<ul class='help_dojo'>"
            + "<li>è indifferente l'uso di caratteri maiuscoli o minuscoli;</li>"
            + "<li>U e V sono considerati del tutto indifferenti;</li>"
            + "<li>I e J sono considerati del tutto indifferenti."
            + costante_help + "</li></ul>";
        var myDialog = new TooltipDialog({
            content: testo
        });
        var myButton = new DropDownButton({
            label: "?",
            dropDown: myDialog
        });
        document.getElementById("help_1").appendChild(myButton.domNode);


   });

});

