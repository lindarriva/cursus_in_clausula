
function muoviSezione(verso){
    var obj = document.getElementById("cambiaSezione");
    obj.selectedIndex = obj.selectedIndex + verso;
    document.toServer.submit();
}