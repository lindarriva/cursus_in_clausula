caricamento in corso<br>
<?php



    
        $mysqli = new mysqli("localhost", "cursus", "cursus", "cursus");
        $mysqli->set_charset("utf8");
        set_time_limit (10000);

        $path = "uploads/temi.sql";

        $f = fopen($path, "r");

        $n = filesize($path);
        error_log('il file =' . $n);
        $testo = fread($f, $n);
            $riga = explode("\n",$testo);
            foreach ($riga as $value){
                if (substr($value, 0,6) ==="INSERT"){
                    continue;
                }
                $stringa = "INSERT INTO temi VALUES " . substr($value, 0, strlen($value)-1);
                
               if(!$mysqli->query($stringa)){
             error_log($mysqli->error);
        	}
            }

            	
           
        $path = "uploads/lemmi.sql";

        $f = fopen($path, "r");
        $n = filesize($path);
        error_log('il file =' . $n);
        
        $testo = fread($f, $n);
            $riga = explode("\n",$testo);
            foreach ($riga as $value){
                if (substr($value, 0,6) ==="INSERT"){
                    continue;
                }
                $stringa = "INSERT INTO lemmi VALUES " . substr($value, 0, strlen($value)-1);
                
               if(!$mysqli->query($stringa)){
             error_log($mysqli->error);
        	}
            }
              
            
        fclose($f);
        $mysqli->close();
        set_time_limit (120);
        
        ?>
    
    

