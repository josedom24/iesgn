<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("secretaria");

if(!$_GET["tipo"]) $tipo=$_POST["tipo"]; else $tipo=$_GET["tipo"];
if($tipo=="") $tipo="e";


if($_POST["curso"]) $vc=$_POST["curso"]; else $vc=$_GET["curso"];
if($vc=="") $vc=curso();



if (substr($_SERVER["HTTP_REFERER"],42,12)!="registro.php") $_SESSION["cond"]="";
if($_POST)  {$cond=criterio($tipo,$vc);$_SESSION["cond"]=$cond;}
else $cond=$_SESSION["cond"];
if($cond=="") $cond=criterio($tipo,$vc);

$sql="select * from Registro,ClaseDocumento,Procedencia,Remitente where Registro.Idp=Procedencia.Idp and Registro.Idr=Remitente.Idr and Registro.Idc=ClaseDocumento.Idc and ".$cond;
$fich=escribir_fich("9",$sql);





$menu="secretaria";
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");


if ($tipo=="e") echo "<center><h2>Gestión del registro de entrada</h2></center>";
else echo "<center><h2>Gestión del registro de salida</h2></center>";

echo "<form name=\"f1\" action=\"registro.php\" method=\"post\">\n";
echo "<input type=\"hidden\" name=\"tipo\"  value=\"".$tipo."\">";
echo "Busqueda:<br>";
echo "<table><tr>";
echo "<td>Curso:</td>";
echo "<td>";
ComboCurso($vc);
echo "</td>";
if($tipo=="e") echo "<td>Procedencia:</td><td>"; else echo "<td>Destino:</td><td>";

Combo("Procedencia","proc",0,1,"",0,"","Procedencia");
#Combo("Procedencia","proc");
echo "</td></tr><tr>";
echo "<td>Fecha Incial:</td>";
echo "<td><input type=\"text\" size=\"10\" name=\"fi\" value=\"".$_POST["fi"]."\">";
echo "<a href=\"javascript:cal1.popup();\">".imagen("cal.gif")."</a></td>";
echo "<td>Fecha Final:</td>";
echo "<td><input type=\"text\" size=\"10\"  name=\"ff\" value=\"".$_POST["ff"]."\">";
echo "<a href=\"javascript:cal2.popup();\">".imagen("cal.gif")."</a></td></tr><tr>";
if($tipo=="e") echo "<td>Remitente:</td><td>"; else echo "<td>Destinatario:</td><td>";

Combo("Remitente","remi",0,1,"",0,"","Remitente");
#Combo("Remitente","remi");
echo "</td>";
echo "<td>Documento:</td><td>";
Combo("ClaseDocumento","clas",0,1,"",0,"","ClaseDocumento");
echo "</td></tr></table>";
echo "<table><tr><td>Contenido:</td><td><input type=\"text\" size=\"70\" name=\"cont\" value=\"".$_POST["cont"]."\"></td></tr></table>";

echo "<input type=\"submit\" value=\"Buscar\">\n";
echo "</form>";
echo "<hr>";




$result=mysql_query($sql);
$cont=1;

//cantidad de resultados por página (opcional, por defecto 20)
$_pagi_cuantos = 10;
$_pagi_sql =$sql;

$_pagi_propagar=array("tipo",$tipo,"curso",$_POST["curso"],"proc",$_POST["proc"],"remi",$_POST["remi"],"clas",$_POST["clas"]);
if($_POST["cont"]!="") {$_pagi_propagar[]="cont";$_pagi_propagar[]=$_POST["cont"];}



//Incluimos el script de paginación. Éste ya ejecuta la consulta automáticamente
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/pag/paginator.inc.php");


//Incluimos la barra de navegación




if(mysql_num_rows($result)>0)
{

echo "<center><table id=\"datos\" border=\"1\">";
echo "<tr>";
	echo "<td>N</td>";
	echo "<td>Fecha</td>";
	if($tipo=="e")
	{
		if(permisos("secretaria","A")) echo "<td>Procedencia<a href=\"datos.php?t=Procedencia&t1=Destino&tipo=".$tipo."\">+</a></td>"; else echo"<td>Procedencia</td>";
		if(permisos("secretaria","A"))  echo "<td>Remitente<a href=\"datos.php?t=Remitente&t1=Destinatario&tipo=".$tipo."\">+</a></td>"; else echo"<td>Remitente</td>";
	}
	else	
	{
		if(permisos("secretaria","A")) echo "<td>Destino<a href=\"datos.php?t=Procedencia&t1=Destino&tipo=".$tipo."\">+</a></td>"; else echo"<td>Destino</td>";
		if(permisos("secretaria","A"))  echo "<td>Destinatario<a href=\"datos.php?t=Remitente&t1=Destinatario&tipo=".$tipo."\">+</a></td>"; else echo"<td>Destinatario</td>";
	}
	if(permisos("secretaria","A"))  echo "<td>Doc.<a href=\"datos.php?t=ClaseDocumento&t1=ClaseDocumento&tipo=".$tipo."\">+</a></td>"; else echo"<td>Doc.</td>";
	echo "<td>Contenido</td>";
	if(permisos("secretaria","B")) 	echo "<td>B</td>";
	if(permisos("secretaria","E")) 	echo "<td>E</td>";
	echo "</tr>";


while($row=mysql_fetch_array($_pagi_result))
{
	echo "<tr>";
	echo "<td>".$row["Id"]."</td>";
	echo "<td>".cambiaf_a_normal($row["Fecha"])."</td>";
	echo "<td>".$row["Procedencia"]."</td>";
	echo "<td>".$row["Remitente"]."</td>";
	echo "<td>".$row["ClaseDocumento"]."</td>";
	echo "<td>".$row["Contenido"]."</td>";
	
	
	//echo "<td><a href=\"eliminaralumno.php?id=".$row["Id"]."\"><img src=\"img/del.gif\" border=0></a></td>";
	if(permisos("secretaria","B"))
		echo "<td><a href=\"eliminarregistro.php?tipo=".$tipo."&id=".$row["Id"]."&curso=".$row["Curso"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar el registro ".$row["Id"]."?')\">".imagen("del.gif")."</a></td>";
    if(permisos("secretaria","E"))
	echo "<td><a href=\"modificarregistro.php?fecha=".cambiaf_a_normal($row["Fecha"])."&tipo=".$tipo."&id=".$row["Id"]."&curso=".$row["Curso"]."\">".imagen("mod.gif")."</a></td>"; 
	
	
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

<script language="JavaScript">
<!-- // create calendar object(s) just after form tag closed
	 // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
	 // note: you can have as many calendar objects as you need for your application
	var cal1 = new calendar1(document.forms['f1'].elements['fi']);
	cal1.year_scroll = true;
	cal1.time_comp = false;
		var cal2 = new calendar1(document.forms['f1'].elements['ff']);
	cal1.year_scroll = true;
	cal1.time_comp = false;
//-->
</script>
