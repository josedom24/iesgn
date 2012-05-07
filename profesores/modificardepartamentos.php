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
	if($_POST["abr"]=="")
	{
		$cont_error++;
		$error_abr=true;
	}
	
		//Si todo ok
	if($cont_error==0)
	{
		$sql="UPDATE Departamentos SET Departamento='".$_POST['nombre']."', Abr='".$_POST['abr']."' WHERE Id=".$_POST["id"];
		
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));		
		mysql_close();
		header("Location:departamentos.php");
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");
	if(!$_POST && $_GET)
	{
		
		
		$sql="SELECT * FROM Departamentos WHERE Id=".$_GET['id'];
		$result=mysql_query($sql) or die("error:".$sql."<br>".mysql_error());
		
		if ($row=mysql_fetch_array($result))
		{
			$valor_nombre=$row['Departamento'];
			$valor_abr=$row['Abr'];
			$valor_modificar=$row['Id'];
		}
		
	}
	else
	{
			$valor_nombre=$_POST['nombre'];
			$valor_abr=$_POST['abr'];
			
			$valor_modificar=$_POST["id"];
			
			
	}

?>
	<h1>Modificar Departamentos</h1>
	<form action="modificardepartamentos.php" method="post">
	<?
	echo "<input type=\"hidden\" name=\"id\"  value=\"".$valor_modificar."\">";
	

	echo  "Abreviatura:<br>\n";
	if($eroor_abr)
		echo "<input type=\"text\" name=\"abr\"> <font color=\"red\">Debes indicar la abreviatura.</font>\n";
	else
		echo "<input type=\"text\" name=\"abr\" value=\"".$valor_abr."\">\n";
	echo "<br>\n";
	echo  "Nombre:<br>\n";
	if($error_nombre)
		echo "<input type=\"text\" name=\"nombre\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" name=\"nombre\" value=\"".$valor_nombre."\">\n";
	echo "<br>\n";

	
	?>
	<br>
	<br>
	<input type="submit" value="Listo!!!">
	</form>
<?
}include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>