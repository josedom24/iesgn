<?
if($_POST)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/funciones.inc");
	header("Location:".url()."impresora/carta.php?l=5&uni=".$_POST["uni"]);
	die("");
}
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("faltas");
$menu="faltas";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");

echo "<center><h2>Gestión de Partes de Falta</h2></center>";
echo "<form name=\"f1\" action=\"faltas.php\" method=\"post\">\n";
$sql="select Unidad from Alumnos group by Unidad";
$result=mysql_query($sql);

echo "<br><center>";
echo "<select  name=\"uni\" size=\"0\">";
if ($_GET) $uni=$_GET["uni"];
if ($_POST) $uni=$_POST["uni"];

while($row=mysql_fetch_array($result))
{
	if($uni=="") $uni=$row["Unidad"];
	
	if($uni==$row["Unidad"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
}
$sql="select * from Cursos";
$result=mysql_query($sql);
while($row=mysql_fetch_array($result))
{
	if($uni=="") $uni=$row["Abr"];
	
	if($uni==$row["Abr"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Abr"]."\">".$row["Abr"]."</option>\n";
}
echo "</select>\n";
echo "<input type=\"submit\" value=\"Aceptar\">\n";
echo "</form>";
echo "</center>";

include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>

