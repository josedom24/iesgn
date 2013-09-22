<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("partes");
//Recibe la fecha de resumen de amonestaciones
if($_POST)
{		
	//Recibe la fecha y seleccionas todas las amonestaciones en esa fecha
	$sql="select * from Alumnos,Partes where Alumnos.Id=Partes.Ida and Partes.Tipo='c' and Partes.Fecha='".cambiaf_a_mysql($_POST["fecha"])."' group by Partes.Ida order by Alumnos.Nombre";
	
	//Crea los ficheros de texto necesario para el listado
	//$fich=escribir_fich("1",$sql);
	
	//Coloca el menu horizontal
	$menu="resumen_cit";	
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");

	//Muestra las cabeceras
	echo "<center><h2>Resumen de citaciones</h2></center>";
	echo "<center><h3>Fecha: ".$_POST["fecha"]."</h3></center><br><br>";	
	
	echo "<table border=\"1\">";	
	$result=mysql_query($sql) or die("error:".$sql);
	if(mysql_num_rows($result)==0) echo "No hay datos para esa fecha.";
	while($row=mysql_fetch_array($result))
	{
		echo "<tr>";
		//echo "<td>".$row["Ida"]."</td>";
		echo "<td>".$row["Nombre"]."</td>";
		echo "<td>".$row["Unidad"]."</td>";
		echo "<td>".calcular_num_amonestaciones($row["Ida"])."/".calcular_num_citaciones($row["Ida"])."/".calcular_num_sanciones($row["Ida"])."</td>";
		echo "<td><a href=\"".url()."impresora/carta.php?id=".$row[0]."&l=6\">".imagen("report.gif")."</a></td>";
		echo "</tr>";

	}
	echo "</table>";
	echo "<br>";
	
}

//La primera vez muestra un formulario para pedir la fecha
if(!$_POST)
{
	echo "<form name=\"for\" action=\"resumen_cit.php\" method=\"post\"  onsubmit=\"return validarF(this.fecha)\">";
	echo "Elige fecha del resumen de citaciones:<br>";
	#echo "<input type=\"text\" size=\"10\" name=\"fecha\" value=\"".date("d/m/Y")."\">";
	ComboPartes("c");
	echo "<a href=\"javascript:cal1.popup();\">".imagen("cal.gif")."</a><br><br>";
	echo "<input type=\"submit\" name=\"respuesta\" value=\"Aceptar\">";

	echo "</form>";
	
}?>
<script language="JavaScript">
<!-- // create calendar object(s) just after form tag closed
	 // specify form element as the only parameter (document.forms['formname'].elements['inputname']);
	 // note: you can have as many calendar objects as you need for your application
	var cal1 = new calendar1(document.forms['for'].elements['fecha']);
	cal1.year_scroll = true;
	cal1.time_comp = false;
//-->
</script>
