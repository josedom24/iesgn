<?php
/*
**----------------------------No Borrar esta sección -------------------------
** Paginador 
** PaginaciOn de resultados de consultas a MySqli con PHP
**
** Versión 1.5.20
**
** Nombre de archivo :
** paginator.inc.php
**
**
** Autor:
** Jorge Pinedo Rosas (jpinedo)	<jorpinedo@yahoo.es>
** Con la colaboración de los usuarios del foro de PHP de www.forosdelweb.com
** Especialmente de dooky que posteó el código en el que se basa este script.
**
**	
** Descripción :
** Devuelve el resultado de una consulta sqli por paginas, así como los enlaces de navegación respectivos.
** Este script ha sido pensado con fines didácticos, por eso la gran cantidad de comentarios. 
**
** Licencia : 
** GPL con las siguientes extensiones:
** Úselo con el fin que quiera (personal o lucrativo).
**
**----------------------------------------------------------------------*/

/*----------------------------------------------------------------------
** Historial:
**
** Versión 1.0.0 (30/11/2003): -Versión inicial.
** Versión 1.5.20 (03/05/2020): -Se actualizo el codigo para usarse en consultas mysqli para mas seguridad
** ya que mysql quedo obsoleta codigo modificado por (elflacocanson@gmail.com).
**
**-----------------------------------------------------------------------------------------------------------*/


/**
* Variables que se pueden definir antes de incluir el script via include():
* ------------------------------------------------------------------------
* $_pagi_sqli OBLIGATORIA. Cadena. Debe contener una sentencia sql valida (y sin la clausula "limit").

* $_pagi_cuantos OPCIONAL. Entero. Cantidad de registros que contendra como maximo cada pagina.
Por defecto está en 20.

* $_pagi_nav_num_enlaces OPCIONAL Entero. Cantidad de enlaces a los números de página que se mostrarán como 
máximo en la barra de navegación.
Por defecto se muestran todos.

* $_pagi_mostrar_errores OPCIONAL Booleano. Define si se muestran o no los errores de MySQL que se puedan producir.
Por defecto está en "true";

* $_pagi_propagar OPCIONAL Array de cadenas. Contiene los nombres de las variables que se quiere propagar
por el url. Por defecto se propagarán todas las que ya vengan por el url (GET).
* $_pagi_conteo_alternativo OPCIONAL Booleano. Define si se utiliza ->num_rows; (true) o (*) (false).
Por defecto está en false.
* $_pagi_separador OPCIONAL Cadena. Cadena que separa los enlaces numéricos en la barra de navegación entre páginas.
Por defecto se utiliza la cadena " | ".
* $_pagi_nav_estilo OPCIONAL Cadena. Contiene el nombre del estilo CSS para los enlaces de paginación.
Por defecto no se especifica estilo.
* $_pagi_nav_anterior OPCIONAL Cadena. Contiene lo que debe ir en el enlace a la página anterior. Puede ser un tag <img>.
Por defecto se utiliza la cadena "&laquo; Anterior".
* $_pagi_nav_siguiente OPCIONAL Cadena. Contiene lo que debe ir en el enlace a la página siguiente. Puede ser un tag <img>.
Por defecto se utiliza la cadena "Siguiente &raquo;"
* $_pagi_nav_primera OPCIONAL Cadena. Contiene lo que debe ir en el enlace a la primera página. Puede ser un tag <img>.
Por defecto se utiliza la cadena "&laquo;&laquo; Primera".
* $_pagi_nav_ultima OPCIONAL Cadena. Contiene lo que debe ir en el enlace a la página siguiente. Puede ser un tag <img>.
Por defecto se utiliza la cadena "&Uacute;ltima &raquo;&raquo;"
--------------------------------------------------------------------------
*/


/*
* Verificación de los parámetros obligatorios y opcionales.
*------------------------------------------------------------------------
*/
if(empty($_pagi_sqli))
{
	// Si no se definió $_pagi_sqli... grave error!
	// Este error se muestra si o si (ya que no es un error de mysqli)
	die("<b>Error Paginator : </b>No se ha definido la variable \$_pagi_sqli");
}

if(empty($_pagi_cuantos))
{
	// Si no se ha especificado la cantidad de registros por página
	// $_pagi_cuantos será por defecto 20
	$_pagi_cuantos = 20;
}

if(!isset($_pagi_mostrar_errores))
{
	// Si no se ha elegido si se mostrará o no errores
	// $_pagi_errores será por defecto true. (se muestran los errores)
	$_pagi_mostrar_errores = true;
}

if(!isset($_pagi_conteo_alternativo))
{
	// Si no se ha elegido el tipo de conteo
	// Se realiza el conteo dese mySQLI con (*)
	$_pagi_conteo_alternativo = false;
}

if(!isset($_pagi_separador))
{
	// Si no se ha elegido un separador
	// Se toma el separador por defecto.
	$_pagi_separador = " | ";
}

if(isset($_pagi_nav_estilo))
{
	// Si se ha definido un estilo para los enlaces, se genera el atributo "class" para el enlace
	$_pagi_nav_estilo_mod = "class=\"$_pagi_nav_estilo\"";
}
else
{
	// Si no, se utiliza una cadena vacía.
	$_pagi_nav_estilo_mod = "";
}

if(!isset($_pagi_nav_anterior))
{
	// Si no se ha elegido una cadena para el enlace "siguiente"
	// Se toma la cadena por defecto.
	$_pagi_nav_anterior = "&laquo; Anterior<br>";
} 

if(!isset($_pagi_nav_siguiente))
{
	// Si no se ha elegido una cadena para el enlace "siguiente"
	// Se toma la cadena por defecto.
	$_pagi_nav_siguiente = "<br>Siguiente &raquo;";
} 

if(!isset($_pagi_nav_primera))
{
	// Si no se ha elegido una cadena para el enlace "primera"
	// Se toma la cadena por defecto.
	$_pagi_nav_primera = "&laquo;&laquo; Primera";
} 

if(!isset($_pagi_nav_ultima))
{
	// Si no se ha elegido una cadena para el enlace "siguiente"
	// Se toma la cadena por defecto.
	$_pagi_nav_ultima = "&Uacute;ltima &raquo;&raquo;";
} 

//------------------------------------------------------------------------


/*
* Establecimiento de la página actual.
*------------------------------------------------------------------------
*/
if(empty($_GET['_pagi_pg']))
{
	// Si no se ha hecho click a ninguna página específica
	// O sea si es la primera vez que se ejecuta el script
	// $_pagi_actual es la pagina actual-->será por defecto la primera.
	$_pagi_actual = 1;
}
else
{
	// Si se "pidió" una página específica:
	// La página actual será la que se pidió.
	$_pagi_actual = $_GET['_pagi_pg'];
}
//------------------------------------------------------------------------


/*
* Establecimiento del número de páginas y del total de registros.
*------------------------------------------------------------------------
*/
// Contamos el total de registros en la BD (para saber cuántas páginas serán)
// La forma de hacer ese conteo dependerá de la variable $_pagi_conteo_alternativo
if($_pagi_conteo_alternativo == false)
{
	$_pagi_sqliConta = @preg_replace("/(select[[:space:]]*[[:space:]]from)/i", "SELECT * FROM", $_pagi_sqli);
	$_pagi_result2 = $mysqli->query($_pagi_sqliConta);
	// Si ocurrió error y mostrar errores está activado
	if($_pagi_result2 == false && $_pagi_mostrar_errores == true)
	{
		die ("Error en la consulta de conteo de registros: $_pagi_sqliConta. Mysqli dijo: <b>".$mysqli->error."</b>");
	}
	$_pagi_totalReg = $_pagi_result2->num_rows; //total de registros
}
else
{
	$_pagi_result3 = $mysqli->query($_pagi_sqli);
	// Si ocurrió error y mostrar errores está activado
	if($_pagi_result3 == false && $_pagi_mostrar_errores == true)
	{
		die ("Error en la consulta de conteo alternativo de registros: $_pagi_sqli. Mysqli dijo: <b>".$mysqli->error."</b>");
	}
	$_pagi_totalReg=$_pagi_result3->num_rows;
}
// Calculamos el número de páginas (saldrá un decimal)
// con ceil() redondeamos y $_pagi_totalPags será el número total (entero) de páginas que tendremos
$_pagi_totalPags = ceil($_pagi_totalReg / $_pagi_cuantos);

//------------------------------------------------------------------------


/*
* Propagación de variables por el URL.
*------------------------------------------------------------------------
*/
// La idea es pasar también en los enlaces las variables hayan llegado por url.
$_pagi_enlace = $_SERVER['PHP_SELF'];
$_pagi_query_string = "?";

if(!isset($_pagi_propagar))
{
	//Si no se definió qué variables propagar, se propagará todo el $_GET (por compatibilidad con versiones anteriores)
	//Perdón... no todo el $_GET. Todo menos la variable (_pagi_pg)
	if(isset($_GET['_pagi_pg'])) unset($_GET['_pagi_pg']); // Eliminamos esa variable del $_GET
	$_pagi_propagar = array_keys($_GET);
} elseif(!is_array($_pagi_propagar))
{
	// si $_pagi_propagar no es un array... grave error!
	die("<b>Error Paginator : </b>La variable \$_pagi_propagar debe ser un array");
}

foreach($_pagi_propagar as $var)
{
	if(isset($GLOBALS[$var]))
	{
		// Si la variable es global al script
		$_pagi_query_string.= $var."=".$GLOBALS[$var]."&";
	} elseif(isset($_REQUEST[$var]))
	{
		// Si no es global (o register globals está en OFF)
		$_pagi_query_string.= $var."=".$_REQUEST[$var]."&";
	}
}

// Aсadimos el query string a la url.
$_pagi_enlace .= $_pagi_query_string;

//------------------------------------------------------------------------


/*
* Generación de los enlaces de paginación.
*------------------------------------------------------------------------
*/
// La variable $_pagi_navegacion contendrá los enlaces a las páginas.
$_pagi_navegacion_temporal = array();
if ($_pagi_actual != 1)
{
	// Si no estamos en la página 1. Ponemos el enlace "primera"
	$_pagi_url = 1; //será el número de página al que enlazamos
	$_pagi_navegacion_temporal[] = "<a ".$_pagi_nav_estilo_mod." href='".$_pagi_enlace."_pagi_pg=".$_pagi_url."' class='clase1'>$_pagi_nav_primera</a>";

	// Si no estamos en la página 1. Ponemos el enlace "anterior"
	$_pagi_url = $_pagi_actual - 1; //será el número de página al que enlazamos
	$_pagi_navegacion_temporal[] = "<a ".$_pagi_nav_estilo_mod." href='".$_pagi_enlace."_pagi_pg=".$_pagi_url."' class='clase1'>$_pagi_nav_anterior</a>";
}

// La variable $_pagi_nav_num_enlaces sirve para definir cuántos enlaces con 
// números de página se mostrarán como máximo.
// Ojo: siempre se mostrará un número IMPAR de enlaces. Más info en la documentación.
// Definimos el numero de enlaces a paginas que queremos que aparezcan, 7 en este caso.
// Si queremos que aparezcan todos los enlaces eliminamos la siguiente linea.
$_pagi_nav_num_enlaces = "7";

if(!isset($_pagi_nav_num_enlaces))
{
	// Si no se definió la variable $_pagi_nav_num_enlaces
	// Se asume que se mostrarán todos los números de página en los enlaces.
	$_pagi_nav_desde = 1;//Desde la primera
	$_pagi_nav_hasta = $_pagi_totalPags;//hasta la última
}
else
{
	// Si se definió la variable $_pagi_nav_num_enlaces
	// Calculamos el intervalo para restar y sumar a partir de la página actual
	$_pagi_nav_intervalo = ceil($_pagi_nav_num_enlaces/2) - 1;

	// Calculamos desde qué número de página se mostrará
	$_pagi_nav_desde = $_pagi_actual - $_pagi_nav_intervalo;
	// Calculamos hasta qué número de página se mostrará
	$_pagi_nav_hasta = $_pagi_actual + $_pagi_nav_intervalo;

	// Ajustamos los valores anteriores en caso sean resultados no válidos

	// Si $_pagi_nav_desde es un número negativo
	if($_pagi_nav_desde < 1)
	{
		// Le sumamos la cantidad sobrante al final para mantener el número de enlaces que se quiere mostrar. 
		$_pagi_nav_hasta -= ($_pagi_nav_desde - 1);
		// Establecemos $_pagi_nav_desde como 1.
		$_pagi_nav_desde = 1;
	}
	// Si $_pagi_nav_hasta es un número mayor que el total de páginas
	if($_pagi_nav_hasta > $_pagi_totalPags)
	{
		// Le restamos la cantidad excedida al comienzo para mantener el número de enlaces que se quiere mostrar.
		$_pagi_nav_desde -= ($_pagi_nav_hasta - $_pagi_totalPags);
		// Establecemos $_pagi_nav_hasta como el total de páginas.
		$_pagi_nav_hasta = $_pagi_totalPags;
		// Hacemos el último ajuste verificando que al cambiar $_pagi_nav_desde no haya quedado con un valor no válido.
		if($_pagi_nav_desde < 1)
		{
			$_pagi_nav_desde = 1;
		}
	}
}

for ($_pagi_i = $_pagi_nav_desde; $_pagi_i<=$_pagi_nav_hasta; $_pagi_i++)
{//Desde página 1 hasta última página ($_pagi_totalPags)
	if ($_pagi_i == $_pagi_actual)
	{
		// Si el número de página es la actual ($_pagi_actual). Se escribe el número, pero sin enlace y en negrita.
		$_pagi_navegacion_temporal[] = "<span ".$_pagi_nav_estilo_mod.">$_pagi_i</span>";
	}
	else
	{
		// Si es cualquier otro. Se escibe el enlace a dicho número de página.
		$_pagi_navegacion_temporal[] = "<a ".$_pagi_nav_estilo_mod." href='".$_pagi_enlace."_pagi_pg=".$_pagi_i."' class='clase1'>".$_pagi_i."</a>";
	}
}

if ($_pagi_actual < $_pagi_totalPags)
{
	// Si no estamos en la última página. Ponemos el enlace "Siguiente"
	$_pagi_url = $_pagi_actual + 1; //será el número de página al que enlazamos
	$_pagi_navegacion_temporal[] = "<a ".$_pagi_nav_estilo_mod." href='".$_pagi_enlace."_pagi_pg=".$_pagi_url."' class='clase1'>$_pagi_nav_siguiente</a>";

	// Si no estamos en la última página. Ponemos el enlace "última"
	$_pagi_url = $_pagi_totalPags; //será el número de página al que enlazamos
	$_pagi_navegacion_temporal[] = "<a ".$_pagi_nav_estilo_mod." href='".$_pagi_enlace."_pagi_pg=".$_pagi_url."' class='clase1'>$_pagi_nav_ultima</a>";
}
$_pagi_navegacion = implode($_pagi_separador, $_pagi_navegacion_temporal);

//------------------------------------------------------------------------


/*
* Obtención de los registros que se mostrarán en la página actual.
*------------------------------------------------------------------------
*/
// Calculamos desde qué registro se mostrará en esta página
// Recordemos que el conteo empieza desde CERO.
$_pagi_inicial = ($_pagi_actual-1) * $_pagi_cuantos;

// Consulta SQLI. Devuelve $cantidad registros empezando desde $_pagi_inicial
$_pagi_sqlLim = $_pagi_sqli." LIMIT $_pagi_inicial,$_pagi_cuantos";
$_pagi_result = $mysqli->query($_pagi_sqlLim);
// Si ocurrió error y mostrar errores está activado
if($_pagi_result == false && $_pagi_mostrar_errores == true)
{
	die ("Error en la consulta limitada: $_pagi_sqlLim. Mysqli dijo: <b>".$mysqli->error."</b>");
}

//------------------------------------------------------------------------


/*
* Generación de la información sobre los registros mostrados.
*------------------------------------------------------------------------
*/
// Número del primer registro de la página actual
$_pagi_desde = $_pagi_inicial + 1;

// Número del último registro de la página actual
$_pagi_hasta = $_pagi_inicial + $_pagi_cuantos;
if($_pagi_hasta > $_pagi_totalReg)
{
	// Si estamos en la última página
	// El ultimo registro de la página actual será igual al número de registros.
	$_pagi_hasta = $_pagi_totalReg;
}

$_pagi_info = "desde el <b>$_pagi_desde</b> hasta el <b>$_pagi_hasta</b> de un total de <u>$_pagi_totalReg</u>";

//------------------------------------------------------------------------


/**
* Variables que quedan disponibles después de incluir el script vía include():
* ------------------------------------------------------------------------

* $_pagi_result Identificador del resultado de la consulta a la BD para los registros de la página actual. 
Listo para ser "pasado" por una función como ->num_rows;, ->fetch_assoc(), 
->fetch_assoc();, etc.

* $_pagi_navegacion Cadena que contiene la barra de navegación con los enlaces a las diferentes páginas.
Ejemplo: "<<primera | <anterior | 1 | 2 | 3 | 4 | siguiente> | última>>".

* $_pagi_info Cadena que contiene información sobre los registros de la página actual.
Ejemplo: "desde el 16 hasta el 30 de un total de 123"; 

*/
?>
