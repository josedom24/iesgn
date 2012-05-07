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
	if($_POST["usuario"]!=$_POST["usuario_a_modificar"])
	{
		$sql="SELECT * FROM usuarios WHERE usuario='".$_POST["usuario"]."'";
		$result=mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));
		if($row=mysql_fetch_array($result))
		{
			$cont_error++;
			$error_usuario2=true;
		}
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
	if($_POST["email"]!=$_POST["correo_a_modificar"])
	{
		$sql="SELECT * FROM usuarios WHERE email='".$_POST["email"]."'"; 
		$result=mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));
		if($row=mysql_fetch_array($result))
		{
			$cont_error++;
			$error_email2=true;
		}
	}

	//Si todo ok
	if($cont_error==0)
	{
		$sql="UPDATE usuarios SET Nombre='".$_POST['nombre']."', Usuario='".$_POST['usuario']."',Pass='".md5($_POST['pass'])."', Email='".$_POST['email']."',Perfil='".$_POST['perfil']."' WHERE Usuario='".$_POST['usuario_a_modificar']."'";
		
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));		
		mysql_close();
		header("Location:usuarios.php");
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");
	if(!$_POST && $_GET)
	{
		
		
		$sql="SELECT * FROM usuarios WHERE Usuario='".$_GET['id']."'";
		$result=mysql_query($sql) or die("error:".$sql."<br>".mysql_error());
		
		if ($row=mysql_fetch_array($result))
		{
			$valor_nombre=$row['Nombre'];
			$valor_usuario=$row['Usuario'];
			$valor_email=$row['Email'];
			$valor_perfil=$row['Perfil'];
			$valor_usuario_modificar=$row['Usuario'];
			$valor_correo_modificar=$row['Email'];
		}
		
	}
	else
	{
			$valor_nombre=$_POST['nombre'];
			$valor_usuario=$_POST['usuario'];
			$valor_email=$_POST['email'];
			$valor_perfil=$_POST['perfil'];
			$valor_usuario_modificar=$_POST["usuario_a_modificar"];
			$valor_correo_modificar=$row["correo_a_modificar"];
			
	}

?>
	<h1>Modificar Usuario</h1>
	<form action="modificarusuario.php" method="post">
	<?
	echo "<input type=\"hidden\" name=\"usuario_a_modificar\"  value=\"".$valor_usuario_modificar."\">";
			echo "<input type=\"hidden\" name=\"correo_a_modificar\" value=\"".$valor_correo_modificar."\">";
	echo  "Nombre Completo:<br>\n";
	if($error_nombre)
		echo "<input type=\"text\" name=\"nombre\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" name=\"nombre\" value=\"".$valor_nombre."\">\n";
	echo "<br>\n";

	echo  "Nombre de Usuario:<br>\n";
	if($error_usuario || $error_usuario2)
	{
		echo "<input type=\"text\" name=\"usuario\">";
		if($error_usuario) echo "<font color=\"red\">Debes indicar el nombre de usuario.</font>\n"; 
		if($error_usuario2) echo "<font color=\"red\">El nombre de usuario ya existe.</font>\n";
	}	
	else
		echo "<input type=\"text\" name=\"usuario\" value=\"".$valor_usuario."\">\n";
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
		echo "<input type=\"text\" name=\"email\" value=\"".$valor_email."\">\n";
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





		
