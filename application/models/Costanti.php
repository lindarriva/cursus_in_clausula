<?php

class Application_Model_Costanti
{

    private static $prefissi = array ("AD","ADIN", "AB","ANIMAD","CIRCUM","CON","DIS",
        "DISCON","IN","INTER[IO;IU]","OB","PER","PRAETER","PROPTER","SUB","SUBTER","SUPER[IO;IU]","SUPERIN","TRANS");
    public static function getPrefissi(){
        return self::$prefissi;
    }
    private static $desinenze_perfetto = array ("ISTI","IT","IMUS","ISTIS","ERUNT","ERE","ERAM","ERAS",
        "ERAT","ERAMUS","ERATIS","ERANT","ERO","ERIS","ERIT","ERIMUS","ERITIS","ERINT",
        "ERIM","ISSEM","ISSES","ISSET","ISSEMUS","ISSETIS","ISSENT","ISSE");
    public static function getDesinenze(){
        return self::$desinenze_perfetto;
    }
    private static $semivocale = array ("ABSOLUI","DISSOLUI","EXOLUI", "EXSOLUI","PERSOLUI","RESOLUI",
        "SOLUI","ADUOLUI","SUBUOLUI","CIRCUMUOLUI","CONUOLUI","DEUOLUI","EUOLUI","INUOLUI",
        "OBUOLUI","PROUOLUI","REUOLUI","TRANSUOLUI","FERUI","EFFERUI","DEFERUI");
    public static function getSemivocale(){
        return self::$semivocale;
    }
    private static $omografi = array ("UOLUI");
    public static function getOmografi(){
        return self::$omografi;
    }
    private static $certe = array ("IBUS"=>"--", "ARE"=>"+-", "IRE"=>"+-", "ERE"=>"*-", "IUNT"=>"-+", "EBAT"=>"+-",
            "BAMUS"=>"+-", "EBATIS"=>"++-", "EBANT"=>"++", "ERINT"=>"-+", "ISSE"=>"+-", "ATUR"=>"+-",
            "AMUR"=>"+-","ANTUR"=>"+-", "BITUR"=>"--", "BIMUR"=>"--", "BUNTUR"=>"+-", "ETUR"=>"+-",
            "EMUR"=>"+-", "ENTUR"=>"+-", "UNTUR"=>"+-");
    public static function getCerte(){
        return self::$certe;
    }
}

