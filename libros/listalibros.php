<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("libros","E",1);


echo "<center>";
echo "<h1>Libros de Texto</h1>";
$cursos=array("1º ESO","2º ESO","3º ESO","4º ESO");
echo "<form name=\"f1\" action=\"listalibros.php\" method=\"post\">\n";

echo "<select onchange=\"document.f1.submit();\" name=\"uni\" size=\"0\">";
if ($_GET) $uni=$_GET["uni"];
if ($_POST) $uni=$_POST["uni"];

foreach($cursos as $c)
{
	if($uni=="") $uni=$c;
	
	if($uni==$c)
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$c."\">".$c."</option>\n";
}
echo "</select>\n";
//echo "<input type=\"submit\" value=\"Aceptar\">\n";
echo "</form>";


$sql="select * from Libros where Curso=".substr($uni,0,1);
$result=mysql_query($sql) or die("error: ".$sql."<br>".mysql_error());
echo "<table border=\"1\">";
while($row=mysql_fetch_array($result))
{
	echo "<tr>";
	echo "<td>".$row["Abr"]."</td>";
	echo "<td>".$row["Nombre"]."</td>";
	
		echo "<td><a href=\"modificarlibros.php?uni=".$uni."&id=".$row["Id"]."\">".imagen("mod.gif")."</a></td>";
	//	echo "<td><a href=\"eliminarlibros.php?uni=".$uni."&id=".$row["Id"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar a ".$row["Nombre"]."?')\">".imagen("del.gif")."</a></td>";
	
	echo "</tr>";
}
echo "</table><br><br>";
echo "</center>";
//echo imagen("add_user.png")."<a href=\"registro.php\">Insertar nuevo</a>";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");


?>
	
