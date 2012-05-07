<?
$result2=mysql_query($sql) or die("error:".$sql);
$row=mysql_fetch_array($result2);
fputs($fic,"Resumen de sanciones\nFecha: ".date("d/m/Y")." - Curso: ".utf8_decode($row["Unidad"])); 
?>