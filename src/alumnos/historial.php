<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("alumnos");
//Recibe el identificador del alumno
if($_GET)
{	
	//Seleccion las amonestaciones y las sanciones del alumno recibido
	$sql="select * from Alumnos,Partes where Alumnos.Id=Partes.Ida and Alumnos.Id=".$_GET["id"]. " order by Partes.Fecha";
	
	//Crea los ficheros de texto necesario para el listado
	$fich=escribir_fich("4",$sql);
	
	//Coloca el menu horizontal
	$menu="historial";	
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/menu2.inc");
	
	//Muestra los resultados
	$result=mysql_query($sql) or die("error:".$sql);
	if(mysql_num_rows($result)==0) echo "No hay datos para ese alumno.";
	
	$band=false;
	while($row=mysql_fetch_array($result))
	{
		if(!$band)
		{
			echo "<center><h2>Historial del alumno</h2></center>";
			echo "<center><h3>Alumno: ".$row["Nombre"]."(".$row["Unidad"].")</h3></center><br><br>";	
			echo "<table border=\"1\">";
			echo "<tr>";
	echo "<td>Tipo</td>";
	echo "<td>Fecha</td>";
	echo "<td>Fecha Fin</td>";
	echo "<td>Sanción</td>";
	echo "<td>Comentario</td>";
	echo "</tr>";
			
			$band=true;
		}
	
		if($row["Tipo"]=="a" or $row["Tipo"]=="c")
		{
		echo "<tr>";
		if($row["Tipo"]=="a") echo "<td>Amonestación</td>"; else echo "<td>Citación</td>";
		echo "<td>".cambiaf_a_normal($row["Fecha"])."</td>";
		echo "<td></td>";
		echo "<td></td>";
		echo "<td>".$row["Comentario"]."</td>";
		if(permisos("partes","B"))
			echo "<td><a href=\"eliminarpartes.php?id=".$row["Id"]."&ida=".$row["Ida"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar la amonestación ".$row["Idp"]." de ".$row["Nombre"]."?')\">".imagen("del.gif")."</a></td>";
		echo "</tr>";
		}
		else
		{
			echo "<tr>";
			echo "<td>Sanción</td>";
			echo "<td>".cambiaf_a_normal($row["Fecha"])."</td>";
			echo "<td>".cambiaf_a_normal($row["Fecha_fin"])."</td>";
			echo "<td>".$row["Sancion"]."</td>";
			echo "<td>".$row["Comentario"]."</td>";
			if(permisos("partes","B"))
			echo "<td><a href=\"eliminarpartes.php?id=".$row["Id"]."&ida=".$row["Ida"]."\" onclick=\"return confirmar('¿Estás seguro de eliminar la sanción ".$row["Idp"]." de ".$row["Nombre"]."?')\">".imagen("del.gif")."</a></td>";
			echo "</tr>";
		}
	}
	echo "</table>";
	echo "<br>";
	
}
//Si no recibe GET vuelve a alumnos.php
else
{
	Header("Location:alumnos.php");
}
?>

