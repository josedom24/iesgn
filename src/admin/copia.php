<?
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/header.inc");
permisos("admin");
system("rm *.gz");
$backupFile = "iesgn".date("Y-m-d-H-i-s").".gz";
echo $backupFile;
$command = "mysqldump --opt  -u root --password=iesgn iesgn|gzip>".$backupFile;
system($command);
system("chmod 777 ".$backupFile);
echo '<a href="'.$_SERVER["SERVER_ROOT"].'/iesgn/admin/'.$backupFile.'">Copia Seguridad</a>';
include ($_SERVER["DOCUMENT_ROOT"]."/iesgn/includes/footer.inc");
?>

