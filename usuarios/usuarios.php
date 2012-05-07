<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");

$sql="select * from usuarios";
$result=mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
echo "<h1>Usuarios</h1>";
echo "<table border=\"1\">";
while($row=mysql_fetch_array($result))
{
	echo "<tr>";
	echo "<td>".$row["Usuario"]."</td>";
	echo "<td>".$row["Nombre"]."</td>";
	echo "<td>".$row["Perfil"]."</td>";
	echo "<td>".$row["Email"]."</td>";
			echo "<td><a href=\"modificarusuario.php?id=".$row["Usuario"]."\">".imagen("edit_user.png")."</a></td>";
		echo "<td><a href=\"eliminarusuario.php?id=".$row["Usuario"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar a ".$row["Nombre"]."?')\">".imagen("remove_user.png")."</a></td>";
	
	echo "</tr>";
}
echo "</table><br><br>";
echo imagen("add_user.png")."<a href=\"registro.php\">Insertar nuevo</a>";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");


?>
	
