/* pannelli di tipo titlePanel, con apertura e chiusura; esempio:

    <div class="titlePanel" id="panelAUT">
        <a class="closePanel" id="AUT" href="javascript:apri('AUT')">
            AUTORI
        </a>
        <div class="closePanel" id="divAUT">
            // contenuto .....
        </div>
    </div>

l'id della div interna deve essere uguale all'id dell'<a>, con prefisso 'div'

*/


function apriChiudiTitlePanel(id){
    var obj = document.getElementById(id);
    var classNome = obj.className;
    if(classNome == 'closePanel'){
        apriTitlePanel(id);
    }else{
        chiudiTitlePanel(id);
    }
}
function apriTitlePanel(id){
    var obj = document.getElementById(id);
    obj.className = "openPanel";
    obj = document.getElementById("div" + id);
    obj.style.display = "block";
}
function chiudiTitlePanel(id){
    var obj = document.getElementById(id);
    obj.className= "closePanel";
    obj = document.getElementById("div" + id);
    obj.style.display = "none";
}