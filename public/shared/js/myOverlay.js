function showMyOverlay(titolo){    
    var larghezza = document.getElementById("myOverlay").style.width;
    larghezza = larghezza.substring(0, larghezza.length - 2);
    var screenHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    document.getElementById("over_body").style.height = (screenHeight - 78) + "px";
    var screenLen = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    document.getElementById("myOverlay").style.left = ((screenLen - larghezza) / 2 ) + "px";
    document.getElementById("over_caption").innerHTML = titolo;
    document.getElementById("myOverlay").style.display = "block";
}
