<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("secretaria","A",1);
$menu="datossecretaria";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");

echo "<center><h1>";
if($_GET["tipo"]=="e") echo $_GET["t"]; else echo $_GET["t1"];
echo "</h1>";

$sql="select * from ".$_GET["t"];
$result=mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
echo "<table border=\"1\">";
while($row=mysql_fetch_array($result))
{
	echo "<tr>";
	echo "<td>".$row[0]."</td>";
	echo "<td>".$row[1]."</td>";
	
		echo "<td><a href=\"modificardatos.php?t=".$_GET["t"]."&t1=".$_GET["t1"]."&tipo=".$_GET["tipo"]."&id=".$row[0]."\">".imagen("mod.gif")."</a></td>";
	//	echo "<td><a href=\"eliminarlibros.php?uni=".$uni."&id=".$row["Id"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar a ".$row["Nombre"]."?')\">".imagen("del.gif")."</a></td>";
	
	echo "</tr>";
}
echo "</table><br><br>";
echo "</center>";
//echo imagen("add_user.png")."<a href=\"registro.php\">Insertar nuevo</a>";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");


?>
	
