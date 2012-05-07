<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("contabilidad");

if($_POST["cg"]) $cg=$_POST["cg"]; else $cg=$_GET["cg"];

if($_POST)  {$_SESSION["cond"]=$cg;}
else $cg=$_SESSION["cond"];

if($cg=="") $cg=1;
$menu="contabilidad";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");


echo "<center><h2>Gestión contabilidad Departamentos</h2></center>";

echo "<form name=\"f1\" action=\"contabilidad.php\" method=\"post\">\n";
echo "<table><tr>";
echo "<td>Centro de Gasto:</td>";
echo "<td>";
$sql="select * from CentroGastos where Id=".$cg;
	$result=mysql_query($sql);
	if($row=mysql_fetch_array($result)) $dep=$row[2];
Combo2("CentroGastos","cg",1,2,$dep,0,"onchange=\"document.f1.submit()\"");
echo "</td></tr></table>";

echo "</form>";
echo "<hr>";
$sql="select sum(Cantidad) as total from Contabilidad where Idcg=".$cg;
$result=mysql_query($sql);
if($row=mysql_fetch_array($result)) $cant_total=$row["total"];
if($cant_total!="") echo "Total:<strong>".round($cant_total,2)."</strong><br/>";
$sql="select * from CentroGastos,Contabilidad where Contabilidad.Idcg=CentroGastos.Id and Contabilidad.Idcg=".$cg." order by Fecha desc";

$result=mysql_query($sql);
$cont=1;

//cantidad de resultados por página (opcional, por defecto 20)
$_pagi_cuantos = 10;
$_pagi_sql =$sql;

$_pagi_propagar=array("cg",$cg);
if($_POST["cont"]!="") {$_pagi_propagar[]="cont";$_pagi_propagar[]=$_POST["cont"];}



//Incluimos el script de paginación. Éste ya ejecuta la consulta automáticamente
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/pag/paginator.inc.php");


//Incluimos la barra de navegación




if(mysql_num_rows($result)>0)
{

echo "<center><table id=\"datos\" border=\"1\">";
echo "<tr>";
	
	echo "<td>Fecha</td>";
	echo "<td>Concepto</td>";
	echo "<td>Cantidad</td>";
	echo "<td>B</td>";
	echo "<td>E</td>";
	echo "</tr>";


while($row=mysql_fetch_array($_pagi_result))
{
	echo "<tr>";
	
	echo "<td>".cambiaf_a_normal($row["Fecha"])."</td>";
	echo "<td>".$row["Concepto"]."</td>";
	echo "<td>".$row["Cantidad"]."</td>";
	
	
	//echo "<td><a href=\"eliminaralumno.php?id=".$row["Id"]."\"><img src=\"img/del.gif\" border=0></a></td>";
	echo "<td><a href=\"eliminar.php?cg=".$cg."&id=".$row["Id"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar el registro:".$row["Concepto"]."?')\">".imagen("del.gif")."</a></td>";
    
	echo "<td><a href=\"modificar.php?cg=".$cg."&id=".$row["Id"]."\">".imagen("mod.gif")."</a></td>"; 
	
	
	//echo "<td><a alt=\"Historial de amonestaciones\" href=\"historial.php?id=".$row["Id"]."\"&tipo=\"s\"><img  src=\"img/cal.gif\" border=0></a></td>";
	echo "</tr>";
	$cont++;
}
echo "</table></center><br>";
echo"<p>".$_pagi_navegacion."</p>";
}
else
{
	echo "<h3>Registros no encontrados...</h3>";
}
echo "<br>";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>

