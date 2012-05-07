<?if($_SESSION["usuario"]){
$opciones=array("Alumnos"=>array(array(
																		array("alumnos/alumnos.php\">Gestión","alumnos"),
																		array("alumnos/resumen.php\">Resumen de amonestaciones","partes"),
																		array("alumnos/resumen_cit.php\">Resumen de citaciones","partes"),
																		array("alumnos/resumen_san.php\">Resumen de sanciones","partes"),
																		array("alumnos/faltas.php\">Control de Faltas","faltas"),
																		array("alumnos/compdiv.php\">Comp-Div.","alumnos"),
																		array("libros/libros.php\">Libros de Texto","libros"),
																		array("alumnos/mayoredad.php\">Mayores de edad","mayor")
																	  ),"menuAlumnos"),
							"Profesores"=>array(array(
																		array("profesores/profesores.php\">Gestión","profes"),
																		array("profesores/listados.php\">Listados","profes")
																	  ),"menuProfes"),
							"Secretaría"=>array(array(
																		array("registro/registro.php?tipo=e\">Entrada","secretaria"),
																		array("registro/registro.php?tipo=s\">Salida","secretaria")
																	  ),"menuSecre"),
							"Administración"=>array(array(
																		array("admin/importar.php\">Importar datos","admin"),
																		array("admin/copia.php\">Copia Seguridad","admin"),

																		array("usuarios/usuarios.php\">Usuarios","admin"),
																		array("libros/listalibros.php\">Libros","admin"),
																		array("profesores/departamentos.php\">Departamentos","admin"),
																		array("contabilidad/centrosgastos.php\">Centros de Gastos","admin"),
																	  ),"menuAdmin"),
							"Contabilidad"=>array(array(
																		array("contabilidad/contabilidad.php\">Contabilidad","admin"),
																		array("contabilidad/fotocopias.php\">Fotocopias","admin")
																	),"menuCont")
							);
							


echo "<br>";
echo "<ul class=\"menulist\" id=\"listMenuRoot\">";
foreach($opciones as $tit=>$vr)
{
	if(permisos($vr[1],"V"))
	{
	echo "<li><a href=\"#\">".$tit."</a>";
	echo "<ul>";
	foreach($vr[0] as $opc)
		{
			if(permisos($opc[1],"V")) echo "<li><a href=\"".url().$opc[0]."</a></li>";
		}
	echo "</ul>";
	echo "</li>";
	}
}
echo "<li><a href=\"".url()."logout.php\">Desconectar</a>";
echo "</li>";
echo "</ul>";
echo "<br>";
}
?>

<script type="text/javascript">
var listMenu = new FSMenu('listMenu', true, 'display', 'block', 'none');

listMenu.animations[listMenu.animations.length] = FSMenu.animFade;
listMenu.animations[listMenu.animations.length] = FSMenu.animSwipeDown;
var arrow = null;
if (document.createElement && document.documentElement)
{
 arrow = document.createElement('span');
 arrow.appendChild(document.createTextNode('>'));
 // Feel free to replace the above two lines with these for a small arrow image...
 //arrow = document.createElement('img');
 //arrow.src = 'arrow.gif';
 //arrow.style.borderWidth = '0';
 arrow.className = 'subind';
}
addEvent(window, 'load', new Function('listMenu.activateMenu("listMenuRoot", arrow)'));




</script>
