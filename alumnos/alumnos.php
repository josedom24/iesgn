<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("alumnos");
$uni="";
if ($_GET) $uni=$_GET["uni"];
if ($_POST) $uni=$_POST["uni"];
$sql2="select Unidad from Alumnos group by Unidad";
$result2=mysql_query($sql2);
while($row=mysql_fetch_array($result2))
{
	if($uni=="") { $uni=$row["Unidad"];}
}
$cond=" where Unidad='".$uni."'";

$sql="select * from Alumnos".$cond." order by ".ordenar_bien("Nombre");
$fich=escribir_fich("8",$sql);

$menu="alumnos";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");


echo "<center><h2>Gestión de alumnos</h2></center>";
echo "<form name=\"f1\" action=\"alumnos.php\" method=\"post\">\n";


echo "<br><center>";
echo "<select onchange=\"document.f1.submit();\" name=\"uni\" size=\"0\">";
$sql2="select Unidad from Alumnos group by Unidad";
$result2=mysql_query($sql2);
while($row=mysql_fetch_array($result2))
{

	
	if($uni==$row["Unidad"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
}
echo "</select>\n";
//echo "<input type=\"submit\" value=\"Aceptar\">\n";
echo "</form>";
echo "</center>";

//7/5/12: Recorro la tabla para calcular el total de amonestaciones, citaciones y sanciones

$result=mysql_query($sql);
$ta=0;
$tc=0;
$ts=0;
while($row=mysql_fetch_array($result))
{
	$ta=$ta+calcular_num_amonestaciones($row["Id"]);
	$tc=$tc+calcular_num_citaciones($row["Id"]);
	$ts=$ts+calcular_num_sanciones($row["Id"]);
}



echo "<center><h2>".$uni."</h2></center>";
echo "<center><h4>Total:".$ta."/".$tc."/".$ts."</h4></center>";
echo "<center><table id=\"datos\" border=\"1\">";
echo "<tr>";
	echo "<td>N</td>";
	echo "<td>Nombre</td>";
	echo "<td>A/C/S</td>";
	echo "<td>A</td>";
	echo "<td>C</td>";
	echo "<td>S</td>";
	echo "<td>H</td>";
	if(permisos("alumnos","B")) echo "<td>B</td>";
	if(permisos("alumnos","E")) echo "<td>E</td>";
	
	
	echo "</tr>";

$result=mysql_query($sql);
$cont=1;
while($row=mysql_fetch_array($result))
{
	echo "<tr>";
	echo "<td>".$cont."</td>";
	echo "<td>".$row["Nombre"]."</td>";
	echo "<td>".calcular_num_amonestaciones($row["Id"])." / ".calcular_num_citaciones($row["Id"])." / ".calcular_num_sanciones($row["Id"])."</td>";
	echo "<td><a href=\"partes.php?id=".$row["Id"]."\"&tipo=\"a\">".imagen("amon.png")."</a></td>";
	echo "<td><a href=\"citacion.php?id=".$row["Id"]."\"&tipo=\"c\">".imagen("cit.png")."</a></td>";
	echo "<td><a href=\"sancion.php?id=".$row["Id"]."\"&tipo=\"s\">".imagen("sans.png")."</a></td>";
	
	echo "<td><a href=\"historial.php?id=".$row["Id"]."\">".imagen("hist.png")."</a></td>";
	//echo "<td><a href=\"modificaralumno.php?id=".$row["Id"]."\"><img src=\"img/mod.gif\" border=0></a></td>";
	if(permisos("alumnos","B"))
		echo "<td><a href=\"eliminaralumno.php?id=".$row["Id"]."&uni=".$row["Unidad"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar a ".$row["Nombre"]."?')\">".imagen("del.gif")."</a></td>";
	if(permisos("alumnos","E"))
		echo "<td><a href=\"modificaralumno.php?id=".$row["Id"]."\">".imagen("mod.gif")."</a></td>"; 
	 
	
	//echo "<td><a alt=\"Historial de amonestaciones\" href=\"historial.php?id=".$row["Id"]."\"&tipo=\"s\"><img  src=\"img/cal.gif\" border=0></a></td>";
	echo "</tr>";
	$cont++;
}
echo "</table></center>";
echo "<br/>";
echo imagen("add_user.png")."<a href=\"addalumno.php?uni=".$uni."\">Insertar nuevo alumno</a>";

include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>

