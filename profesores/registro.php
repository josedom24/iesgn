<?
if($_POST)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	$cont_error=0;
	//Validacion nombre
	if($_POST["nombre"]=="")
	{
		$cont_error++;
		$error_nombre=true;
	}
	if($_POST["apellidos"]=="")
	{
		$cont_error++;
		$error_apellidos=true;
	}
	
	
	//Validación correo electronico
	if($_POST["email"] && (!strstr($_POST["email"],".") || !strstr($_POST["email"],"@")))
	{
			$cont_error++;
			$error_email=true;
	}
	
	//Validacion telefono
	
	if($_POST["telefono"] && !is_numeric($_POST["telefono"]))
	{
			$cont_error++;
			$error_telefono=true;
	}
	
	if($_POST["movil"] && !is_numeric($_POST["movil"]))
	{
			$cont_error++;
			$error_movil=true;
	}
	
	
	//Si todo ok
	if($cont_error==0)
	{
		
		$sql="INSERT INTO Profesores (Nombre,Apellidos,Telefono,Movil,Email,Departamento,Tutor) VALUES ('".$_POST["nombre"]."','".$_POST["apellidos"]."','".$_POST["telefono"]."','".$_POST["movil"]."','".$_POST["email"]."',".$_POST["dep"].",'".$_POST["tut"]."')";
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
		mysql_close();
		header("Location:profesores.php");
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("profesores","A",1);
?>
	<h1>Nuevo Profesor</h1>
	<form action="registro.php" method="post">
	<?
	echo  "Nombre:<br>\n";
	if($error_nombre)
		echo "<input type=\"text\" size=\"50\" name=\"nombre\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" size=\"50\" name=\"nombre\" value=\"".$_POST["nombre"]."\"\n";
	echo "<br>\n";
	echo  "Apellidos:<br>\n";
	if($error_nombre)
		echo "<input type=\"text\" size=\"50\" name=\"apellidos\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" size=\"50\" name=\"apellidos\" value=\"".$_POST["apellidos"]."\"\n";
	echo "<br>\n";
   //telefono
	echo  "Telefono fijo:<br>\n";
	if($error_telefono)
	{
		echo "<input type=\"text\" name=\"telefono\">";
		if($error_telefono) echo "<font color=\"red\">Error en teléfono.</font>\n"; 
		
	}	
	else
		echo "<input type=\"text\" name=\"telefono\" value=\"".$_POST["telefono"]."\"\n";
	echo "<br>\n";
	//movil
	echo  "Telefono movil:<br>\n";
	if($error_movil)
	{
		echo "<input type=\"text\" name=\"movil\">";
		if($error_movil) echo "<font color=\"red\">Error en teléfono movil.</font>\n"; 
	}	
	else
		echo "<input type=\"text\" name=\"movil\" value=\"".$_POST["movil"]."\"\n";
	echo "<br>\n";
	

	echo  "Correo electronico:<br>\n";
	if($error_email)
	{
		echo "<input type=\"text\" size=\"30\" name=\"email\">";
		if ($error_email) echo " <font color=\"red\">Correo electronico incorrecto.</font>\n";
	}	
	else
		echo "<input type=\"text\" size=\"30\" name=\"email\" value=\"".$_POST["email"]."\"\n";
	echo "<br>\n";
	
	echo  "Departamento:<br>\n";
	
	combo("Departamentos","dep",1,2);
	echo "<br>\n";
	echo  "Tutoria:<br>\n";
	
$sql="select Unidad from Alumnos group by Unidad";
$result=mysql_query($sql);


echo "<select  name=\"tut\" size=\"0\">";
echo "<option value=\"\"></option>";
while($row=mysql_fetch_array($result))
{
		
	if($row["Unidad"]==$_POST["tut"])
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
}
echo "</select>\n";
	echo "<br>\n";
	
	
	?>
	<br>
	<br>
	<input type="submit" value="Listo!!!">
	</form>
<?
}include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>
