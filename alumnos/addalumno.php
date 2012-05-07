<?
session_start();
if($_POST)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");
	require_once ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/verify.inc");
	permisos("alumnos","E",1);
	$cont_error=0;
	

	//Si todo ok
	
	if($_POST["nom"]=="")
	{
		$error_nom=true;
		$cont_error++;
	}
	
	if($cont_error==0)
	{
		$txt="";
		foreach($_POST as $v)
		   $txt=$txt."'".$v."',";
		$txt=substr($txt,0,-1);
		$sql="INSERT INTO Alumnos(Unidad,Nombre,DNI,Direccion,Localidad,Provincia,CodPostal,Ap1tutor,Ap2tutor,Nomtutor) values (".$txt.")";
		mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));		
		mysql_close();
		header("Location:alumnos.php");
		
	
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
}

if(!$_POST || $cont_error>0)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
	permisos("alumnos","E",1);
	if($_GET) $valor_unidad=$_GET["uni"];
?>
	<h1>Añadir Alumno</h1>
	<form action="addalumno.php" method="post">
	<?
	

	echo "<br><center><table>";
	echo '<input type="hidden" name="uni" value="'.$valor_unidad.'" /></td>';

	echo "</td></tr>";
	echo "<tr>";
	echo "<tr><td>Unidad:".$valor_unidad."</td></tr>";

	if($error_nom) echo "Debes indicar un nombre.";

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
