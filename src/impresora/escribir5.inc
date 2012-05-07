<?
while($row=mysql_fetch_array($result))

	{

	
	fputs($fic,utf8_decode($row["Apellidos"])." ".utf8_decode($row["Nombre"]).";"); 

	fputs($fic,utf8_decode($row["Telefono"]).";");  
	fputs($fic,utf8_decode($row["Movil"]).";");   
	fputs($fic,utf8_decode($row["Email"]).";");  
	fputs($fic,utf8_decode($row[1])."\n"); 

	

	

	}
?>