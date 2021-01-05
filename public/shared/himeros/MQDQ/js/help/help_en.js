
require(["dojo/ready", "dijit/TooltipDialog", "dijit/form/DropDownButton"], function(ready, TooltipDialog, DropDownButton){
    ready(function(){
        var inizio = "<div style='width:350px;padding-bottom:0px'>";
        var testo = inizio
            + "<p class='help_dojo'>The use of the wild card symbols (<b>?</b> and <b>*</b>) "
            + "is permitted to indicate one or more characters, and inverted commas to enclose precise sequences. "
            + "In typing the word to be searched:</p>"
            + "<ul class='help_dojo'>"
            + "<li>upper case or lower case letters may be used: the search is case insensitive; </li>"
            + "<li>the two letters U and V are both considered: when searching for VENIO or UENIO the same result is predicted;"
            + "<li>the two letters I and J are both considered: when searching for JUSTITIA or IUSTITIA the same result is predicted."
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

