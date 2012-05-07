<?/*if(!$_SESSION["usuario"])
{
echo "No tienes permiso para estar aqui.<br>";
echo "<a href=http://".$_SERVER['SERVER_NAME']."/iesgn/index.php>Volver</a>";
die("");
}*/
function permisos($mod,$p="*",$error=0)
{
$perm=array();
$sql="select * from Permisos where Perfil='".$_SESSION["perfil"]."' and Modulo='".$mod."'";
$result=mysql_query($sql);
if($row=mysql_fetch_array($result))
{
	$perm=explode(",",$row["Permisos"]);
}
else {if($_SESSION["perfil"]) return permisos("General",$p,$error);}


if ($perm[0]=="" && $p=="*")
{
	echo "No puedes ver esta página<br>";
	echo "<a href=http://".$_SERVER['SERVER_NAME']."/iesgn/index.php>Volver</a>";
	die("");
}

if($error==1 && !in_array($p,$perm))
{
	echo "No puedes realizar esa operación.<br>";
	echo "<a href=http://".$_SERVER['SERVER_NAME']."/iesgn/index.php>Volver</a>";
	die("");
}

return (in_array($p,$perm));
}
?>