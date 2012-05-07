<?
$cont=1;
while($row=mysql_fetch_array($result))
	{
			fputs($fic,utf8_decode($cont).";"); 
			fputs($fic,utf8_decode($row["Nombre"]).";"); 
			$sql="select * from Libros where Curso=".substr($row["Unidad"],0,1)." order by Id";
			$uni=$row["Unidad"];
			
			$result2=mysql_query($sql);
			$c=1;
			while($row2=mysql_fetch_array($result2))
			{
				
				
				if(libro_select($row["Id"],$row2["Id"])) fputs($fic,utf8_decode("     ")); 
				else fputs($fic,utf8_decode(" "));
				if($c<mysql_num_rows($result2)) fputs($fic,";"); else fputs($fic,"\n");
				$c++;
			}
		
			
			$cont++;
		
		
	}
fputs($fic,"              Indica con una X el libro que se ha prestado hasta para septiembre.");	
	?>
