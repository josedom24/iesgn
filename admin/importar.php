<?if($_FILES)
{
		//Gestion de archivo
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/funciones.inc");
		include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");

		$nombre=$_FILES["archivo"]["name"];		
		$tipo=$_FILES["archivo"]["type"];
		$tam=$_FILES["archivo"]["size"];
		$fichero=$_FILES["archivo"]["tmp_name"];
		if($fichero!="none" && $tipo=="text/plain")
		{
		
			move_uploaded_file($fichero,path()."tmp/alumnos");
 			$lines = file(path().'tmp/alumnos');
			
			foreach ($lines as $line_num => $line)
			{
				$datos = explode("|", $line);
				if(sizeof($datos)!=11)
			{
					echo "Error, formato incorrecto.";
					echo "<br><a href=\"../index.php\">Volver</a>";
					die("");
			}
				//$datos[0]=substr($datos[0],1);
				$datos[sizeof($datos)-1]=substr($datos[sizeof($datos)-1],0,strlen($datos[sizeof($datos)-1])-3);
				$sql="insert into Alumnos (Nombre,Dni,Direccion,CodPostal,Localidad,Fecha_nacimiento,Provincia,Unidad,Ap1tutor,Ap2tutor,Nomtutor,Telefono1,Telefono2) values (";
				$sql2="";
				foreach($datos as $d)		
				{
					if(valida_fecha(trim($d))) $d=cambiaf_a_mysql(trim($d));
					$sql2=$sql2."'".trim($d)."',";
				}
				$sql2=substr($sql2,0,strlen($sql2)-1);
				$sql=$sql.$sql2.")";				
				//echo $sql;
				mysql_query($sql) or die("Error en SQL:".$sql."<br>".mysql_error($bd));	
			}
			header("Location:../index.php");
		}
		else
		{
			echo "Se esperaba un fichero de texto plano.<br>";
			echo "<a href=\"index.php\">Volver</a>";
			die("");
		}
}
else
{
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");
?>
<form ENCTYPE="multipart/form-data" action="importar.php" method="post">
<br/><p>Fichero con texto plano con el siguiente formato: Nombre,DNI,Dirección, CodPostal, Localidad, Fecha_nacimiento,Provincia,Unidad,Ap1tutor,Ap2tutor,Nomtutor, Teléfono1, Telefono2</p><br/>
<br/><br/>A continuación quitar las cabeceras del fichero generado y codificar UTF-8.<br/>
<br>Fichero:<br>
<input type="file" name="archivo" size="55">
<br><br>
<input type="submit" value="Listo!!!">
</form>
<?}
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>

