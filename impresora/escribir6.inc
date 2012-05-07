<?
$cont=1;
while($row=mysql_fetch_array($result))
{
	if($cont==3)
	{
		fputs($fic,utf8_decode($row["Apellidos"])." ".utf8_decode($row["Nombre"])."\n"); 
		$cont=1;
	}
	else
	{
		fputs($fic,utf8_decode($row["Apellidos"])." ".utf8_decode($row["Nombre"]).";"); 
		$cont++;
	}
}
