<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");

if($_POST)
{
	if(!$_POST["mes"])
	{
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
		echo "Debes indicar el mes.";
		echo "<br/><a href=\"fotocopias.php\">Volver</a>";
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
		die("");
	}

	
	foreach($_POST as $id => $v)
	{
		if(is_numeric($id) and is_numeric($v))
		{
		$sql="select * from CentroGastos where Id=".$id;
		$result=mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
		if($row=mysql_fetch_array($result)) $vant=$row["Fotocopias"];
		if($v>=$vant)
		{
			$sql="update CentroGastos Set Fotocopias=".$v." where Id=".$id;
			mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
			if($v>$vant)
			{
				$sql="INSERT INTO Contabilidad(Idcg,Fecha,Concepto,Cantidad) VALUES (".$id.",'".date("Y-m-d")."','Fotocopias de ".$_POST["mes"]."',".(($v-$vant)*(-0.05)).")";
				mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
			}
		}
		else
		{
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
		echo "Valor de fotocopias incorrecto en ".$row[2];
		echo "<br/><a href=\"fotocopias.php\">Volver</a>";
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
		die("");
		}		

		}
	}
	mysql_close();
	header("Location:fotocopias.php");
}
if(!$_POST)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
	permisos("contabilidad");

	echo "<h1>Gestion de Fotocopias</h1>";
	$sql="select * from CentroGastos";
	$result=mysql_query($sql);
	echo "<form action=\"fotocopias.php\" method=\"post\">";
	echo "Mes:<select  name=\"mes\" size=\"0\">";
	$meses=array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
	$mes_actual=date("m");
	$cont=1;
	foreach($meses as $mes)
	{
		if($cont==$mes_actual) echo "<option selected "; else echo"<option ";
		echo "value=\"".$mes."\">".$mes."</option>\n";
		$cont++;
	}
	echo "</select>\n";
	
	echo "<br/>";
	echo "<br/>";
	echo "<table border=\"1\">";
	echo"<tr>";
	echo "<td>Departamento</td>";
	echo "<td>Anterior</td>";
	echo "<td>Actual</td>";
	echo"</tr>";
	
	while($row=mysql_fetch_array($result))
	{
		echo"<tr>";
		echo "<td>".$row[2]."</td>";
		echo "<td>".$row[3]."</td>";
		echo "<td><input type=\"text\" name=\"".$row[0]."\"></td>";
		echo"</tr>";
	}
	echo "</table>";
	echo "<input type=\"submit\" value=\"Enviar\">";
	echo "</form>";
}
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>
