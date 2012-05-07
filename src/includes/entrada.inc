<?
if(!$_SESSION["usuario"])
{?>

	<form action="<?echo url();?>login.php" method="post">
	Usuario:<br>
	<input type="text" name="usuario" value="<?echo $_COOKIE["ult_usuario"]?>">
	<?if($_GET["error"]==1) echo "<br>Usuario incorrecto.";?>
	<br>Password::<br>
	<input type="password" name="pass">
	<?if($_GET["error"]==2) echo "<br>Password incorrecto.";?>
	<br><br>
	<input type="checkbox" name="recordar">Recuerda usuario<br><br>
	<input type="submit" value="Entrar">
	<br>
	</form>
<?
}
else
{
	echo "<font size=\"1\">Beinvenid@,";
	echo $_SESSION["usuario"]."<br>";
	echo "(".perfil($_SESSION["perfil"]).")";
	echo "</font>";
}
	
?>
