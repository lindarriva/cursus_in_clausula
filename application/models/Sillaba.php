<?php

class Application_Model_Sillaba
{
    private $valore = ""; //valore iniziale
    private $standard = ""; //il valore con "significante"
    private $posizione = ""; //quando è sinalefe # ; a=inizio; z=fine; s=interna
    private $isUltima = false;
    private $quantita = "*"; //+=lunga -=breve *=non si sa
    private $isAccento = false;
    private $fenomeno = "";//individua fenomeni prosodici tipo sinalefe (S), aferesi (A) ...


    public function  setValore($stringa){
        $this->valore = $stringa;
    }
    public function getValore(){
        return $this->valore;
    }

    public function setStandard($stringa){
        $this->standard = $stringa;
    }
    public function getStandard(){
        return $this->standard;
    }

    public function setPos($n){
        $this->posizione = $n;
    }
    public function getPos(){
        return $this->posizione;
    }

    public function setUltima($value){
        $this->isUltima = $value;
    }

    public function isUltima(){
            return $this->isUltima;
        }

    public function setQuantita($value){
        $this->quantita = $value;
    }
    public function getQuantita(){
        return $this->quantita;
    }

    public function setAccento($value){
        $this->isAccento = $value;
    }
    public function isAccento(){
        return $this->isAccento;
    }

    public function setFenomeno($value){
        $this->fenomeno = $value;
    }

    public function getFenomeno(){
        return $this->fenomeno;
    }

    public function setisEnclitica($value){
        $this->isenclitica = $value;
    }

    public function isEnclitica(){
        return $this->isenclitica;
    }
}

