<?
$opciones=array("resumen"=>array(array(
																		array("impresora/list.php?l=1&f=".$fich."\" id=\"primero\">Imprimir","V"),
																		array("impresora/carta.php?l=1&f=".$_POST["fecha"]."\">Cartas tipos","V"),
																		array("editor/index.php?l=1\">Editar cartas","E")
																	  ),"partes"),
							"datossecretaria"=>array(array(
																		array("registro/nuevodato.php?tipo=".$_GET["tipo"]."&t=".$_GET["t"]."&t1=".$_GET["t1"]."\" id=\"primero\">Nuevo dato","A"),
																		array("registro/registro.php?tipo=e\">Entrada","V"),
																		array("registro/registro.php?tipo=s\">Salida","V")
																	  ),"secretaria"),
							"resumen"=>array(array(
																		array("impresora/list.php?l=1&f=".$fich."\" id=\"primero\">Imprimir","V"),
																		array("impresora/carta.php?l=1&f=".$_POST["fecha"]."\">Cartas tipos","V"),
																		array("editor/index.php?l=1\">Editar cartas","E")
																	  ),"partes"),										  
							"resumen_san"=>array(array(
																		array("impresora/list.php?l=3&f=".$fich."\" id=\"primero\">Imprimir","V"),
																		array("editor/index.php?l=3\">Editar cartas","E")
																	  ),"partes"),
																	
																	
							"resumen_cit"=>array(array(
																		array("editor/index.php?l=6\" id=\"primero\">Editar citación","E")
																		
																	  ),"partes"),																	
							"historial"=>array(array(
																		array("impresora/list.php?l=4&f=".$fich."\" id=\"primero\">Imprimir","V")
																		
																		
																	  ),"partes"),
							"mayoredad"=>array(array(
																		array("impresora/list.php?l=2&f=".$fich."\" id=\"primero\">Imprimir","V"),
																		array("impresora/carta.php?l=2\">Carnets","V"),
																		array("editor/index.php?l=2\">Editar carnets","E")
																	  ),"partes"),
							"alumnos"=>array(array(
																		array("alumnos/resumen.php\" id=\"primero\">Resumen de amonestaciones","V"),
																		array("alumnos/resumen_cit.php\">Resumen de citaciones","V"),
																		array("alumnos/resumen_san.php\">Resumen de sanciones","V"),
																		array("impresora/list.php?l=8&f=".$fich."\">Imprimir","V")
																	  ),"alumnos"),
							"profesores"=>array(array(
																		array("profesores/listados.php\" id=\"primero\">Listados","V")
																		
																		),"profesores"),
							"registro"=>array(array(
																		array("registro/nuevoregistro.php?tipo=".$tipo."\" id=\"primero\">Nuevo","A"),
																		array("impresora/list.php?l=9&f=".$fich."\" >Imprimir","V"),
						
																	),"registro"),
							"libros"=>array(array(
									array("impresora/list.php?uni=".$uni."&cab=1&l=7&f=".$fich."\" id=\"primero\">Imprimir","V"),
        								array("impresora/carta.php?l=4&uni=".$uni."\">Revisión Alumnos","V"),
									array("impresora/carta.php?l=7&uni=".$uni."\">Revisión Libros","V"),
									array("editor/index.php?l=4\">Editar revisión alumnos","E"),
									array("editor/index.php?l=7\">Editar revisión libros","E")
																	  ),"libros"),	
							"faltas"=>array(array(
																		array("editor/index.php?l=5\" id=\"primero\">Editar Partes","E")
																	  ),"faltas"),
							"contabilidad"=>array(array(
																		array("contabilidad/nuevo.php?cg=".$cg."\" id=\"primero\">Nuevo","A")
																	  ),"contabilidad"),  
							);

echo "<center>";
echo "<div id=\"menuh\">";
echo "<ul>";
foreach($opciones[$menu][0] as $v)
{
	if(permisos($opciones[$menu][1],$v[1])) echo "<li><a href=\"".url().$v[0]."</a></li>";
}
echo "</ul></div><br><br></center>";
?>

