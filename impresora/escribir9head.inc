<?
if(!$_GET["tipo"]) $tipo=$_POST["tipo"]; else $tipo=$_GET["tipo"];
if($tipo=="") $tipo="e";


if ($tipo=="e") 
fputs($fic,"Registro de Entrada");
else
fputs($fic,"Registro de Salida");


?>

