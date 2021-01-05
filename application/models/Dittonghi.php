<?php

class Application_Model_Dittonghi
{
    public static $pstmt = null;
    public static $nodittongo = "";
    public static $standard = "";
    public static function init ($db){
        self::$pstmt = $db->prepare("SELECT standard FROM nondittonghi WHERE word = ?");
        //definisce il tipo stringa e nome della variabile 'provvisoria'
        self::$pstmt->bind_param('s', self::$nodittongo);
        self::$pstmt->bind_result(self::$standard);
        }
    public static function close (){
           self::$pstmt->close();
    }

}

