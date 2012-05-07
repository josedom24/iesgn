<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");


echo "<center>";
echo "<h1>Centro de Gastos</h1>";


$sql="select * from CentroGastos";
$result=mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
echo "<table border=\"1\">";
while($row=mysql_fetch_array($result))
{
	echo "<tr>";
	echo "<td>".$row["Abr"]."</td>";
	echo "<td>".$row["Departamento"]."</td>";
	echo "<td>".$row["Fotocopias"]."</td>";
	
	echo "<td><a href=\"modificarcg.php?id=".$row["Id"]."\">".imagen("mod.gif")."</a></td>";
	
	
	echo "</tr>";
}
echo "</table><br><br>";
echo "<a href=\"addcg.php\">Nuevo Centro de Gasto</a>";
echo "<br><br></center>";
//echo imagen("add_user.png")."<a href=\"registro.php\">Insertar nuevo</a>";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");


?>