<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("libros");

if ($_GET) $uni=$_GET["uni"];
if ($_POST) $uni=$_POST["uni"];
$sql="select Unidad from Alumnos where Unidad like '%ESO%' group by Unidad";
$result=mysql_query($sql);
if($row=mysql_fetch_array($result)) if($uni=="") $uni=$row["Unidad"];

$cond=" where Unidad='".$uni."'";
$sql="select * from Alumnos".$cond." order by ".ordenar_bien("Nombre");
//Crea los ficheros de texto necesario para el listado
$fich=escribir_fich("7",$sql);

$menu="libros";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");


echo "<center><h2>Gestión de Libros de Texto</h2></center>";

$sql="select Unidad from Alumnos where Unidad like '%ESO%' group by Unidad";
$result=mysql_query($sql);

echo "<br><center>";
echo "<form name=\"f1\" action=\"libros.php\" method=\"post\">\n";
echo "<select onchange=\"document.f1.submit();\" name=\"uni\" size=\"0\">";


while($row=mysql_fetch_array($result))
{
	
	
	if($uni==$row["Unidad"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
}
echo "</select>\n";
//echo "<input type=\"submit\" value=\"Aceptar\">\n";

echo "</center>";
echo "</form>";
echo "<form name=\"f2\" action=\"guardarlibros.php?uni=".$uni."\" method=\"post\">\n";
$cond=" where Unidad='".$uni."'";
$sql="select * from Alumnos".$cond." order by ".ordenar_bien("Nombre");

echo "<center><h2>".$uni."</h2></center>";
if($_GET["e"])  echo "<center>Datos guardados correctamente...</center>";
if(permisos("libros","A")) echo "<br><p align=\"right\"><input type=\"submit\" value=\"Guardar cambios\"></p>";
echo "<center><table id=\"datos\" border=\"1\">";
abre_libros($uni);
	
$result=mysql_query($sql);
$cont=1;
while($row=mysql_fetch_array($result))
{
	echo "<tr>";
	echo "<td>".$cont."</td>";
	echo "<td>".$row["Nombre"]."</td>";
	selectores($uni,$row["Id"]);
	echo "</tr>";
	$cont++;
}
echo "</table></center>";
echo "</form>";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");




function abre_libros($uni)
{
	echo "<tr>";
	echo "<td></td>";
	
	echo "<td>Alumnos</td>";
	$sql="select * from Libros where Curso=".substr($uni,0,1)." order by Id";
	$result=mysql_query($sql);
	while($row=mysql_fetch_array($result))
		echo "<td>".$row["Abr"]."</td>";
	if(permisos("libros","A")) echo "<td>B</td><td>N</td><td>C</td><td>D</td>";
	echo "</tr>";
	
	
}
function selectores($uni,$id)
{
	$sql="select * from Libros where Curso=".substr($uni,0,1)." order by Id";
	$result=mysql_query($sql);
	while($row=mysql_fetch_array($result))
	{
		echo "<td>";
		if(permisos("libros","A"))
		{
			echo "<center><input type=\"checkbox\" value=\"".$id."-".$row["Id"]."\" id=\"".$id."-".$row["Id"]."\" name=\"".$id."-".$row["Id"]."\"";
			if(libro_select($id,$row["Id"])) echo "checked>"; else echo ">";
			echo "</center>";
		}
		else
		{
			if(libro_select($id,$row["Id"])) echo "<center>X</center>"; else echo " ";
		}
		echo "</td>\n";
	}
	if(permisos("libros","A"))
	{
		echo "<td><input type=\"button\" value=\"B\" onClick=\"operador(".$id.",".substr($uni,0,1).",'B');\"></td>\n";
		echo "<td><input type=\"button\" value=\"N\" onClick=\"operador(".$id.",".substr($uni,0,1).",'N');\"></td>\n";
		echo "<td><input type=\"button\" value=\"C\" onClick=\"operador(".$id.",".substr($uni,0,1).",'C');\"></td>\n";
		echo "<td><input type=\"button\" value=\"D\" onClick=\"operador(".$id.",".substr($uni,0,1).",'D');\"></td>\n";
	}
	
	
}
function libro_select($id,$idl)
{
	$sql="select * from LibrosAlumnos where Id=".$id." and Idl=".$idl;
	$result=mysql_query($sql);
	if($row5=mysql_fetch_array($result)) return true; else return false;
}
?>

