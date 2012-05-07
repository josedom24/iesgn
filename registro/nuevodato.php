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
	
		//Si todo ok
	if($cont_error==0)
	{
		$sql="insert into ".$_POST["t"]."(".$_POST["t"].") values ('".$_POST['nombre']."')";
		
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));		
		mysql_close();
		header("Location:datos.php?t=".$_POST["t"]);
		
	
	}	
}

if(!$_POST || $cont_error>0)
{
	include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
	permisos("secretaria","A",1);
	if(!$_POST && $_GET)
	{
		$cid="Id".strtolower(substr($_GET["t"],0,1));
	}
?>
	<h1>Nuevo <?if($_GET["tipo"]=="e") echo $_GET["t"]; else echo $_GET["t1"];?></h1>
	<form action="nuevodato.php" method="post">
	<?
	
	echo "<input type=\"hidden\" name=\"campoid\"  value=\"".$cid."\">";
	echo "<input type=\"hidden\" name=\"t\"  value=\"".$_GET["t"]."\">";

	if($_GET["tipo"]=="e") echo  $_GET["t"].":<br>\n"; else echo  $_GET["t1"].":<br>\n";
	
	if($error_nombre)
		echo "<input type=\"text\" name=\"nombre\"> <font color=\"red\">Debes indicar el nombre.</font>\n";
	else
		echo "<input type=\"text\" name=\"nombre\" value=\"".$_POST["Nombre"]."\">\n";
	echo "<br>\n";

	
	?>
	<br>
	<br>
	<input type="submit" value="Listo!!!">
	</form>
<?
}include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");

?>





		
