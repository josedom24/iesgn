<?if($_SESSION["perfil"]!="a")
{
echo "No tienes permiso para estar aqui.<br>";
echo "<a href=http://".$_SERVER['SERVER_NAME']."/iesgn/index.php>Volver</a>";
die("");
}?>