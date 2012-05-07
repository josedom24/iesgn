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
		$sql="insert into Departamentos (Abr,Departamento) values ('".$_POST['abr']."','".$_POST['nombre']."')";
		
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));		
		mysql_close();
		header("Location:departamentos.php");
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");
	
			$valor_nombre=$_POST['nombre'];
			$valor_abr=$_POST['abr'];
			$valor_modificar=$_POST["id"];
?>
	<h1>Nuevo Departamento</h1>
	<form action="adddepartamentos.php" method="post">
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
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
}
?>