<?php

class Application_Model_Segmento
{
    private $valore = "";
    private $parole = array();



    function __construct($valore, $db, $vocabolario){//un segmento

        $this->valore = $valore;
        $this->elabora($db, $vocabolario);//richiamare il metodo elabora su oggetto
    }

    public function getParole(){
    	return $this->parole;
        }

   public function getValore(){
    	return $this->valore;
        }
        
    public function getPorzione(){ //frammento utile al mostra report!
    	$n=50;
        if(mb_strlen($this->valore) < $n){
            return $this->valore;
        } else {
            $result = mb_substr($this->valore, mb_strlen($this->valore)-$n);
            $n= mb_strpos($result, " ");
            return mb_substr($result, $n+1);
        }
   }
            

    public function getUltimaP(){
        return $this->parole[count($this->parole)-1];
    }

    public function getPenultimaP(){
        return $this->parole[count($this->parole)-2];
    }

    private function elabora($db, $vocabolario){
        //crea oggetto segmento in grado di interfacciarsi con db

        $value = $this->valore;
        $value = cancPunt($value);

        //echo $value . "<br/>";
        $array = explode(" ", $value);
        //array_filter elimina array vuoti, vd: http://www.miniscript.it/articoli/56/filtrare_gli_array_con_array_filter_e_array_map.html
        $filtrato = array_filter($array, 'strlen');
        $array = array();
        foreach ($filtrato as $value){
                $array[] = $value;
        }
        $this->parole = array();
        
 // considera solo le parole utili a contare almeno 8 sillabe partendo dal punto.
        $conta = 0;
        $n=count($array);

        for ($i=$n-1; $i >= 0; $i--){ //mando i valori a parola dall'ultimo fino a quando serve!!
            
                $this->parole[$i]= new Application_Model_Parola($array[$i], $db, $vocabolario);

                if ($i==$n-1){
                    $ultima = $this->parole[$i]->getUltima();
                    if ($ultima->getQuantita()=="*"){
                        $ultima->setQuantita("x");
                    }

                }
                //contare qui parole accentate utili??
                $conta += $this->parole[$i]->getContaSill();
                

                if (($this->parole[$i]->isAccentata()===true) &&
                    ($this->parole[$i]->getContaSill()>(6) || $this->parole[$i]->getContaSill()>(7) || $this->parole[$i]->getContaSill()>(8))){
                   $conta = $conta;
                }else if ($conta > 6){
                    break;
                }
                
            }//ricompongo le parole dalla prima utile al punto fermo:
       $this->parole = array_reverse($this->parole);

       for ($i=0; $i< (count ($this->parole)-1); $i++){

           $sillabe = $this->parole[$i]->getSillabe();
//accento bisillabi o monosillabi////
           if (count($sillabe) < 2){
               $sillabe[0]->setAccento(true);
           }
//controllo sinalefe considerando la prima e l'ultima sillaba delle parole
           $ultima = $this->parole[$i]->getUltima();
           $prima = $this->parole[$i+1]->getPrima();
           $finale = substr($ultima->getValore(), strlen($ultima->getValore())-1);
           $iniziale = substr($prima->getValore(), 0, 1);
           //caso sinalefe
           if ((($finale === "M") || Application_Model_Start::is_Vocale($finale)) && Application_Model_Start::is_Vocale($iniziale)){
               $ultima->setFenomeno("S");
               //$ultima->setQuantita("");//in sinalefe il valore prosodico dell'ultima sillaba è annullato
           }
           if (!Application_Model_Start::is_Vocale($finale) && !Application_Model_Start::is_Vocale($iniziale)){
               $ultima->setQuantita("+");//nella catena sillabica due consonanti consecutive chiudono la vocale precedente
           }
           //assegna ritmica a parola!
           if (count($sillabe) > 2){
               $penultima = $this->parole[$i]->getPenultima();
               $terzultima = $this->parole[$i]->getTerzultima();
               if ($penultima->getQuantita() === "+"){
                   $penultima->setAccento(true);
               } else{
                   $terzultima->setAccento(true);
               }
           }
           
       }

    }//finisce metodo elabora

}

