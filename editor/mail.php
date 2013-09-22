<?php
session_start();?>
<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/FCKeditor/fckeditor.php");
permisos("mail");

?>

<html>
  <head>
    <title>Mail - IES Gonzalo Nazareno</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <body>
    <form action="correo.php" method="post" enctype="multipart/form-data">
	
<table border=0>
<tr align="left">
<td></td>
<td>
<p align="left">
Asunto:<br>
<input type="text" size="70" name="asunto">
<?php

include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/config.inc");

$oFCKeditor = new FCKeditor('FCKeditor1') ;
$oFCKeditor->BasePath = '../FCKeditor/';
$oFCKeditor->Value = $_GET["msg"];
$oFCKeditor->Create() ;
?>
</p>      <br>
Adjunto:<input size="70" name="file" type="file" />
</td>
</tr>
</table>

	<br>
	      <input type="submit" name ="todos" value="Enviar a todos los Profesores">
		  <input type="submit" name="select" value="Seleccionar Profesores">  
	</form>  
 <?include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");?>

