<?if($_POST)
{
		if($_POST["respuesta"]=="Volver") 
		{
			header("Location:alumnos.php?uni=".$_POST["uni"]);
			die("");
		}	
	//Validacion
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/funciones.inc");
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	$cont=0;
	if(!valida_fecha($_POST["fecha"]))
	{
		$cont++;
		$error_fecha=true;
	}
	if(!valida_fecha($_POST["fecha2"]))
	{
		$cont++;
		$error_fecha2=true;
	}
	if($cont==0)
	{	
		
		
		
			$sql="insert into Partes (Ida,Tipo,Fecha,Fecha_fin,Sancion,Comentario) values (".$_POST["id"].",'s','".cambiaf_a_mysql($_POST["fecha"])."','".cambiaf_a_mysql($_POST["fecha2"])."','".$_POST["sancion"]."','".$_POST["comentario"]."')";	
			mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
			header("Location:alumnos.php?uni=".$_POST["uni"]);
			die("");
		}
		mysql_close();
	
}

if($_GET || $cont>0)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");

permisos("partes");
if($_GET) $i=$_GET["id"]; else $i=$_POST["id"];
if($_GET) $ti=$_GET["tipo"]; else $ti=$_POST["tipo"];
	$sql="select * from Alumnos where Id=".$i;
	$result=mysql_query($sql);
	if($row=mysql_fetch_array($result))
	{
		echo "<h1>Sanción</h1><br>";
		echo "Alumno:<b>".$row["Nombre"]."</b> (".$row["Unidad"].")";
		echo "<form name=\"for\" action=\"sancion.php\" method=\"post\">";
		echo "<input type=\"hidden\" name=\"id\" value=\"".$i."\">"; 
		echo "<input type=\"hidden\" name=\"uni\" value=\"".$row["Unidad"]."\">"; 
		echo "<input type=\"hidden\" name=\"tipo\" value=\"".$ti."\">"; 
		echo "Fecha de incio:<br>";

  		if($error_fecha) 
		{
			echo "<input type=\"text\" size=\"10\" name=\"fecha\">"; 
			echo "Fecha incorrecta.";
			echo "<a href=\"javascript:cal1.popup();\">".imagen("cal.gif")."</a><br><br>";
		}		
		else
		{	
			echo "<input type=\"text\" size=\"10\" name=\"fecha\" value=\"".date("d/m/Y")."\">";
			echo "<a href=\"javascript:cal1.popup();\">".imagen("cal.gif")."</a><br><br>";
		}
		echo "Fecha finalización:<br>";

  		if($error_fecha2) 
		{
			echo "<input type=\"text\" size=\"10\" name=\"fecha2\">"; 
			echo "Fecha incorrecta.";
			echo "<a href=\"javascript:cal2.popup();\">".imagen("cal.gif")."</a><br><br>";
		}		
		else
		{	
			echo "<input type=\"text\" size=\"10\" name=\"fecha2\" value=\"".date("d/m/Y")."\">";
			echo "<a href=\"javascript:cal2.popup();\">".imagen("cal.gif")."</a><br><br>";
		}
		echo "<br>Sanción:<br>";
		echo "<input type=\"text\" size=\"40\" name=\"sancion\" value=\"".$_POST["sancion"]."\">";
		echo "<br>Comentario:<br>";
		echo "<textarea name =\"comentario\" rows=\"8\" cols=\"50\">";
		echo "</textarea><br><br>";?>
		<input type="submit" name="respuesta" value="Aceptar">
		<input type="submit" name="respuesta" value="Volver">
		<?echo "</form>";
	}
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>
<script language="JavaScript">
<!-- // create calendar object(s) just after form tag closed
	 // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
	 // note: you can have as many calendar objects as you need for your application
	
	var cal1 = new calendar1(document.forms['for'].elements['fecha']);
	cal1.year_scroll = true;
	cal1.time_comp = false;
	var cal2 = new calendar1(document.forms['for'].elements['fecha2']);
	cal2.year_scroll = true;
	cal2.time_comp = false;
//-->
</script>	
<?

}
	?>
	
