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
	//Validacion de nombre usuario
	if($_POST["usuario"]=="")
	{
		$cont_error++;
		$error_usuario=true;
	}
	
	//Validación: nombre de usuario no repetido en la base de datos
	$sql="SELECT * FROM usuarios WHERE usuario='".$_POST["usuario"]."'";
	$result=mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));
	if($row=mysql_fetch_array($result))
	{
		$cont_error++;
		$error_usuario2=true;
	}
	
	//Validacion de password
	if(strlen($_POST["pass"])<6)
	{
		$cont_error++;
		$error_pass=true;
	}
	if($_POST["pass"]!=$_POST["pass2"])
	{
		$cont_error++;
		$error_pass2=true;
	}
	//Validación correo electronico
	if(!strstr($_POST["email"],".") || !strstr($_POST["email"],"@"))
	{
			$cont_error++;
			$error_email=true;
	}
	
	//Validación: correo electronico no repetido en la base de datos
	
	$sql="SELECT * FROM usuarios WHERE email='".$_POST["email"]."'";
	$result=mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));
	if($row=mysql_fetch_array($result))
	{
		$cont_error++;
		$error_email2=true;
	}

	//Si todo ok
	if($cont_error==0)
	{
		
		$sql="INSERT INTO usuarios VALUES ('".$_POST["nombre"]."','".$_POST["usuario"]."','".md5($_POST["pass"])."','".$_POST["email"]."','".$_POST["perfil"]."')";
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
		mysql_close();
		header("Location:usuarios.php");
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");
?>
	<h1>Nuevo Usuario</h1>
	<form action="registro.php" method="post">
	<?
	echo  "Nombre Completo:<br>\n";
	if($error_nombre)
		echo "<input type=\"text\" name=\"nombre\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" name=\"nombre\" value=\"".$_POST["nombre"]."\"\n";
	echo "<br>\n";

	echo  "Nombre de Usuario:<br>\n";
	if($error_usuario || $error_usuario2)
	{
		echo "<input type=\"text\" name=\"usuario\">";
		if($error_usuario) echo "<font color=\"red\">Debes indicar el nombre de usuario.</font>\n"; 
		if($error_usuario2) echo "<font color=\"red\">El nombre de usuario ya existe.</font>\n";
	}	
	else
		echo "<input type=\"text\" name=\"usuario\" value=\"".$_POST["usuario"]."\"\n";
	echo "<br>\n";
	
	echo  "Password:<br>\n";
	if($error_pass)
		echo "<input type=\"password\" name=\"pass\"> <font color=\"red\">Password incorrecta.</font>\n";
	else
		echo "<input type=\"password\" name=\"pass\">";
	echo "<br>\n";

	echo  "Repita password:<br>\n";
	if($error_pass2)
		echo "<input type=\"password\" name=\"pass2\"> <font color=\"red\">Password no coinciden.</font>\n";
	else
		echo "<input type=\"password\" name=\"pass2\">";
	
	echo "<br>\n";	

	echo  "Correo electronico:<br>\n";
	if($error_email || $error_email2)
	{
		echo "<input type=\"text\" name=\"email\">";
		if ($error_email) echo " <font color=\"red\">Correo electronico incorrecto.</font>\n";
		if ($error_email2) echo " <font color=\"red\">Correo electronico ya esta registrado.</font>\n";
	}	
	else
		echo "<input type=\"text\" name=\"email\" value=\"".$_POST["email"]."\"\n";
	echo "<br>\n";
	
		echo "<br>Perfil:";
		Combo("Perfil","perfil",1,2,"",1);
	
	?>
	<br>
	<br>
	<input type="submit" value="Listo!!!">
	</form>
<?
}include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>
