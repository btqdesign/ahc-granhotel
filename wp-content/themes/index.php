
<?php
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
switch ($lang){
    case "fr":
        //echo "PAGE FR";
        include("https://www.http://granhoteldelaciudaddemexico.com.mx/en/");//include check session FR
        break;
    case "en":
        //echo "PAGE EN";
        include("https://www.http://granhoteldelaciudaddemexico.com.mx/en/");
        break;        
    default:
        //echo "PAGE ES - Setting Default";
        include("https://www.http://granhoteldelaciudaddemexico.com.mx/es/");//include EN in all other cases of different lang detection
        break;
}
?>