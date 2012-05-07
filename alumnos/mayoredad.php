<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("mayor");

//Recibe order: campo por el que ordena; Nombre: para buscar el nombre; uni: para filtrar por unidad

if(!$_POST) $_POST["order"]="Nombre";
if($_POST["order"]) $or=" order by ".$_POST["order"];
if($_POST["Nombre"]) $wh=" where Nombre like '".$_POST["Nombre"]."%'";
if($_POST["uni"])
{
	if(strlen($wh)==0) $wh=" where Unidad='".$_POST["uni"]."'";
	else $wh=$wh." and Unidad='".$_POST["uni"]."'";
}
//Construye la sql según los paramétros recibidos
$sql="select * from Alumnos".$wh.$or;
$_SESSION["sql"]=$sql;

//Crea los ficheros de texto necesario para el listado
$fich=escribir_fich("2",$sql);

//Coloca el menu horizontal
	$menu="mayoredad";	
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");

//Muestra las cabeceras
echo "<center><h2>Alumnos mayores de edad</h2></center>";
echo "<form action=\"mayoredad.php\" method=\"post\">\n";
echo "<table border=\"0\">";
echo "<tr><td>Nombre: </td>";
echo "<td><input type=\"text\" name=\"Nombre\" value=\"".$_POST["Nombre"]."\"></td></tr>";


$sql2="select Unidad from Alumnos group by Unidad";
$result=mysql_query($sql2);

echo "<tr><td>Unidad: </td>";
echo "<td><select name=\"uni\" size=\"0\">";
if ($_POST) $uni=$_POST["uni"];

while($row=mysql_fetch_array($result))
{
	
	if($uni==$row["Unidad"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
}
echo "</select></td></tr>\n";
echo "<tr><td>Ordenar por:</td><td><select name=\"order\" size=\"0\">";
if($_POST["order"]=="Nombre") 
{
	echo "<option selected value=\"Nombre\">Nombre</option>";
	echo "<option value=\"Unidad\">Unidad</option>";
}
else
{
	echo "<option value=\"Nombre\">Nombre</option>";
	echo "<option selected value=\"Unidad\">Unidad</option>";
	}
echo "</select></td></tr>\n";	
echo "<tr><td><input type=\"submit\" value=\"Aceptar\"></td></tr>\n";
echo "</table>";
echo "</form>";
echo "<br><br>";




//Muestra los resultados

$result=mysql_query($sql);
echo "<center><table id=\"datos\" border=\"1\">";
echo "<thead>";
		echo "<td>Nombre</td>";
		echo "<td>F. Nacimiento</td>";
		echo "<td>Unidad</td>";
		echo "<td>Edad</td>";
	echo "</thead>";
while($row=mysql_fetch_array($result))
{
	
		echo "<tr>";
		$edad=edad(cambiaf_a_normal($row["Fecha_nacimiento"]));
	if($edad>18)
	{
		echo "<td>".$row["Nombre"]."</td>";
		echo "<td>".cambiaf_a_normal($row["Fecha_nacimiento"])."</td>";
		echo "<td>".$row["Unidad"]."</td>";
		echo "<td>".$edad."</td>";
	echo "</tr>";
	}
	
}
echo "</table></center><br>";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");?>
