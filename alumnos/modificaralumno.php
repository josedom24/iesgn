<?
session_start();
if($_POST)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
	permisos("alumnos","E",1);
	$cont_error=0;
	

	//Si todo ok
	
	if($cont_error==0)
	{
		$sql="UPDATE Alumnos SET Nombre='".$_POST['nom']."',DNI='".$_POST["dni"]."',Unidad='".$_POST['uni']."',Direccion='".$_POST["dir"]."',Localidad='".$_POST["loc"]."',Provincia='".$_POST["pro"]."',CodPostal='".$_POST['cp']."',Ap1tutor='".$_POST['t1']."',Ap2tutor='".$_POST['t2']."',Nomtutor='".$_POST['t0']."' WHERE Id='".$_POST['Id']."'";
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));		
		mysql_close();
		header("Location:alumnos.php");
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("alumnos","E",1);
	if(!$_POST && $_GET)
	{
		
		
		$sql="SELECT * FROM Alumnos WHERE Id=".$_GET['id'];
		
		$result=mysql_query($sql) or die("error:".$sql."<br>".mysql_error());
		
		if ($row=mysql_fetch_array($result))
		{
			$valor_nom=$row['Nombre'];
			$valor_dni=$row['DNI'];
			$valor_unidad=$row['Unidad'];
			$valor_usuario_modificar=$row['Id'];
			$valor_dir=$row["Direccion"];
			$valor_loc=$row["Localidad"];
			$valor_prov=$row["Provincia"];
			$valor_cp=$row["CodPostal"];
			$valor_t0=$row["Nomtutor"];			
			$valor_t1=$row["Ap1tutor"];			
			$valor_t2=$row["Ap2tutor"];			

		}
		
	}
	else
	{
			$valor_nom=$_POST['nom'];
			$valor_dni=$_POST['dni'];
			$valor_unidad=$_POST['uni'];
			$valor_usuario_modificar=$_POST["usuario_a_modificar"];
			$valor_dir=$_POST["dir"];
			$valor_loc=$_POST["loc"];
			$valor_prov=$_POST["pro"];
			$valor_cp=$_POST["cp"];
			$valor_t0=$_POST["t0"];			
			$valor_t1=$_POST["t1"];			
			$valor_t2=$_POST["t2"];			


			
			
	}

?>
	<h1>Modificar Alumno</h1>
	<form action="modificaralumno.php" method="post">
	<?
	echo "<input type=\"hidden\" name=\"Id\"  value=\"".$valor_usuario_modificar."\">";
	
	//echo  "Unidad:<br>\n";
	$sql="select Unidad from Alumnos group by Unidad";
	$result=mysql_query($sql);

	echo "<br><center><table>";
	echo "<tr><td>";
	echo "<select  name=\"uni\" size=\"0\">";

	while($row=mysql_fetch_array($result))
	{
		
	
		if($row["Unidad"]==$valor_unidad)
				echo "<option selected ";
			else
				echo "<option "; 
			echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
	}
	echo "</select>\n";
	echo "</td></tr>";

	echo "<tr>";
	echo "<td>Nombre:";
	echo '<input type="text" name="nom" value="'.$valor_nom.'" size="50"/></td>';
	echo "</tr>";

	echo "<tr>";
	echo "<td>DNI:";
	echo '<input type="text" name="dni" value="'.$valor_dni.'"/></td>';
	echo "</tr>";


	echo "<tr>";
	echo "<td>Dirección:";
	echo '<input type="text" name="dir" value="'.$valor_dir.'" size="50"/></td>';
	echo "</tr>";
	
	echo "<tr>";
	echo '<td>Localidad:<input type="text" name="loc" value="'.$valor_loc.'"/></td>';
	echo "</tr>";

	echo "<tr>";
	echo '<td>Provincia:<input type="text" name="pro" value="'.$valor_prov.'"/></td>';
	echo "</tr>";

	echo "<tr>";
	echo '<td>CP:<input type="text" name="cp" value="'.$valor_cp.'"/><td/>';
	echo "</tr>";

	echo "<tr>";
	echo '<td>Nombre Tutor:<input type="text" name="t0" value="'.$valor_t0.'"/></td>';
	echo "</tr>";

	echo "<tr>";
	echo '<td>Apellido 1 Tutor:<input type="text" name="t1" value="'.$valor_t1.'"/></td>';
	echo "</tr>";

	echo "<tr>";
	echo '<td>Apellido 2 Tutor:<input type="text" name="t2" value="'.$valor_t2.'"/></td>';
	echo "</tr>";
	echo "</table>";


	?>
	
	
	<br>
	<br>
	<input type="submit" value="Listo!!!">
	</form>
<?
}include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>
