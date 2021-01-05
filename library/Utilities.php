<?php

class Utilities{

	static public function stringCount($str, $substr){
		// conta le occorrenze della sottostringa $substr nella stringa $str
		$len = mb_strlen($substr);
		$cont = 0;
		$ptr = 0;
		while(true){
        	$ptr = mb_strpos($str, $substr, $ptr);
        	if($ptr === false) break;
            $ptr += $len;
            $cont ++;
        }
		return $cont;
	}

	public static function indexOf($inStringa, $cerca, $start = 0){	
		if(empty($cerca)) return -1;
        if(($n = mb_strpos($inStringa, $cerca, $start)) === false){
        	return -1;
		}else{
            return $n;
        }	
	} 
	public static function lastIndexOf($inStringa, $cerca, $start = 0){	
        if(($n = mb_strrpos($inStringa, $cerca, $start)) === false){
        	return -1;
		}else{
            return $n;
        }	
	} 

	public static function right($stringa, $n){
		$l = strlen($stringa);
		$l -= $n;
		if($l < 0) $l = 0;
		return substr($stringa, $l);
	}

	public static function startsWith($stringa, $start){
		$n1 = strlen($stringa);
		$n2 = strlen($start);
		if($n2 > $n1) return false;
		$s = substr($stringa, 0, $n2);
		$result = $s == $start;	
		return $result;
	}
	public static function endsWith($stringa, $end){
		$n1 = strlen($stringa);
		$n2 = strlen($end);
		if($n2 > $n1) return false;
		$s = substr($stringa, $n1 - $n2);
		return $s == $end;
	}
	
	public static function isAlpha($char) {
		// riceve una stringa di 1 carattere;
		if(preg_match("/[a-zàáâäåçèéêëìíîïñòóôöùúûüỳýŷÿ]/i", $char)){
			$result = true;
		}else{
			$result = false;
		}
    	return $result;
	}
	public static function isPari($n) {
		if ( $n & 1 ) { 
    		return false;
		} else {
    		return true;
		} 
	}
	public static function deOrto($stringa){
		// toglie tutti i segni non alfabetici, spazi compresi
		$stringa = preg_replace("/[^A-Za-z]/", "", $stringa);
		return $stringa;
	}
	
	public static function array_unique_nosort($array){
		// riunisce gli uguali in sequenza, ma senza ordinare: perciò lo stesso valore può tornare più volte, se non è in sequenza
		$n = count($array);
		foreach ($array as $key => $value) {
			for($i = $key + 1; $i < $n; $i ++){
				if($array[$i] === $value){
					$array[$i] = "";
					/*
					unset($array[$i]);
					$n --;
					*/
				}else{
					break;
				}
			}
		}
		$array = array_filter($array);
		return $array;
	}
	
	
	
	public static function normalizzaApici($stringa){
		// sostituisce apici singoli e doppi curvi in apici diritti
        $stringa = str_replace("“", "\"", $stringa);
        $stringa = str_replace("”", "\"", $stringa);
        $stringa = str_replace("’", "'", $stringa);
        $stringa = str_replace("‘", "'", $stringa);
        $stringa = str_replace("…", "...", $stringa);
        $stringa = str_replace("º", "°", $stringa);        
        return $stringa;		
	}
	
	public static function trimAll($stringa){
		// esegue una ripulitura completa della fine del file, eliminando anche \r, \n e \t
		while(true){
			if(self::endsWith($stringa, "\n")
				|| self::endsWith($stringa, "\r")
				|| self::endsWith($stringa, "\t")){
				$stringa = trim(substr($stringa, 0, strlen($stringa) -1));
			}else{
				break;
			}
		}
		return $stringa;
	}
	
	
	
    public static function getItalianDate($data, $meseInLettere = false){
        // converte la data Y-m-d in giorno mese anno
        // gestione della data incompleta
        if(substr($data, 0, 4) == "0000"){
        	// anno assente
        	return "";
        }
        if(substr($data, 5, 2) == "00"){
        	// mese assente
        	return substr($data, 0, 4);
        }else if(substr($data, 8) == "00"){
        	// giorno assente
        	$mese = substr($data, 5, 2);
        	if($meseInLettere){
        		$mese = self::getMese($mese);   
        	}else{
        		if(substr($mese, 0, 1) == "0"){ 
        			$mese = substr($mese, 1);
        		}
        	}
        	return $mese . "-" . substr($data, 0, 4);
        }
        $timeStamp = self::getTimeStamp($data);	// $timeStamp è la data in formato numero
        if(empty($timeStamp)){ 
        	return "";
        }
        $giorno = date("d", $timeStamp);
        if(substr($giorno, 0, 1) == "0") $giorno = substr($giorno, 1);          
        $mese = date("m", $timeStamp);
        if($meseInLettere){
			$mese = self::getMese($mese);       
			return $giorno . " " . $mese . " " . date("Y", $timeStamp); 
        }else if(substr($mese, 0, 1) == "0"){ 
        	$mese = substr($mese, 1);
        }
        return $giorno . "-" . $mese . "-" . date("Y", $timeStamp);
    }
    public static function getItalianDateTime($data){
        // converte la data Y-m-d h:m in giorno mese anno ore minuti
        $timeStamp = self::getTimeStamp($data);
        if(empty($timeStamp)) return "";
        return date("d", $timeStamp) . "-" . date("m", $timeStamp) . "-" . date("Y", $timeStamp) . " " . date("H", $timeStamp) . ":" . date("i", $timeStamp);
    }

    public static function getTimeStamp($dataTime){
        $n = strpos($dataTime, " ");
        if(!$n){
        	// la data è in formato Y-m-d
        	$data = explode("-", $dataTime);
        	$time = array("00","00","00");
        }else{
        	// la data è in formato Y-m-d H:m:s
        	$data = explode("-", substr($dataTime, 0, $n));
        	$time = explode(":", substr($dataTime, $n + 1));
        }
        if($data[0] == 0) return "";
        return mktime($time[0],$time[1],$time[2],$data[1],$data[2],$data[0]);
    }
    public static function getMese($numMese){
        $result = "";
        switch ($numMese) {
            case 1:
                $result = "gennaio";
                break;
            case 2:
                $result = "febbraio";
                break;
            case 3:
                $result = "marzo";
                break;
            case 4:
                $result = "aprile";
                break;
            case 5:
                $result = "maggio";
                break;
            case 6:
                $result = "giugno";
                break;
            case 7:
                $result = "luglio";
                break;
            case 8:
                $result = "agosto";
                break;
            case 9:
                $result = "settembre";
                break;
            case 10:
                $result = "ottobre";
                break;
            case 11:
                $result = "novembre";
                break;
            case 12:
                $result = "dicembre";
                break;
            default:
                break;
        }
        return $result;
    }
	public static function datediff($tipo, $partenza, $fine){
		// restituisce la differenza tra due date in giorni (tipo=G), in settimane (tipo=S), in mesi (tipo=M), in anni (tipo=A)
        switch ($tipo){
            case "A" : $tipo = 365;
            break;
            case "M" : $tipo = (365 / 12);
            break;
            case "S" : $tipo = (365 / 52);
            break;
            case "G" : $tipo = 1;
            break;
        }
        $arr_partenza = explode("-", $partenza);
        $partenza_gg = $arr_partenza[2];
        $partenza_mm = $arr_partenza[1];
        $partenza_aa = $arr_partenza[0];
        $arr_fine = explode("-", $fine);
        $fine_gg = $arr_fine[2];
        $fine_mm = $arr_fine[1];
        $fine_aa = $arr_fine[0];
        $date_diff = mktime(12, 0, 0, $fine_mm, $fine_gg, $fine_aa) - mktime(12, 0, 0, $partenza_mm, $partenza_gg, $partenza_aa);
        $date_diff  = floor(($date_diff / 60 / 60 / 24) / $tipo);
        return $date_diff;
    }    
    
    public static function getComboData($data, $annoi, $annof, $ordine = 1){
        // da una data in formato 0000-00-00 restituisce un array di tre combo
        // con eventuale selezione
        // ordine: 1 crescente -1 decrescente
        $result = array();
        $array = explode("-", $data);
        if(empty($data)){
            $array = array("0000","00","00");
        }
        if($array[0] != "0000" && $array[0] < $annoi){
        	$annoi = $array[0];
        }
        $result["anni"] = "<option value=\"0000\">AAAA</option>\n";
        if($ordine == 1){
        	// ordine crescente
        	for($i = $annoi; $i <= $annof; $i ++){
            	$result["anni"] .= "<option value=\"" . $i . "\"";
            	if($i == $array[0]){
                	$result["anni"] .= " selected=\"selected\"";
            	}
            	$result["anni"] .= ">" . $i . "</option>\n";
        	}
        }else{
        	// ordine decrescente
        	for($i = $annof; $i >= $annoi; $i --){
            	$result["anni"] .= "<option value=\"" . $i . "\"";
            	if($i == $array[0]){
                	$result["anni"] .= " selected=\"selected\"";
            	}
            	$result["anni"] .= ">" . $i . "</option>\n";
        	}
        }

        $result["mesi"] = "<option value=\"00\">MM</option>\n";
        for($i = 1; $i <= 12; $i ++){
            $mm = self::right("00" . $i, 2);
            $result["mesi"] .= "<option value=\"" . $mm . "\"";
            if($mm == $array[1]){
                $result["mesi"] .= " selected=\"selected\"";
            }
            $result["mesi"] .= ">" . $i . "</option>\n";
        }

        $result["giorni"] = "<option value=\"00\">GG</option>\n";;
        for($i = 1; $i <= 31; $i ++){
            $gg = self::right("00" . $i, 2);
            $result["giorni"] .= "<option value=\"" . $gg . "\"";
            if($gg == $array[2]){
                $result["giorni"] .= " selected=\"selected\"";
            }
            $result["giorni"] .= ">" . $i . "</option>\n";
        }
        return $result;
    }    
    
    
	public static function indiceTitoli($titolo){
        // trasforma un titolo per l'indice
        $titolo = trim($titolo);
        if(empty($titolo)) return $titolo;
        $articoli = "/IL /LA /LO /L'/I /GLI /GL'/LI /LE /UN /UNA /UNO /UN'/LES /DES /UNE /O /OS /THE /A /AN /LOS /LAS /EL /DER /DIE /DAS /";

        // toglie i diacritici
        $titolo = iconv('UTF-8', 'ASCII//TRANSLIT', $titolo);

        $titolo = strtoupper($titolo);

        // elimina l'articolo
        // articolo con apostrofo iniziale
        if(substr($titolo, 0, 1) == "'"){
            if(($n = strpos($titolo, " ")) === false){
            }else{
                $titolo = substr($titolo, $n + 1);
            }
        }
        // toglie parte iniziale non alfabetica
        while(true){
            if(strpos(".;,:?!-/()[]«»\"", substr($titolo, 0, 1)) === false){
                break;
            }else{
                $titolo = substr($titolo, 1);
            }
        }

        // toglie articolo
        $n = strpos($titolo, " ");
        $n1 = strpos($titolo, "'");
        if($n1 === false) $n1 = 10000;
        if($n === false) $n = 10000;
        if($n > $n1) $n = $n1;
        if($n < 10000){
            $stringa1 = "/" . substr($titolo, 0, $n + 1) . "/";
            $n1 = strpos($articoli, $stringa1);
            if($n1 === false){
            }else{
                $titolo = substr($titolo, $n + 1);
            }
        }
        // toglie tutti i segni non alfanumerici, spazi compresi, tranne la punteggiatura
        $titolo = preg_replace("/[^A-Z0-9\.\;\:\,\!\?-]/", "", $titolo);
        $iniziale = substr($titolo, 0, 1);

        switch ($iniziale) {
            case '1':
                $ct = 0;
                for($i = 0; $i < strlen($titolo); $i++){
                    if(!is_numeric(substr($titolo, $i, 1))){
                        break;
                    }
                    $ct ++;
                }
                if($ct == 1){
                    $titolo = "UN" . substr($titolo, 1);
                }else if($ct == 2){
                    $n = substr($titolo, 0, 2);
                    if($n == 10 || $n == 12 || $n > 16){
                        $titolo = "D" . substr($titolo, 1);
                    }else if($n == 11){
                        $titolo = "UNDICI" . substr($titolo, 1);
                    }else if($n == 13){
                        $titolo = "TREDICI" . substr($titolo, 1);
                    }else if($n == 14 || $n == 15){
                        $titolo = "QU" . substr($titolo, 1);
                    }else if($n == 16){
                        $titolo = "SEDICI" . substr($titolo, 1);
                    }
                }else if($ct == 3){
                    $titolo = "CENTO" . substr($titolo, 1);
                }else if($ct == 4){
                    $titolo = "MILLE" . substr($titolo, 1);
                }
                break;
            case '2':
                $titolo = "DUE" . substr($titolo, 1);
                break;
            case '3':
                $titolo = "TRE" . substr($titolo, 1);
                break;
            case '4':
                $titolo = "QUATTRO" . substr($titolo, 1);
                break;
            case '5':
                $titolo = "CINQUE" . substr($titolo, 1);
                break;
            case '6':
                $titolo = "SEI" . substr($titolo, 1);
                break;
            case '7':
                $titolo = "SETTE" . substr($titolo, 1);
                break;
            case '8':
                $titolo = "OTTO" . substr($titolo, 1);
                break;
            case '9':
                $titolo = "NOVE" . substr($titolo, 1);
                break;

            default:
                break;
        }

        if(strlen($titolo) > 100) $titolo = substr($titolo, 0, 100);
        return $titolo;
    }
    public static function indiceNomi($nome, $lunghezza = 100){
        $nome = trim($nome);
        // toglie i diacritici
        $nome = iconv('UTF-8', 'ASCII//TRANSLIT', $nome);
        $nome = strtoupper($nome);
        // toglie tutti i segni non alfabetici, spazi compresi
        $nome = preg_replace("/[^A-Z]/", "", $nome);
        if(strlen($nome) > $lunghezza) $nome = substr($nome, 0, $lunghezza);
        return $nome;
    }

    public static function chkEmail($email){
		// elimino spazi, "a capo" e altro alle estremità della stringa
		$email = trim($email);
		// se la stringa è vuota sicuramente non è una mail
		if(!$email) {
			return false;
		}
		// controllo che ci sia una sola @ nella stringa
		$num_at = count(explode( '@', $email )) - 1;
		if($num_at != 1) {
			return false;
		}
		// controllo la presenza di ulteriori caratteri "pericolosi":
		if(strpos($email,';') || strpos($email,',') || strpos($email,' ')) {
			return false;
		}
		// la stringa rispetta il formato classico di una mail?
		if(!preg_match( '/^[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}$/', $email)) {
			return false;
		}
		return true;
    }
    public static function moveRecord($DbTable, $rows, $verso, $ID, $base, $idName = "ID"){
        // Funzione generale per lo spostamento di un record in un gruppo, definito dal campo 'ordinale'.
        // Argomenti:
        // $DbTable: oggetto di classe Application_Model_DbTable_
        // $rows: recordset fornito dal precedente, ordinato per 'ordinale'
        // $verso: -1=sposta verso l'alto 1=sposta verso il basso
        // $ID: id del record da spostare
        // $base: 0 | 1
        // $idName: nome del campo ID
        // Preliminarmente si rinumerano tutti ordinatamente (non si sa mai)
        foreach ($rows as $key => $value) {
            $data = array();
            $data["ordinale"] = ($key + $base);
            $where = $DbTable->getAdapter()->quoteInto($idName . " = ?", $value[$idName]);
            $DbTable->update($data, $where);
        }
        foreach ($rows as $key => $value) {
            if($value[$idName] == $ID){
                if($verso == 1 && $key == count($rows) - 1){
                    // è già l'ultimo, non si fa nulla
                }else if($verso == -1 && $key == 0){
                    // è già il primo, non si fa nulla
                }else{
                    // seguente o precedente
                    $data = array();
                    $data["ordinale"] = $key + $base;        
                    $where = $DbTable->getAdapter()->quoteInto($idName . " = ?", $rows[$key + $verso][$idName]);
                    $DbTable->update($data, $where);
                    $data = array();
                    $data["ordinale"] = $key + $base + $verso;
                    $where = $DbTable->getAdapter()->quoteInto($idName . " = ?", $value[$idName]);
                    $DbTable->update($data, $where);
                }
                break;
            }
        }
    }    
    
    public static function trattaNomePerIndice($nome){
        // se una parola componente di un nome è più breve di 4 caratteri, viene incrementata con XXX, in maniera che non sfugga
        // all'indicizzazione fulltext di MySql, che si ferma ai 4 caratteri;
        // di norma si fa solo per i nomi propri; ovviamente occorre tenerne conto in ricerca
        $array = explode(" ", $nome);
        foreach ($array as &$value) {
            if(strlen($value) < 4 && strlen($value) > 0){
                $value = $value . "XXX";
                $value = substr($value, 0, 4);
            }
        }
        $nome = implode(" ", $array);
        return $nome;
    }
    public static function controllaApostrofoPerIndice($stringa){
        // L'indicizzazione fulltext di MySql tratta le due parole con apostrofo in mezzo come una sola parola;
        // questo va bene, ma esclude la possibilità di trovare le singole parole, in particolare la seconda,
        // che interessa di più; per ovviare al problema la seconda parola viene ripetuta
        $array = explode(" ", $stringa);
        foreach ($array as &$value) {
            if(substr($value, 0, 1) == "'") $value = substr($value, 1);
            if(substr($value, strlen($value - 1)) == "'") $value = substr($value, 0, strlen($value - 1));
            $tmp = explode("'", $value);
            if(count($tmp) > 1){
                $value .= " " . $tmp[1];
            }
        }
        $result = implode(" ", $array);
        return $result;
    }
    
     public static function rmdir_recursive($dir) {
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir($dir . '/' .$file)){
                self::rmdir_recursive($dir. '/' .$file);
            }else{
                unlink($dir.'/'.$file);
            }
        }
        rmdir($dir);
    }    
    public static function ordutf8($string, &$offset) {
    	// senza ricorrere alle funzioni mb restituisce il codice unicode di ciascun carattere;
    	// $offset in ingresso corrisponde alla posizione iniziale del carattere, in uscita
    	// a quella del carattere successivo, per cui la differenza è la lunghezza in bytes del carattere 
        $code = ord(substr($string, $offset,1));
        if ($code >= 128) {        //otherwise 0xxxxxxx
            if ($code < 224) $bytesnumber = 2;                //110xxxxx
            else if ($code < 240) $bytesnumber = 3;        //1110xxxx
            else if ($code < 248) $bytesnumber = 4;    //11110xxx
            $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
            for ($i = 2; $i <= $bytesnumber; $i++) {
                $offset ++;
                $code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
                $codetemp = $codetemp*64 + $code2;
            }
            $code = $codetemp;
        }
        $offset += 1;
        if ($offset >= strlen($string)) $offset = -1;
        return $code;
    }
    
    
	public static function getCampiPerInput($infoDB, $row){    
		// nei campi varchar o tinytext di un record sostituisce virgolette con entità html: questo per impedire che nel value="" dell'elemento <input>
		// la stringa sia troncata all'altezza delle prime virgolette
		// $infoDB = array di metadati ottenuti in Zend con dbTable->info()
		// $row = array contenente i campi del record
        foreach ($infoDB["metadata"] as $key => $value) {
            if($value["DATA_TYPE"] == "tinytext" || $value["DATA_TYPE"] == "varchar"){
                $row[$key] = str_replace("\"", "&quot;", $row[$key]);
            }
        }
        return $row;
    }
    
    public static function getDimensioneCampi($infoDB){
		$result = array();
        foreach ($infoDB["metadata"] as $key => $value) {
            if($value["DATA_TYPE"] == "tinytext"){
                $result[$value["COLUMN_NAME"]] = 265;
            }else if($value["DATA_TYPE"] == "char" || $value["DATA_TYPE"] == "varchar"){
                $result[$value["COLUMN_NAME"]] = $value["LENGTH"];
            }
        }
		return $result;
    }
    
    public static function traslittera($stringa){
         // traslittera greco, per l'indice

        $offset = 0;
        $prOffset = 0;
        $n1 = 0;
        $result = "";
        $k = false;
        while ($offset >= 0) {
            $prOffset = $offset;
            $code = self::ordutf8($stringa, $offset);
            if($code >= 900){
                $k = true;
                $result .= substr($stringa, $n1, ($prOffset - $n1)) . self::getLatino($code);
                $n1 = $offset;
            }
        }
        if($n1 >= 0) $result .= substr($stringa, $n1);
        return $result;
     }

     private static function getLatino($code){
         // traslittera greco in latino
        switch($code){
            case 946:
                return "B";
            case 947:
                return "G";
            case 948:
                return "D";
            case 950:
                return "Z";
            case 952:
                return "TH";
            case 954:
                return "K";
            case 955:
                return "L";
            case 956:
                return "M";
            case 957:
                return "N";
            case 958:
                return "X";
            case 960:
                return "P";
            case 962:
                return "S";
            case 963:
                return "S";
            case 964:
                return "T";
            case 966:
                return "F";
            case 967:
                return "CH";
            case 968:
                return "PS";
            case 914:
                return "B";
            case 915:
                return "G";
            case 916:
                return "D";
            case 918:
                return "Z";
            case 920:
                return "TH";
            case 922:
                return "K";
            case 923:
                return "L";
            case 924:
                return "M";
            case 925:
                return "N";
            case 926:
                return "X";
            case 928:
                return "P";
            case 931:
                return "S";
            case 932:
                return "T";
            case 934:
                return "F";
            case 935:
                return "CH";
            case 936:
                return "PS";
        }
        $code = "/" . $code . "/";

        $stringa = "/940/945/7936/7937/8048/8049/8118/7938/7939/7940/7941/7942/7943/8115/8064/8065/8114/8116/8119/8066/8067"
                . "/8068/8069/8070/8071/913/7944/7945/7946/7947/7948/7949/7950/7951/";
        if(strpos($stringa, $code) === false){
        }else{
            return "A";
        }
        $stringa = "/941/949/7952/7953/8050/8051/7954/7955/7956/7957/917/7960/7961/7962/7963/7964/7965/";
        if(strpos($stringa, $code) === false){
        }else{
            return "E";
        }
        $stringa = "/942/951/7968/7969/8052/8053/8134/7970/7971/7972/7973/7974/7975/8131/8080/8081/8130/8132/8135/8082/8083/8084"
                . "/8085/8086/8087/919/7976/7977/7978/7979/7980/7981/7982/7983/";
        if(strpos($stringa, $code) === false){
        }else{
            return "E";
        }
        $stringa = "/943/953/7984/7985/8054/8055/8150/7986/7987/7988/7989/7990/7991/921/7992/7993/7994/7995/7996/7997/7998/7999";
        if(strpos($stringa, $code) === false){
        }else{
            return "I";
        }
        $stringa = "/972/959/8000/8001/8056/8057/8002/8003/8004/8005/927/8008/8009/8010/8011/8012/8013/";
        if(strpos($stringa, $code) === false){
        }else{
            return "O";
        }
        $stringa = "/973/965/8016/8017/8058/8059/8166/8018/8019/8020/8021/8022/8023/933/8025/8027/8029/8031/";
        if(strpos($stringa, $code) === false){
        }else{
            return "U";
        }
        $stringa = "/974/969/8032/8033/8060/8061/8182/8034/8035/8036/8037/8038/8039/937/8040/8041/8042/8043/8044/8045/8046/8047"
                . "/8179/8096/8097/8178/8180/8183/8098/8099/8100/8101/8102/8103/";
        if(strpos($stringa, $code) === false){
        }else{
            return "O";
        }
        $stringa = "/961/8164/8165/929/8172/";
        if(strpos($stringa, $code) === false){
        }else{
            return "R";
        }
        return "";
     }
     
     ////////////////////////////////////////////////////////////////////////////////////////
     // Questo e il metodo successivo eseguono l'evidenziazione dopo una ricerca;
     // a prescindere dal tipo di ricerca (con o senza omologazione dei diacritici)
     // qui l'omologazione viene fatta comunque, perché altrimenti non funziona con gli accentati e simili
     ////////////////////////////////////////////////////////////////////////////////////////
    public static function evidenzia($testo, $pattern, $campiPattern, $campo, $modoIconv = "TRANSLIT"){
    	// $testo = testo da evidenziare
    	// $pattern: array delle chiavi
    	// $campiPattern: array contenente i nomi dei campi interessati dalla ricerca
    	// $campo: il nome del campo che contiene il testo
    	// $modoIconv: definisce le modalità di standardizzazione in presenza di caratteri non trattabili:
    	// il TRANSLIT di default va bene perché permette la conversione degli accentati ecc. nelle forme senza diacritico;
    	// purtroppo però, se incontra caratteri che non riesce a traslitterare, restituisce una stringa vuota e pure un notice;
    	// l'alternativa è IGNORE, che previene l'errore, ma fa ignorare anche tutti i caratteri traslitterabili.
    	// Il campo per la ricerca full-text, che perciò va evidenziata sempre, deve chiamarsi "chiave_full"
		// la parola evidenziata è marcata con <span class=\"evi\">    	
		
		// si identificano subito i casi in cui non si deve evidenziare
		$k = false;		
        for($i = 0; $i < count($pattern); $i ++){
//error_log($campiPattern[$i]);         
            if($campiPattern[$i] == $campo || $campiPattern[$i] == "chiave_full"){
                // si evidenzia solo nel campo selezionato  o se la chiave è in full-text)
                $k = true;
                break;
            }
        }		
		if(!$k) return $testo;
        $originale = $testo;
        $originale = str_replace("’", "'", $originale);
        $standard = str_replace("-", " ", $testo);

        // prima di sostituire gli apostrofi, si individua eventuale presenza di apostrofo nella chiave;
        // in tal caso si individuano le occorrenze e si sostituisce apostrofo con _
        foreach ($pattern as $key => $value) {
            $pattern[$key] = self::standardizza($pattern[$key]);	// la chiave viene standardizzata, perciò si tolgono accenti e simili 
            if(strpos($value, "_")){
                $str = str_replace("_", "'", $value);
                while($n = stripos($originale, $str)){
                    $nn = strpos($originale, "'", $n);
                    $originale = substr($originale, 0, $nn) . "_" . substr($originale, $nn + 1);
                    $standard = substr($standard, 0, $nn) . "_" . substr($standard, $nn + 1);
                }
            }
        }

        $standard = str_replace("’", "'", $standard);
        $standard = str_replace("'", " ", $standard);
        $standard = strtoupper($standard);
        $standard = self::standardizza($standard, $modoIconv);
        $arrayStandard = explode(" ", $standard);
        $originale = str_replace("-", "-# ", $originale);
        $originale = str_replace("'", "'# ", $originale);
        $originale = str_replace("_", "'", $originale);
        $arrayOriginale = explode(" ", $originale);
        $evidenziate = array();
           

        for($i = 0; $i < count($pattern); $i ++){
            if($campiPattern[$i] == $campo || $campiPattern[$i] == "chiave_full"){
                // si evidenzia solo nel campo selezionato  o se la chiave è in full-text)
                $array = array_keys ($arrayStandard, $pattern[$i]);
                $evidenziate = array_merge($evidenziate, $array);
            }
        }
        foreach ($evidenziate as $value) {
            $prima = "";
            $dopo = "";
            $stringa = $arrayOriginale[$value];
            for($i = 0; $i < strlen($stringa); $i ++){
                if(self::isAlpha(substr($stringa, $i, 1))){
                    $stringa = substr($stringa, $i);
                    break;
                }
                $prima .= substr($stringa, $i, 1);
            }
            for($i = strlen($stringa); $i > 0; $i --){
                if(self::isAlpha(substr($stringa, $i, 1))){
                    $stringa = substr($stringa, 0, $i + 1);
                    break;
                }
                $dopo = substr($stringa, $i, 1) . $dopo;
            }
            $arrayOriginale[$value] = $prima . "<span class=\"evi\">" . $stringa . "</span>" . $dopo;
        }
        $result = "";
        foreach ($arrayOriginale as $key => $value) {
        	if($key > 0) $result .= " ";
            $result .= $value;
        }
    	$result = str_replace("-# ", "-", $result);
        $result = str_replace("'# ", "'", $result);
        return $result;
    }
    public static function standardizza($testo, $modoIconv = "TRANSLIT"){
        // $modoIconv TRANSLIT o IGNORE; uso il secondo nel caso di testo greco, altrimenti va in errore:
        // TRANSLIT cerca un equivalente (anche in combinazione, per esempio  à -> `a); se non lo trova, per esempio in caso di greco, produce un notice e comunque restituisce una stringa vuota
        // IGNORE se trova un carattere non ASCII semplicemente lo salta
        $testo = trim($testo);
        $tmp = explode(" ", $testo);
        $testo = "";
        // si fa parola per parola, altrimenti parole prive di caratteri alfabetici vengono eliminate
        foreach ($tmp as $value) {
        	$value = iconv('UTF-8', 'ASCII//' . $modoIconv, $value);
        	$value = strtoupper($value);
            if(!empty($testo)) $testo .= " ";
            $value = preg_replace("/[^A-Z\ \_]/", "", $value);
            if(empty($value)){
                $value = "*";
            }
            $testo .= trim($value);
        }
        return $testo;
    }
    public static function getColorHex($red, $green, $blue){
        // restituisce un colore in formato esadecimale, fornendo i valori r g b 
        $ritorno = "";
        $ritorno .= self::right("00" . dechex($red), 2);
        $ritorno .= self::right("00" . dechex($green), 2);
        $ritorno .= self::right("00" . dechex($blue), 2);
        return $ritorno;
    }
    static public function charCount($stringa, $carattere){
		$result = 0;
		$n = 0;
		$n1 = 0;
		if(!empty($stringa) && !empty($carattere)){
            while(true){
            	$n = strpos($stringa, $carattere, $n1);
            	if($n === false) break;
                $n1 ++;
                $result ++;
            }
		}
		return $result;
    }
    public static function getUnicode($codice){
    	// restituisce un carattere unicode inserendo il codice in formato \uXXXX
    	return json_decode(sprintf('"%s"', $codice));
    }
    
    /*public static function mb_str_split($stringa){
    	// trasforma una stringa in un array di caratteri
    	// sostituisce str_split nel caso di caratteri multibyte
    	return preg_split('//u', $stringa, -1, PREG_SPLIT_NO_EMPTY);
    }*/
    
    public static function mb_str_split($stringa){
    	// trasforma una stringa in un array di caratteri
    	$len = mb_strlen($stringa);
    	$result = array();
    	for ($i=0; $i<$len; $i++){
    		$result[] = mb_substr($stringa, $i, 1);
    		}
    	return $result;
    }
    
    public static function tronca($stringa, $len){
    	// tronca una stringa per la lunghezza $len, senza spezzare le parole
    	if(mb_strlen($stringa) <= $len) return $stringa;
    	$len = $len - 3;
    	$result = mb_substr($stringa, 0, $len);
    	/*
    	if(ctype_alpha(mb_substr($stringa, $len, 1))){ 
    	}else{
    		$n = mb_strrpos($result, " ");
    		if($n !== false){
    			$result = mb_substr($result, 0, $n);
    		}
    	}
    	*/
    	return $result . "...";
    }

    
    
}
