<?session_start();
unset($_SESSION);
setcookie("iesgn[0]",$_POST["usuario"],time()-1);
setcookie("iesgn[1]",$_POST["pass"],time()-1);
session_destroy();
Header("Location:index.php");
?>