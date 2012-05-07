<?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("profesores");
$sql="select * from Departamentos,Profesores where Profesores.Departamento=Departamentos.Id order by ".ordenar_bien("Apellidos");
//Crea los ficheros de texto necesario para el listado
$fich=escribir_fich("5",$sql);
$sql="select * from Profesores where Baja=0 order by ".ordenar_bien("Apellidos");
//Crea los ficheros de texto necesario para el listado
$fich2=escribir_fich("6",$sql);
//$sql="select * from Profesores where Tutor is not null order by Tutor";
//$fich3=escribir_fich("7",$sql);

	echo "<center><h2>Listado de Profesores</h2></center>";

	echo "<li><a href=\"".url()."impresora/list.php?cab=1&l=5&f=".$fich."\" id=\"primero\">Datos generales</a></li>";
	echo "<br>";
	echo "<li><a href=\"".url()."impresora/list.php?cab=1&l=6&f=".$fich2."\" id=\"primero\">Asistencia a claustro</a></li>";
	echo "<br>";
	//echo "<li><a href=\"".url()."impresora/list.php?cab=1&l=7&f=".$fich3."\" id=\"primero\">Lista de tutores</a></li>";
	//echo "<br>";
	


include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>