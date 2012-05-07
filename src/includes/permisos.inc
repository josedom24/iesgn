<?
$admin=array("alumnos"=>"v");
$per=array("a"=>$admin,"u"=>$usuario);


function permiso($a,$op="")
{
		foreach($per as $i)
		echo $i;
		$v=$per[$_SESSION["perfil"]][$a];
		echo $v[0];
		if($v[0]=="" and $op=="")
		{
			echo "No tienes permiso para estar aqui.<br>";
			echo "<a href=http://".$_SERVER['SERVER_NAME']."/iesgn/index.php>Volver</a>";
			die("");	
		}
		if(op!="") 
		{
			foreach($v as $i)
				if($i==$op) return true;
			return (false);
		}
}