<?php


class Menu{
    private $pagina = "";
    private $baseUrl = "";


    function __construct($pagina, $baseUrl){
        $this->pagina = $pagina;
        $this->baseUrl = $baseUrl;
        $this->render();
    }

    private function render(){
        $disabled = true;
        $userName = "";
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $disabled = false;
            $accessi = new Application_Model_Accessi();
            $userName = $accessi->getUserName();
        }

        echo "<script type=\"text/javascript\">\n";
        echo "<!--\n";
        echo "var menuAnalisi = new Array();\n";
        echo "menuAnalisi[0] = '<a href=\"" . $this->baseUrl . "/analisi/frequenze\">Frequenze</a>';\n";
        echo "menuAnalisi[1] = '<a href=\"" . $this->baseUrl . "/analisi/confronti\">Confronti</a>';\n";
        echo "menuAnalisi[2] = '<a href=\"" . $this->baseUrl . "/analisi/locuzioni\">Locuzioni</a>';\n";
        echo "var menuTemi = new Array();\n";
        echo "menuTemi[0] = '<a href=\"" . $this->baseUrl . "/temi/ricerca\">Ricerca</a>';\n";
        echo "menuTemi[1] = '<a href=\"" . $this->baseUrl . "/temi/aggiorna\">Aggiorna albero</a>';\n";
        echo "-->\n";
        echo "</script>\n";

        echo "<div id=\"divTopMenu\">\n";

        echo "<table class=\"topMenu\"";
        if(!$disabled) echo " width=\"100%\"";
        echo " cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
        echo "<tr>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";

        echo "<td class=\"menuItem " . $this->getClasse("home") . "\"
            onclick=\"location.href='" . $this->baseUrl . "/index/index'\">HOME</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";


        echo "<td class=\"menuItem " . $this->getClasse("testi");
        if($disabled){
            echo " disabled";
        }else{
            echo "\" onclick=\"location.href='" . $this->baseUrl . "/testi/testi'";
        }
        echo "\">ARCHIVIO&nbsp;TESTI</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";

        /*echo "<td class=\"menuItem ". $this->getClasse("analizza");
        if($disabled){
            echo " disabled";
        }else{
            echo "\" onclick=\"return clickreturnvalue()\" onmouseover=\"dropdownmenu(this, event, menuAnalisi, '150px')\" onmouseout=\"delayhidemenu()";
        }
        echo "\">ANALIZZA</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";*/


        echo "<td class=\"menuItem ". $this->getClasse("analisi");
        if($disabled){
            echo " disabled";
        }else{
            echo "\" onclick=\"location.href='" . $this->baseUrl . "/analisi/analisi'";
        }
        echo "\">ANALISI</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";

        echo "<td class=\"menuItem ". $this->getClasse("singulariter");
        if($disabled){
            echo " disabled";
        }else{
            echo "\" onclick=\"location.href='" . $this->baseUrl . "/singulariter/singulariter'";
        }
        echo "\">SINGULARITER</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";

        echo "<td class=\"menuItem ". $this->getClasse("bibliografia");
        if($disabled){
            echo " disabled";
        }else{
            echo "\" onclick=\"location.href='" . $this->baseUrl . "/bibliografia/bibliografia'";
        }
        echo "\">BIBLIOGRAFIA</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";

        echo "<td class=\"menuItem " . $this->getClasse("logout");
        if($disabled){
            echo " disabled";
        }else{
            echo "\" onclick=\"location.href='" . $this->baseUrl . "/index/logout'";
        }
        echo "\">ESCI</td>\n";
        echo "<td class=\"menuVuoto\">&nbsp;</td>\n";

        
        echo "<td width=\"70%\" align=\"right\" class=\"right\">";
        echo "<table><tr><td>";
       if(!$disabled){
            echo "<img src=\"" . $this->baseUrl . "/img/avatar.png\" />";
       } else {
            echo "&nbsp";
       }
        echo "</td><td style=\"color:#666\">";
        echo $userName;
        echo "</td></tr></table></td>";
        

        echo "</tr>\n</table>\n";
        echo "</div>\n";


        
    }

    private function getClasse($pagina){
        if($this->pagina == $pagina){
            return "topScelto";
        }else{
            return "normale";
        }
    }


}




?>
