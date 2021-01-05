<?php

function startsWith($stringa, $inizio) {
    $result=false;
    $n=mb_strlen($inizio);
    if ($n>mb_strlen($stringa)){
    	return false;
    	}
    $init=mb_substr($stringa, 0, $n);
    if ($init==$inizio) $result=true;
    return $result;
}

function endsWith($stringa, $fine) {
    $result=false;
    $n=strlen($fine);
    if ($n>strlen($stringa)){
    	return false;
    	}
    $end=substr($stringa, -$n);
    if ($end==$fine) $result=true;
    return $result;
}


function right ($stringa, $n){//caratteri da destra
    $len = strlen($stringa);
    if ($n>$len){
        $n=$len;
    }
    $n = $len-$n;
    return substr($stringa, $n);
}

//cancella la punteggiatura e presenta il testo in lettere maiuscole
function cancPunt($stringa) {
    $result="";
    $n=strlen($stringa);
    for($i = 0; $i < $n; $i++){
        $c=substr($stringa, $i, 1);
        
        if(ctype_alpha($c) || $c==" " || $c=="(" || $c=="=" || $c==")" || $c=="+" || $c=="-" || $c=="|"){
            $result = $result . $c;
        }elseif (ctype_cntrl($c)){
            $result .= " ";
        }

    }
    return mb_convert_case($result, MB_CASE_UPPER);
}

?>
