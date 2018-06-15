
<?php
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
switch ($lang){
    case "fr":
        //echo "PAGE FR";
        include("index_fr.php");//include check session FR
        break;
    case "it":
        //echo "PAGE IT";
        include("index_it.php");
        break;
    case "en":
        //echo "PAGE EN";
        include("index_en.php");
        break;        
    default:
        //echo "PAGE EN - Setting Default";
        include("index_en.php");//include EN in all other cases of different lang detection
        break;
}
?>