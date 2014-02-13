<?php
session_start();
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/FCKeditor/fckeditor.php");
permisos("General");
?>
<script>
function add()
{
	var oEditor = FCKeditorAPI.GetInstance('FCKeditor1') ;
	oEditor.InsertHtml("##"+document.forms[0].campo.value+"##");
}
</script>
<?


?>

    <form action="savedata.php?ir=<? echo $_SERVER["HTTP_REFERER"];?>" method="post">
	<input type="hidden" name="id" value="<? echo $_GET["l"];?>">
<table border=0>
<tr align="left">
<td></td>
<td>
<p align="left">
<?php

include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");

$sql="select * from Cartas where Id=".$_GET["l"];
$result=mysql_query($sql);
$row=mysql_fetch_array($result);
$oFCKeditor = new FCKeditor('FCKeditor1') ;
$oFCKeditor->BasePath = '../FCKeditor/';
$oFCKeditor->Value = utf8_decode($row["Contenido"]);
$oFCKeditor->Create() ;
?>
</p>      <br>
</td>
</tr>
</table>

Datos del alumno:<br>
<SELECT name="campo">
		<OPTION VALUE="Nombre">Nombre</OPTION>
		<OPTION VALUE="DNI">DNI</OPTION>
		<OPTION VALUE="T">Numero de amonestaciones</OPTION>
		<OPTION VALUE="Direccion">Dirección</OPTION>
		<OPTION VALUE="CodPostal">CodPostal</OPTION>
		<OPTION VALUE="Localidad">Localidad</OPTION>
		<OPTION VALUE="Provincia">Provincia</OPTION>
		<OPTION VALUE="Fecha_nacimiento">Fecha de nacimiento</OPTION>
		<OPTION VALUE="Unidad">Unidad</OPTION>
		<OPTION VALUE="Ap1tutor">1er apellido tutor</OPTION>
		<OPTION VALUE="Ap2tutor">2º apellido tutor</OPTION>
		<OPTION VALUE="Nomtutor">Nombre tutor</OPTION>
		<OPTION VALUE="Nomtutor">Nombre tutor</OPTION>
	</SELECT>
	 <input type="button" value="Insertar" onclick="add();">


	 
	<br>
	      <input type="submit" value="Aceptar">
	</form>  
 <?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");?>



