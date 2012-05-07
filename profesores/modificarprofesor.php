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
		$sql="UPDATE Profesores SET Nombre='".$_POST['nombre']."',Apellidos='".$_POST['apellidos']."', Telefono='".$_POST['telefono']."',Movil='".$_POST['movil']."', Email='".$_POST['email']."',Departamento=".$_POST['dep'].",Tutor='".$_POST["tut"]."' WHERE Id='".$_POST['Id']."'";
		
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));		
		mysql_close();
		header("Location:profesores.php");
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("profesores","E",1);
	if(!$_POST && $_GET)
	{
		
		
		$sql="SELECT * FROM Profesores WHERE Id=".$_GET['id'];
		
		$result=mysql_query($sql) or die("error:".$sql."<br>".mysql_error());
		
		if ($row=mysql_fetch_array($result))
		{
			$valor_nombre=$row['Nombre'];
			$valor_apellidos=$row['Apellidos'];
			$valor_telefono=$row['Telefono'];
			$valor_movil=$row['Movil'];
			$valor_email=$row['Email'];
			$valor_dep=$row['Departamento'];
			$valor_tut=$row['Tutor'];
			$valor_usuario_modificar=$row['Id'];
		}
		
	}
	else
	{
			$valor_nombre=$_POST['nombre'];
			$valor_apellidos=$_POST['apellidos'];
			$valor_telefono=$_POST["telefono"];
			$valor_movil=$_POST['movil'];
			$valor_email=$_POST['email'];
			$valor_dep=$_POST['dep'];
			$valor_tut=$_POST["tut"];
			$valor_usuario_modificar=$_POST["Id"];
			
			
	}

?>
	<h1>Modificar Profesor</h1>
	<form action="modificarprofesor.php" method="post">
	<?
	echo "<input type=\"hidden\" name=\"Id\"  value=\"".$valor_usuario_modificar."\">";
	
	echo  "Nombre:<br>\n";
	if($error_nombre)
		echo "<input type=\"text\" size=\"50\" name=\"nombre\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" size=\"50\" name=\"nombre\" value=\"".$valor_nombre."\"\n";
	echo "<br>\n";
	echo  "Apellidos:<br>\n";
	if($error_nombre)
		echo "<input type=\"text\" size=\"50\" name=\"apellidos\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" size=\"50\" name=\"apellidos\" value=\"".$valor_apellidos."\"\n";
	echo "<br>\n";
   //telefono
	echo  "Telefono fijo:<br>\n";
	if($error_telefono)
	{
		echo "<input type=\"text\" name=\"telefono\">";
		if($error_telefono) echo "<font color=\"red\">Error en teléfono.</font>\n"; 
		
	}	
	else
		echo "<input type=\"text\" name=\"telefono\" value=\"".$valor_telefono."\"\n";
	echo "<br>\n";
	//movil
	echo  "Telefono movil:<br>\n";
	if($error_movil)
	{
		echo "<input type=\"text\" name=\"movil\">";
		if($error_movil) echo "<font color=\"red\">Error en teléfono movil.</font>\n"; 
	}	
	else
		echo "<input type=\"text\" name=\"movil\" value=\"".$valor_movil."\"\n";
	echo "<br>\n";
	

	echo  "Correo electronico:<br>\n";
	if($error_email)
	{
		echo "<input type=\"text\" size=\"30\" name=\"email\">";
		if ($error_email) echo " <font color=\"red\">Correo electronico incorrecto.</font>\n";
	}	
	else
		echo "<input type=\"text\" size=\"30\" name=\"email\" value=\"".$valor_email."\"\n";
	echo "<br>\n";
	
	echo  "Departamento:<br>\n";
	
	
	combo("Departamentos","dep",1,2,$valor_dep);
	echo "<br>\n";
	echo  "Tutoria:<br>\n";
			$sql="select Unidad from Alumnos group by Unidad";
$result=mysql_query($sql);

echo "<select  name=\"tut\" size=\"0\">";
echo "<option value=\"\"></option>";
while($row=mysql_fetch_array($result))
{
		
	if($row["Unidad"]==$valor_tut)
			echo "<option selected ";
		else
			echo "<option "; 
		echo " value=\"".$row["Unidad"]."\">".$row["Unidad"]."</option>\n";
}
echo "</select>\n";
	
	?>
	
	
	<br>
	<br>
	<input type="submit" value="Listo!!!">
	</form>
<?
}include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>





		
