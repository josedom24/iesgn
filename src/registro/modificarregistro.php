<?
if($_POST)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	
	$cont_error=0;
	
	
	//Validacion de fecha
	$datos=explode("/",$_POST["fecha"]);
	$v=checkdate((int)$datos[1],(int)$datos[0],(int)$datos[2]);
		if(!$v)
	{
		$cont_error++;
		$error_fecha=true;
	}
	
	if(curso2($_POST["fecha"])!=$_POST["curso"])
	{
		$cont_error++;
		$error_n3=true;
		$_POST["curso"]=curso2($_POST["fecha"]);
	
		$_POST["id"]=calcula_id2($_POST["tipo"],$_POST["curso"]);
	}
	
	
	
	
	//Si todo ok
	if($cont_error==0)
	{
		
		$sql="UPDATE Registro SET Fecha='".cambia_a_mysql($_POST["fecha"])."',Idp=".$_POST["proc"].",Idr=".$_POST["remi"].",Idc=".$_POST["clas"].",Contenido='".$_POST["cont"]."' WHERE Id=".$_POST["id"]." and Tipo='".$_POST["tipo"]."' and Curso='".$_POST["curso"]."'";
		
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
		mysql_close();
		header("Location:registro.php?tipo=".$_POST["tipo"]);
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");

permisos("secretaria","E",1);


?>
     <?if($_GET["tipo"]=="e") $valor="Entrada"; else $valor="Salida";?>
	<h1>Modificar Registro de <?echo $valor;?></h1>
	<form name="for" action="modificarregistro.php" method="post">
	<?
	$tipo=$_GET["tipo"];
	if($_POST["curso"]) $vcurso=$_POST["curso"]; else $vcurso=curso();
	if($_POST["id"]) $vid=$_POST["id"]; else $vid=calcula_id($_GET["tipo"],$vcurso);
	if($_POST["fecha"]) $vf=$_POST["fecha"]; else $vf=date("d/m/Y");
	if($_POST["tipo"]) $vt=$_POST["tipo"]; else $vt=$_GET["tipo"];
	if(!$_POST && $_GET)
	{
	if($_GET["curso"]) $vcurso=$_GET["curso"]; else $vcurso=curso();
	if($_GET["id"]) $vid=$_GET["id"]; else $vid=calcula_id($_GET["tipo"],$vcurso);
	if($_GET["fecha"]) $vf=$_GET["fecha"]; else $vf=date("d/m/Y");
	if($_GET["tipo"]) $vt=$_GET["tipo"];
	
	
	$sql="SELECT * FROM Registro WHERE Tipo='".$vt."' and Fecha='".cambiaf_a_mysql($vf)."' and Id=".$vid." and Curso='".$vcurso."'";
		$result=mysql_query($sql) or die("error:".$sql."<br>".mysql_error());
		
		if ($row=mysql_fetch_array($result))
		{
			$vidp=$row['Idp'];
			$vidr=$row['Idr'];
			$vidc=$row['Idc'];
			$vcont=$row['Contenido'];
		}
		
	}
	else
	{
			$vidp=$_POST['proc'];
			$vidr=$_POST['remi'];
			$vidc=$_POST['clas'];
			$cont=$_POST["cont"];
			
			
	}
	
	echo "<input type=\"hidden\" name=\"curso\"  value=\"".$vcurso."\">";
	echo "<input type=\"hidden\" name=\"tipo\"  value=\"".$vt."\">";
	echo "<input type=\"hidden\" name=\"id\"  value=\"".$vid."\">";
	


echo  "Nº orden:<strong>".$vid."</strong>\n";
	if($error_n3) echo "Se ha comenzalo un nuevo curso escolar: ".$vcurso.".Se reinicia el orden de registros.";
	echo "<br><br>Fecha:<br>";
	//Fecha
		if($error_fecha) 
		{
			echo "<input type=\"text\" size=\"10\" name=\"fecha\">"; 
			echo "Fecha incorrecta.";
			echo "<a href=\"javascript:cal1.popup();\">".imagen("cal.gif")."</a><br><br>";
		}		
		else
		{	
			echo "<input type=\"text\" size=\"10\" name=\"fecha\" value=\"".$vf."\">";
			echo "<a href=\"javascript:cal1.popup();\">".imagen("cal.gif")."</a><br><br>";
		}
		
	
	if($tipo=="e") echo "Procedencia:<br>"; else echo "Destino:<br>";
	Combo("Procedencia","proc",1,1,$vidp);
	if($tipo=="e") echo "<br>Remitente:<br>"; else echo "<br>Destinatario:<br>";
	Combo("Remitente","remi",1,1,$vidr);
	echo "<br>Documento:<br>";
	Combo("ClaseDocumento","clas",1,1,$vidc);
	echo "<br>Contenido:<br><input type=\"text\" size=\"70\" name=\"cont\" value=\"".$vcont."\"><br>";
	?>
	<br>
	<br>
	<input type="submit" value="Listo!!!">
	</form>
<?
}include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>
<script language="JavaScript">
<!-- // create calendar object(s) just after form tag closed
	 // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
	 // note: you can have as many calendar objects as you need for your application
	
	var cal1 = new calendar1(document.forms['for'].elements['fecha']);
	cal1.year_scroll = true;
	cal1.time_comp = false;
	
//-->
</script>	
<?function cambia_a_mysql($fecha){
    ereg( "([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha);
    $lafecha=$mifecha[3]."-".$mifecha[2]."-".$mifecha[1];
    return $lafecha;
}
function curso2($f=0)
{
	if($f==0)
	{
	if(date("m")<9) $curso=(date("Y")-1)."-".date("Y");
	else $curso=date("Y")."-".(date("Y")+1);
	}
	else
	{
	$datos=explode("/",$f);	
	if($datos[1]<9) $curso=($datos[2]-1)."-".$datos[2];
	else $curso=$datos[2]."-".($datos[2]+1);
	}
  return $curso;
}  


function calcula_id2($t,$c)
{
	$sql="select * from Registro where Tipo='".$t."' and Curso='".$c."' order by Id desc";
	
$result=mysql_query($sql);
if($row=mysql_fetch_array($result)) return ($row["Id"]+1); else return (1);
	
} ?>