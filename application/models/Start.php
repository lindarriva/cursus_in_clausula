<?php

class Application_Model_Start
{

//funzione che controlla l'ultimo carattere di una parola
public static function isFinale ($stringa){
    $result=false;
    $n = strlen($stringa);
        if ($n === strrpos($stringa)){
            $result = true;
        }
        return $result;
 }

//cerca una sottostringa dentro una stringa a partire da qualsiasi posizione
//il valore va impostato a -1, altrimenti con 0 ignora la prima posizione!!!

public static function inStringa ($stringa, $cerca, $start=0){
    $n = mb_strpos($stringa, $cerca, $start);
    if ($n === false){
            return -1;
    }
    else {
            return $n; //= numero della posizione
    }
}



//public static function is_Consonante($carattere){
//    $carattere = mb_strtoupper ($carattere);
//    $vocali = 'bcdfgjlmnpqrstvzwx';
//    $result = false;
//    $n = -1;
//  // echo "C=" . $carattere . "##<br/>";
//    $n = strpos ($vocali, $carattere);
//    if ($n === false){
//    }else {
//            $result = true;
//    }
//    return $result;
//}


//metodo che verifica la presenza di vocale
public static function is_Vocale($carattere){
    //$carattere = strtoupper ($carattere);
    $vocali = 'AEIOUYĀĒĪŌŪȲāēīōūĂĔĬŎŬăĕĭȳўŏŭ';
    $result = false;
    $n = -1;
  // echo "C=" . $carattere . "##<br/>";
    $n = mb_strpos ($vocali, $carattere);
    
    if ($n === false){
    }else {
            $result = true;
    }
    return $result;
}
//
//metodo che verifica la presenza di vocale lunga
public static function is_Lunga($carattere){

    $vocali = 'ĀĒĪŌŪāēīōū';
    $result = false;
    $n = -1;
  // echo "C=" . $carattere . "##<br/>";
    $n = mb_strpos ($vocali, $carattere);
    if ($n === false){
    }else {
            $result = true;
    }
    return $result;
}

public static function is_Sinalefe ($stringa){
    $result = false;
    $chars = str_split($stringa);
    for ($i=1; $i<count($chars); $i++){//inizializzo a 1, poichè non avrò mai sinalefe in prima posizione
        if ($chars[$i-1] === "#"){
            if ((is_Vocale($chars[$i])) || ($chars[$i]== "*") ){
                $result = true;
            }else{
                $result = false;
            }
        }
    }
    return $result;
}


//dovrebbe bastare:
public static function cambiaV_U($parola){
    $parola = strtoupper($parola);
    $parola = str_replace ("V", "U", $parola);
    return $parola;
}

public static function cambiareU_V($parola){
    $parola = strtoupper($parola);
    $parola = str_replace ("U", "V", $parola);
    return $parola;
}


public static function cancellaH_init($parola){
    $parola = strtoupper($parola);
    $array = explode (" ", $parola);
    for ($i=0; $i<count($array); $i++){
    	if (startsWith($array[$i], "H")){
        $array[$i] = str_replace ("H", "", $array[$i]);
       	}
       	$parola = implode (" ", $array);
    }
    return $parola;
}

public static function cons_doppie ($parola){
    $parola = strtoupper($parola);
    $char = str_split($parola);
    for ($i=0; $i<count($char); $i++){
        if ($char[$i] == "X" && $i<count($char)-1){ // e se non è l'ultima!
                $char[$i] = "KK";
        }
        if ($char[$i] == "Z" && $i<count($char)-1){ // e se non è l'ultima!
                $char[$i] = "KD";
        }
    }
    $parola = "";
    for ($i=0; $i<count($char); $i++){
            $parola .= $char[$i];
    }
    return $parola;
}

public static function occl_liquida ($parola){
    $stringa = explode (" ", $parola);
    for ($i=0; $i<count($stringa); $i++){
    $stringa = str_replace("BL", "B.L", $stringa);
    $stringa = str_replace("BR", "B.R", $stringa);
    $stringa = str_replace("FL", "F.L", $stringa);
    $stringa = str_replace("FR", "F.R", $stringa);
    $stringa = str_replace("PL", "P.L", $stringa);
    $stringa = str_replace("PR", "P.R", $stringa);
    $stringa = str_replace("PHR", "P.H.R", $stringa);
    $stringa = str_replace("PHL", "P.H.L", $stringa);
    $stringa = str_replace("CR", "C.R", $stringa);
    $stringa = str_replace("CL", "C.L", $stringa);
    $stringa = str_replace("CHR", "C.H.R", $stringa);
    $stringa = str_replace("GL", "G.L", $stringa);
    $stringa = str_replace("GR", "G.R", $stringa);
    $stringa = str_replace("DL", "D.L", $stringa);
    $stringa = str_replace("DR", "D.R", $stringa);
    $stringa = str_replace("TR", "T.R", $stringa);
    $stringa = str_replace("TL", "T.L", $stringa);
    $stringa = str_replace("THL", "T.H.L", $stringa);
    $stringa = str_replace("THR", "T.H.R", $stringa);
    $stringa = str_replace("QV", "Q.V", $stringa);
    }
    $parola = implode (" ", $stringa);
    return $parola;
 }

//camabiando la sostituzione del gruppo con il punto...non serve più
public static function is_Liquida($carattere){
    $result = false;
    $n = -1;
    $n = strpos ("w", $carattere);
    if ($n === false){
    }else {
            $result = true;
    }
    return $result;
}


// definisce particolarità e cambia U/V e I/J
public static function definisciSemivocali ($testo){

    $testo = strtoupper ($testo);
    $array = explode (" ", $testo);
    for ($i=0; $i < count($array); $i++){
        if (startsWith ($array[$i], "URGUE")) {
                $array[$i] = "URGVE" . substr ($array[$i], 5);
        }
        if(startsWith ($array[$i], "PERURGUE")){
                $array[$i] = "PERURGVE" . substr (($array[$i]), 8);
        }
        if(startsWith ($array[$i], "BIIUG")){
                $array[$i] = "BIJUG" . substr (($array[$i]), 5);
        }
        if(startsWith ($array[$i], "TRIIUG")){
                $array[$i] = "TRIJUG" . substr (($array[$i]), 6);
        }
        if (startsWith ($array[$i], "TERIUG")){
                $array[$i] = "TERJUG". substr (($array[$i]), 6);
        }
        if(startsWith ($array[$i], "QUADRIIUG")){
                $array[$i] = "QUARIJUG" . substr (($array[$i]), 9);
        }
        if(startsWith ($array[$i], "QUINQUEIUG")){
                $array[$i] = "QUINQUEJUG" . substr (($array[$i]), 10);
        }
        if(startsWith ($array[$i], "ALTIIUG")){
                $array[$i] = "ALTIJUG" . substr (($array[$i]), 7);
        }
        if(startsWith ($array[$i], "MULTIIUG")){
                $array[$i] = "MULTIJUG" . substr (($array[$i]), 8);
        }
        if(startsWith ($array[$i], "CELSIIUG")){
                $array[$i] = "CELSIJUG" . substr (($array[$i]), 8);
        }

        $array[$i] = str_replace ("UUE","UVE", $array[$i]);
        $array[$i] = str_replace ("UUA","UVA", $array[$i]);
        $array[$i] = str_replace ("UUI","UVI", $array[$i]);
        $array[$i] = str_replace ("UUO","UVO", $array[$i]);
        $array[$i] = str_replace ("UUU","UVU", $array[$i]);
        $array[$i] = str_replace ("IUI","IVI", $array[$i]);
//        $array[$i] = str_replace ("SUAD", "SVAD", $array[$i]);
//        $array[$i] = str_replace ("SUAU", "SVAU", $array[$i]);
//        $array[$i] = str_replace ("SUET", "SVET", $array[$i]);
//        $array[$i] = str_replace ("SUEU", "SVEU", $array[$i]);
//        $array[$i] = str_replace ("SUEB", "SVEB", $array[$i]);
//        $array[$i] = str_replace ("SUAS", "SVAS", $array[$i]);
//        $array[$i] = str_replace ("SUES", "SVES", $array[$i]);
//        $array[$i] = str_replace ("SUEM", "SVEM", $array[$i]);
        if ($array[$i] === "SVEM"){
            $array[$i] = "SUEM";
        }
        if ($array[$i] === "SVES"){
            $array[$i] = "SUES";
        }
        if ($array[$i] === "SVAS"){
            $array[$i] = "SUAS";
        }
//trasforma la U in V secondo le regole generali della u semivocalica
        $array[$i] = self::cambiaU_V($array[$i]);
        $array[$i]= self::cambiaI_J ($array[$i]);
        $array[$i]= self::individua_PRE ($array [$i], "U", "V");
        $array[$i]= self::individua_PRE ($array [$i], "I", "J");

    }

    $testo = implode (" ", $array);
    return $testo;
}


//definisce casi regolari della u semivocalica
public static function cambiaU_V ($parola){
    $char = str_split($parola);
    for ($i=0; $i<count($char); $i++){
        if ($char[$i] == "U" && $i<count($char)-1){ // e se non è l'ultima!
            if (self::is_Vocale ($char[$i+1])){
                if ($i==0){ // se è in prima posizione
                    $char[$i] = "V";
                }else if ($char[$i-1] == "Q" || self::is_Vocale ($char[$i-1])){
                     $char[$i] = "V";
                    //caso IUVvocale
                    if ($i < count($char)-2) {
                        if ($char[$i+1] == "V" && self::is_Vocale ($char[$i+2]) && is_Vocale ($char[$i-1])){
                            $char[$i] = "U";
                        }
                    }
                }else if ($i > 1){	//preceduto da R o L ma solo se precedute da vocale
                    if ($char[$i-1] ==="L" || $char[$i-1] ==="R"){
                          if (self::is_Vocale ($char[$i-2])){
                                $char[$i] = "V";
                          }

                    }//caso sangu...
                    if ($char[$i-2] =="N" && $char[$i-1] =="G" && self::is_Vocale($char[$i+1])){
                          $char[$i] = "V";
                    }
                }
            }
        }
    }
    $parola = "";
    for ($i=0; $i<count($char); $i++){
            $parola .= $char[$i];
    }
    return $parola;
}

//definisce casi regolari della i semivocalica
public static function cambiaI_J ($parola){
    $char = str_split($parola);
    for ($i=0; $i<count($char); $i++){
        if ($char[$i] == "I" && $i<count($char)-1){ // e se non è l'ultima!
                if (self::is_Vocale ($char[$i+1])){
                        if ($i==0){ // se è in prima posizione
                                $char[$i] = "J";
                        }
                }
                if ($i> 0 && $i<count($char)-1){ //parti dalla prima posizione e non andare oltre l'ultima
                        if (self::is_Vocale ($char[$i-1]) && self::is_Vocale ($char[$i+1])){
                                $char[$i] = "J";
                        }
                }
        }
    }
    $parola = "";
    for ($i=0; $i<count($char); $i++){
            $parola .= $char[$i];
    }
    return $parola;
}


//NB: cambia modalità!!! abbiamo aggiunto il puntino!!!
//contasillabe di ogni parola
public static function contaSi ($parola){

    $parola = strtoupper($parola);

    $parola = str_replace( "AE", "E", $parola);
    $parola = str_replace( "OE", "E", $parola);
    $parola = str_replace("AU", "A", $parola);
    $array = str_split($parola);
    $parola = "";
    echo $parola;
    $result = 0;
    for ($i=0; $i<count($array); $i++){
        if ($array[$i] == "I"){
                if ($i<count($array)-1 && self::is_Vocale ($array[$i+1])){
                        if ($i==0 || self::is_Vocale ($array[$i-1])){
                                $array[$i] = "J";
                        }
                }
        }
    }
    for ($i=0; $i<count($array); $i++){
        if (self::is_Vocale ($array[$i])){
                $result++;
        }
    }

    return $result;
}


//individua composti di parole che iniziano per prefissi
public static function individua_PRE ($parola, $cerca, $sostituisci){
    //$prefissi = array ("AD","AB","ANIMAD","CIRCUM","CON","DIS","DISCON","IN","INTER","OB","PER","PRAETER","PROPTER","SUB","SUBTER","SUPER","SUPERIN","TRANS");
    $x = Application_Model_Costanti::getPrefissi();
    for ($i=0; $i<count($x); $i++){ // scorri l'array prefissi, ovvero per 17 volte e se trovi un prefisso
        $prefisso = $x[$i];
        $eccezioni = array();
        $eccezione = "";
        //isolo le eccezioni
        if (($n=self::inStringa($prefisso, "["))>-1){
            $prefisso = substr($prefisso, 0, -1);//togli parentesi quadra [stavamo scrivendo un strlen??]
            $eccezioni = explode(";", substr($prefisso, $n+1)); //parti dalla fine del prefisso
            $prefisso = substr($prefisso, 0, $n);//puro prefisso
        }
        $pre_parola = $prefisso . $cerca;

        //$pre_parola = $x[$i] . $cerca; // aggiungi I o U
         if (startsWith ($parola, $pre_parola)){ //	se la parola inizia con $preparola
                if (strlen($parola) > strlen($pre_parola)){
                    $k = true;
                        for ($j=0; $j<count($eccezioni); $j++){
                            if (startsWith ($parola, $prefisso.$eccezioni[$j])){
                                $k = false;
                                break;
                            }
                    }
                    if ($k===true && self::is_Vocale(substr($parola, strlen($pre_parola), 1))){
                        $parola = $x[$i] . $sostituisci . substr($parola, strlen($pre_parola)); //sostituisti J o V
                    }
                }

                break;
        }
    }
return $parola;
}

//restituisce un array ordinato di parole non considerando quelle ripetute
public static function no_Doppi ($array){

    sort ($array); //mette in ordine l'array
    $doppio[0] = $array[0];//il primo elemento dell'array originale è sempre considerato
    $conta = 1; //tiene il conto degli elementi diversi

            for ($i=0; $i<count($array); $i++){
                    if ($doppio[$conta-1] != $array[$i]){ //quando l'elemento è uguale non lo copia
                            $doppio[$conta] = $array[$i];
                            $conta++;
                    }
            }

    return $doppio;
}



/*/////FUNZIONI DEDICATE AL PERFETTO////
In -LU- e -RU- seguiti da vocale la U è trattata di norma come semivocale; questa procedura però tratta
diversamente il caso dei perfetti, in cui di norma la U è vocalica; essa perciò verifica in base alle desinenze
se si tratta di una voce di perfetto e quindi assegna la U vocalica.
Vanno tuttavia riconosciute le eccezioni e le omografie.

Le eccezioni riguardano verbi in cui il perfetto è in -VI: SOLVO e i suoi composti, i composti di VOLVO, FERVEO e i suoi composti;
questi vengono qui riconosciuti e protetti dal trattamento.

Le omografie riguardano:

1) la 1° persona singolare del perfetto, che può essere omografa con infiniti passivi o anche con nomi
e aggettivi, tanto che -I è stata esclusa dalle desinenze che individuano il perfetto; pertanto se la U è vocalica
questa persona deve essere sempre inserita nel file semivocali.xml;
le eventuali forme omografe in V vanno invece marcate nel testo; questi i casi:
CALUI (CALEO, CALESCO)      CALVI (CALVUS, inf. di CALVOR) (5 occorrenze totali)
INCALUI (INCALEO)           INCALVI (inf. di INCALVOR) (2 occorrenze totali)
SERUI (SERO)                SERVI (SERVUS) (85 occorrenze totali)
SALUI (SALIO)               SALVI (SALVUS) (7 occ. totali)

2) la 3° persona singolare del perfetto, che può essere omografa con la 3° persona sing. del presente di un altro verbo;
tuttavia la desinenza -IT viene qui considerata (perciò questa persona non va inserita nel file semicolai.xml),
mentre i casi di omografia sono individuati da marcatura del testo; questi sono i casi:

SERUIT (SERO)               SERVIT (pr. di SERVIO) (111 occ. totali)
INSERUIT (INSERO)           INSERVIT (pr. di INSERVIO) (24 occ. totali)
DESERUIT (DESERO)           DESERVIT (pr. di DESERVIO) (59 occ. totali)

3) Caso VOLO - VOLVO: i perfetti sono completamente omografi; le forme nei testi sono disambiguate mediante la marcatura volui(==VOLUI)

4) SALUERE perfetto di SALIO e SALVERE infinito di SALVEO: si esclude SALUERE dal meccaninismo e si inseriscono in semivocali.xml
le due forme come omografe; questo obbliga a marcare tutti i casi, come per VOLUI e VOLVI
*/
public static function cerca_perfetto ($stringa, $tema){
    //la U di -LU o -RU è convertita in #, in modo che non venga modificata
    $des_perfetti= Application_Model_Costanti::getDesinenze();
    $result = array();

    $n = 0;
    $k = false; //è un perfetto?
    $stringa1 = "";
    $stringa2 = "";
    $eccezione = Application_Model_Costanti::getSemivocale();
    $omo = Application_Model_Costanti::getOmografi();
    $n= strpos($stringa, $tema);
    if ($n===false){//per ovviare risultato anomalo di strpos
    } else if ($n > 0 ){

        $char = str_split($stringa);
        if (self::is_Vocale($char[$n-1])){
            $stringa1 = substr($stringa, $n+2);
            for ($i=0; $i < count($des_perfetti); $i++){//scorriamo l'array dei perfetti
                if ($stringa1 === $des_perfetti[$i]){
                    $k = true;
                    break;
                }
            }
            if ($k===true){

                $stringa2 = substr ($stringa, 0, $n+2) . "I";//radice-tema +I
                //seguono eccezioni
                if ($stringa === "SALUERE"){
                    $result[0] = $stringa;
                } else if (in_array($stringa2, $eccezione)=== true){
                    //SOLVI, VOLVI e composti
                    $result [0] = $stringa;
                } else if (in_array($stringa2, $omo)=== true){
                    $result[0] = $stringa;
                    $result[1] = substr($stringa, 0, $n+1) . "#" . $stringa1;
                }else{

                    $result[0] = substr($stringa, 0, $n+1) . "#" . $stringa1;

                }
                for ($i = 0; $i < count($result); $i++){

                    $result[$i] = self::cambiaU_V($result[$i]);
                    $result[$i] = str_replace("#", "U", $result[$i]);


                }
            }
        }
    }
    return $result;
}

public static function perfetti ($stringa){

    $result = self::cerca_perfetto($stringa, "LU");
    if (count($result)=== 0){
        $result = self::cerca_perfetto($stringa, "RU");
    }
    return $result;
}

public static function semivocali ($stringa){
    $result = in_array($stringa, $dbsemivocali);
}

public static function getArrayOrdine ($stringa_ordine, $ordine){
//restituisce l'array degli ordinamenti di colonna da inviare alla griglia
    //$stringa_ordine impostazione base: A= ascendente, D=discendente, X=nessuna
    //$ordine    formato da numero(=colonna, 0) e tipo (A, D, X)
    $result = str_split($stringa_ordine);
    $i=substr($ordine, 0, 1); //prendo la colonna
    $verso = "A";//default
    if (substr($ordine,1)==="A"){
        $verso="D";
    }
    $result[$i]=$verso;
    foreach ($result as $key=>&$value){
        if ($value=="X"){
            $value="";
        }else if($key==$i){
            $value="*" . $value;//colonna scelta
        }
    }
    return $result;

}



}

