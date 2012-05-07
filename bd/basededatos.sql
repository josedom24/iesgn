-- MySQL dump 10.11
--
-- Host: localhost    Database: iesgn
-- ------------------------------------------------------
-- Server version	5.0.45-Debian_1ubuntu3-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Alumnos`
--

DROP TABLE IF EXISTS `Alumnos`;
CREATE TABLE `Alumnos` (
  `Id` int(11) NOT NULL auto_increment,
  `Nombre` varchar(50) collate utf8_spanish2_ci default NULL,
  `DNI` varchar(9) collate utf8_spanish2_ci default NULL,
  `Direccion` varchar(60) collate utf8_spanish2_ci default NULL,
  `CodPostal` varchar(5) collate utf8_spanish2_ci default NULL,
  `Localidad` varchar(30) collate utf8_spanish2_ci default NULL,
  `Fecha_nacimiento` date default NULL,
  `Provincia` varchar(30) collate utf8_spanish2_ci default NULL,
  `Unidad` varchar(15) collate utf8_spanish2_ci default NULL,
  `Ap1tutor` varchar(20) collate utf8_spanish2_ci default NULL,
  `Ap2tutor` varchar(20) collate utf8_spanish2_ci default NULL,
  `Nomtutor` varchar(20) collate utf8_spanish2_ci default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=508 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;


--
-- Table structure for table `Cartas`
--

DROP TABLE IF EXISTS `Cartas`;
CREATE TABLE `Cartas` (
  `Id` int(11) NOT NULL auto_increment,
  `Titulo` varchar(30) NOT NULL,
  `Contenido` text NOT NULL,
  `Pag` int(11) NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Cartas`
--

LOCK TABLES `Cartas` WRITE;
/*!40000 ALTER TABLE `Cartas` DISABLE KEYS */;
INSERT INTO `Cartas` VALUES (1,'Prieba','<img width=\"154\" height=\"71\" alt=\"\" src=\"http://localhost/iesgn/img/contraportada.jpg\" /><br />\r\n<br />\r\n<font size=\"4\"><strong>IES Gonzalo Nazareno<br />\r\nC/Las Botijas, 10<br />\r\n</strong><font size=\"3\">Tfno: 955839911</font></font><br />\r\n<div align=\"right\">##Nomtutor## ##Ap1tutor##&nbsp; ##Ap2tutor## &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;  &nbsp;&nbsp;  <br />\r\n##Direccion## &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;  &nbsp;&nbsp;  <br />\r\n##CodPostal## ##Localidad## &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;  &nbsp;&nbsp;  <br />\r\n&nbsp; (##Provincia##)&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp;  <br />\r\n</div>\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\nEstimado Sr. / Sra.:<br />\r\n<br />\r\n<div align=\"justify\">&nbsp;&nbsp; &nbsp;La actitud y el comportamiento que su hijo/a <strong>##Nombre##</strong>, matriculado/a en este centro en el curso ##Unidad##, est&aacute; teniendo, incumpliendo las normas de convivencia, con una acumulaci&oacute;n total de ##T## partes de disciplina, as&iacute; como la falta de respeto y colaboraci&oacute;n que demuestra en su trato con alumnos y profesores, dificultan la labor de formaci&oacute;n y educaci&oacute;n que deseamos impartir a todos los alumnos, incluido su hijo/a, para que puedan incorporarse en las mejores condiciones a la vida adulta.<br />\r\n&nbsp;&nbsp;&nbsp; <br />\r\n&nbsp;&nbsp;&nbsp; Comportamientos y actitudes como los que est&aacute; manifestando su hijo/a est&aacute;n recogidos en el Reglamento de Organizaci&oacute;n y Funcionamiento de este Instituto como &ldquo;perjudiciales para el normal desarrollo de la actividad docente y de aprendizaje&rdquo; y los sanciona, la primera vez, con un apercibimiento por escrito. &nbsp;<br />\r\n&nbsp;&nbsp;&nbsp; <br />\r\n&nbsp;&nbsp;&nbsp; De no modificar su conducta, se le podr&aacute; sancionar con la expulsi&oacute;n del Centro de 1 a 3 d&iacute;as. Por reiteraci&oacute;n en este comportamiento se le podr&aacute; sancionar con una expulsi&oacute;n&nbsp; de 5 d&iacute;as a un mes.<br />\r\n&nbsp;&nbsp;&nbsp; <br />\r\n&nbsp;&nbsp;&nbsp; Por otra parte ,para cualquier aclaraci&oacute;n o duda,puede solicitar una entrevista&nbsp; con el Jefe de Estudios los MARTES de 9:15 a 10:15 o en su defecto los MI&Eacute;RCOLES de 9:15 a 10:15 <br />\r\n</div>\r\n<br />\r\n&nbsp;&nbsp; &nbsp;Atentamente,<br />\r\n<br />\r\n<br />\r\n<br />\r\nJEFATURA DE ESTUDIOS<br />\r\n<hr width=\"100%\" size=\"2\" />',1),(3,'SAnsiones','<img width=\"154\" height=\"71\" src=\"http://localhost/iesgn/img/contraportada.jpg\" alt=\"\" /><br />\r\n<strong><font size=\"2\">IES Gonzalo Nazareno</font><br />\r\n</strong> <br />\r\n<br />\r\n<div align=\"center\"><strong><font size=\"4\"> COMUNICADO DE LA CORRECCI&Oacute;N</font><br />\r\n<br />\r\n<br />\r\n<br />\r\n</strong></div>\r\nD&ntilde;a. Pilar Cazenave Bernal, en su calidad de Directora del IES Gonzalo Nazareno, le comunica que, tras el preceptivo tr&aacute;mite de audiencia y una vez comprobada la autoria de los hechos:<br />\r\n<br />\r\n<strong>&nbsp; ##Comentario##</strong><br />\r\n<div align=\"justify\"><br />\r\ndado que los mismo se consideran constitutivos de conductas gravemente perjudiciales contra la convivencia, al alumno <strong>##Nombre## </strong>le ha sido impuesta la correcci&oacute;n de <strong>##Sancion##</strong>, durante el cual deber&aacute; realizar las tareas de formaci&oacute;n que el tutor le asigne, y que tendr&aacute; que recoger durante esta semana en el instituto.<br />\r\n<br />\r\n<br />\r\nContra la presente resoluci&oacute;n podr&aacute; presentar reclamaci&oacute;n ante la Directora. En caso de ser estimada la reclamaci&oacute;n, la correcci&oacute;n no figurar&aacute; en el expediente acad&eacute;mico del alumno.<br />\r\n</div>\r\n<br />\r\n<br />\r\n<br />\r\n<div align=\"right\">En Dos Hermanas, a ##Dia## de ##Mes## de ##Ano##<br />\r\n</div>\r\n<br />\r\n<br />\r\n<table width=\"653\" height=\"83\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" align=\"\" summary=\"\">\r\n    <tbody>\r\n        <tr>\r\n            <td><br />\r\n            </td>\r\n            <td> La Directora<br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            Fdo: Pilar Cazenave Bernal<br />\r\n            <br />\r\n            <br />\r\n            El padre/madre o tutor leagal<br />\r\n            <br />\r\n            <br />\r\n            Fdo:</td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<div align=\"center\"><font size=\"4\"><strong>VISTA Y AUDIENCIA</strong></font><br />\r\n</div>\r\n<br />\r\n<br />\r\nEn Dos Hermanas, siendo las ##Hora## del d&iacute;a ##Dia## de ##Mes## de ##Ano##, comparecen ante la Directora del IES Gonzalo Nazareno, el/la alumno/a: ##Nombre##, matriculado en el curso ##Unidad##, su representante legal D/D&ntilde;a:&nbsp;##Nomtutor##&nbsp;##Ap1tutor##&nbsp;##Ap2tutor## y el Jefe de Estudios D.Jos&eacute; Garrido para llevar a efecto el tr&aacute;mite de Audiencia.<br />\r\n<br />\r\n<br />\r\nA tal fin les informa que en el procedimiento de correcci&oacute;n abierto se de le imputan los siguientes hechos:<br />\r\n<br />\r\n<strong>&nbsp; ##Comentario##</strong><br />\r\n<br />\r\n<br />\r\nAsimismo se le comunica que en relaci&oacute;n con los hechos imputados puede(n) efectuar las alegaciones que en su defensa interesen.<br />\r\n<br />\r\nEn prueba de conformidad con la celebraci&oacute;n del acto, firma la presente:<br />\r\n<br />\r\n<br />\r\n<table width=\"653\" height=\"83\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" align=\"\" summary=\"\">\r\n    <tbody>\r\n        <tr>\r\n            <td> Padre, madre o representante legal del alumno&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; <br />\r\n            Recibido:<br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            Fdo:__________________________________</td>\r\n            <td> El Tutor/a<br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            <br />\r\n            Fdo: __________________________<br />\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n<br />\r\n<br />\r\nLa Directora<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\nFdo: Pilar Cazenave Bernal',1),(7,'Revisión Libros Texto','<div align=\"center\"><font size=\"4\"><strong>REVISI&Oacute;N DE LIBROS DE TEXTO</strong></font><br />\r\nIES Gonzalo Nazareno<br />\r\n</div>\r\n<br />\r\n<font size=\"3\">Curso: ##Unidad##<br />\r\nTutor/a: ##Tutor##</font><br />\r\n<br />\r\n<br />\r\nAsignatura: <strong>##Nombre##</strong> (##Abr##)<br />\r\n<br />\r\n##RevAlumnos##<br />\r\n<br />\r\n<br />\r\n<font size=\"2\"><br />\r\n</font>',1),(2,'Carnets','<table height=\"23\" cellspacing=\"1\" cellpadding=\"1\" border=\"1\" summary=\"\">\r\n    <tbody>\r\n        <tr>\r\n            <td width=\"20%\">&nbsp;</td>\r\n            <td width=\"80%\">\r\n            <div align=\"center\">\r\n            <div align=\"left\"><img width=\"99\" height=\"46\" src=\"http://localhost/iesgn/img/contraportada.jpg\" alt=\"\" />             </div>\r\n            <div align=\"left\"><font size=\"1\">IES Gonzalo Nazareno</font></div>\r\n            <font size=\"1\">            <strong>             </strong></font>\r\n            <div align=\"left\"> <font size=\"1\"><strong>            <br />\r\n            </strong></font>\r\n            <p align=\"center\">CARNET MAYOR DE EDAD<br />\r\n            <br />\r\n            </p>\r\n            <div align=\"left\">\r\n            <div align=\"justify\">El alumno ##Nombre## (DNI: ##DNI##) del curso ##Unidad## es mayor de edad, y por tanto puede salir y entrar en el centro, mientras no moleste en clase.<br />\r\n            <br />\r\n            </div>\r\n            <br />\r\n            LA DIRECCI&Oacute;N<br />\r\n            </div>\r\n            </div>\r\n            </div>\r\n            </td>\r\n        </tr>\r\n    </tbody>\r\n</table>',3),(4,'Revision libros','<div align=\"center\"><font size=\"4\"><strong>REVISI&Oacute;N DE LIBROS DE TEXTO</strong></font><br />\r\nIES Gonzalo Nazareno<br />\r\n</div>\r\n<br />\r\n<font size=\"3\">Curso: ##Unidad##<br />\r\nTutor/a: ##Tutor##</font><br />\r\n<br />\r\n<br />\r\nAlumno: <strong>##Nombre##</strong> (##Unidad##)<br />\r\n<br />\r\n##Libros##<br />\r\n<br />\r\n<br />\r\n<font size=\"2\">Instrucciones: En la columna &quot;Revisi&oacute;n&quot; se valorar&aacute; el estado del&nbsp; libro con una puntuaci&oacute;n de 0-5 (0=Muy mal, 5= Muy bien).<br />\r\nEn la columna Sept., se indicara si el libro se presta hasta septiembre (S) o en caso contrio se indica con una N.<br />\r\nEn la columna Entr. se indica si el libro se ha entregado (S) o no se ha entregado (N), esta columna se rellenar&aacute; en septiembre para los libros prestados para la evaluaci&oacute;n extraordinaria.<br />\r\n<br />\r\nTodos los libros deben estar forrados. En la etiqueta del libro debe aparecer el nombre, el curso y el a&ntilde;o acad&eacute;mico. Cada alumno ser&aacute; responsable del buen uso de cada libro.</font>',1),(5,'Partes de Falta','<div align=\"left\"><font size=\"4\"><strong>CONTROL DE ASISTENCIA A CLASE - </strong>IES Gonzalo Nazareno</font><br />\r\n</div>\r\n<font size=\"3\">Curso: ##Unidad##<br />\r\n<table width=\"649\" height=\"27\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" align=\"\" summary=\"\">\r\n    <tbody>\r\n        <tr>\r\n            <td><font size=\"2\"> Tutor/a: ##Tutor##</font></td>\r\n            <td align=\"right\">Semana del ____/_________ al ____/_________</td>\r\n        </tr>\r\n    </tbody>\r\n</table>\r\n</font><font size=\"3\"></font>\r\n<div align=\"right\">&nbsp;</div>\r\n<font size=\"1\">##Alumnos##</font><br />\r\n<br />\r\n<br />\r\n<font size=\"2\"><br />\r\n</font>',1),(6,'Sitación','<img height=\"71\" width=\"154\" src=\"http://localhost/iesgn/img/contraportada.jpg\" alt=\"\" /><br />\r\n<br />\r\n<font size=\"4\"><strong>IES Gonzalo Nazareno<br />\r\nC/Las Botijas, 10<br />\r\n</strong><font size=\"3\">Tfno: 955839911</font></font><br />\r\n<div align=\"right\">##Nomtutor## ##Ap1tutor##&nbsp; ##Ap2tutor##&nbsp;&nbsp;  &nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp;  <br />\r\n##Direccion## &nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;  <br />\r\n##CodPostal## ##Localidad## &nbsp;&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;  <br />\r\n&nbsp; (##Provincia##)&nbsp; &nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp;  <br />\r\n</div>\r\n<br />\r\n<br />\r\n<br />\r\n<br />\r\n<div align=\"justify\"><font size=\"3\">Padre, madre o tutor legal del alumno:</font><br />\r\n<br />\r\n<font size=\"3\">&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;  Por la presente le comunico que el alumno/a: <strong>##Nombre## (##Unidad##)</strong>, tiene acumulados partes de amonestaci&oacute;n en cantidad suficiente (o de la gravedad necesaria) para ser sancionado seg&uacute;n recoge nuestro Reglamento de Organizaci&oacute;n y Funcionamiento; en consecuencia, le ruego que se ponga en contacto con la Jefatura de Estudios a la mayor brevedad posible con el fin de concertar una Vista y Audiencia preceptiva.</font><br />\r\n<br />\r\n&nbsp;&nbsp;&nbsp; As&iacute; mismo,le comunico que en caso de no llevarse a cabo esta Vista y Audiencia se proceder&aacute; a derivar a los organismos pertinentes para que se tomen las medidas legales que sean oportunas<font size=\"3\"><br />\r\n<br />\r\n&nbsp;&nbsp;&nbsp; Atentamente,</font><br />\r\n<br />\r\n<br />\r\n<br />\r\n<font size=\"3\"> JEFATURA DE ESTUDIOS</font><br />\r\n</div>\r\n<hr size=\"2\" width=\"100%\" align=\"justify\" />',1);
/*!40000 ALTER TABLE `Cartas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CentroGastos`
--

DROP TABLE IF EXISTS `CentroGastos`;
CREATE TABLE `CentroGastos` (
  `Id` int(11) NOT NULL auto_increment,
  `Abr` varchar(4) NOT NULL,
  `Departamento` varchar(30) NOT NULL,
  `Fotocopias` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `CentroGastos`
--

LOCK TABLES `CentroGastos` WRITE;
/*!40000 ALTER TABLE `CentroGastos` DISABLE KEYS */;
INSERT INTO `CentroGastos` VALUES (1,'BG','BiologÃ­a y GeologÃ­a',5093),(2,'DIB','Dibujo',1557),(3,'EF','EducaciÃ³n FÃ­sica',366),(4,'FIL','FilosofÃ­a',5056),(5,'FR','FrancÃ©s',1387),(6,'FQ','FÃ­sica y QuÃ­mica',2068),(7,'GH','GeografÃ­a e HistorÃ­a',6717),(8,'GR','Griego',1077),(9,'IN','InglÃ©s',14513),(10,'LAT','LatÃ­n',1195),(11,'LE','Lengua',15184),(12,'MA','MatemÃ¡ticas',7850),(13,'MU','MÃºsica',4189),(14,'REL','ReligiÃ³n',108),(15,'ORI','OrientaciÃ³n',6378),(16,'TEC','TecnologÃ­a',1954),(17,'INF','InformÃ¡tica',3590),(18,'ECO','EconomÃ­a',1404),(19,'COM','Compensatoria',4377),(20,'BIL','Biblioteca',2),(21,'TIC','TIC',16),(22,'BIL','BilingÃ¼e',6801);
/*!40000 ALTER TABLE `CentroGastos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ClaseDocumento`
--

DROP TABLE IF EXISTS `ClaseDocumento`;
CREATE TABLE `ClaseDocumento` (
  `Idc` int(11) NOT NULL auto_increment,
  `ClaseDocumento` varchar(20) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`Idc`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `ClaseDocumento`
--

LOCK TABLES `ClaseDocumento` WRITE;
/*!40000 ALTER TABLE `ClaseDocumento` DISABLE KEYS */;
INSERT INTO `ClaseDocumento` VALUES (1,'Adjunto'),(2,'Oficio'),(3,'Fax'),(4,'Informe'),(5,'ReclamaciÃ³n'),(6,'Carta'),(7,'ConvalidaciÃ³n'),(8,'Circular'),(9,'Solicitud'),(10,'CitaciÃ³n'),(11,'Comunicado'),(12,'Acta'),(13,'Justificante'),(14,'ResoluciÃ³n'),(15,'Certificado'),(16,'Memoria'),(17,'Convocatoria'),(18,'AutorizaciÃ³n'),(19,'Instancia'),(20,'Correo Corporativo');
/*!40000 ALTER TABLE `ClaseDocumento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `CompDiv`
--

DROP TABLE IF EXISTS `CompDiv`;
CREATE TABLE `CompDiv` (
  `Idc` int(11) NOT NULL,
  `Ida` int(11) NOT NULL,
  PRIMARY KEY  (`Idc`,`Ida`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



--
-- Table structure for table `Contabilidad`
--

DROP TABLE IF EXISTS `Contabilidad`;
CREATE TABLE `Contabilidad` (
  `Id` int(11) NOT NULL auto_increment,
  `Idcg` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `Concepto` varchar(200) NOT NULL,
  `Cantidad` float NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=1105 DEFAULT CHARSET=latin1;


--
-- Table structure for table `Cursos`
--

DROP TABLE IF EXISTS `Cursos`;
CREATE TABLE `Cursos` (
  `Id` int(11) NOT NULL auto_increment,
  `Abr` varchar(10) NOT NULL,
  `Curso` varchar(30) NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Cursos`
--

LOCK TABLES `Cursos` WRITE;
/*!40000 ALTER TABLE `Cursos` DISABLE KEYS */;
INSERT INTO `Cursos` VALUES (1,'Comp 1','Compensatoria 1 ESO'),(2,'Comp 2-3','Compensatoria 2-3 ESO'),(3,'Div 3','Diversificacion 3 ESO'),(4,'Div 4','Diversificacion 4 ESO');
/*!40000 ALTER TABLE `Cursos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Departamentos`
--

DROP TABLE IF EXISTS `Departamentos`;
CREATE TABLE `Departamentos` (
  `Id` int(11) NOT NULL auto_increment,
  `Abr` varchar(4) NOT NULL,
  `Departamento` varchar(30) NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Departamentos`
--

LOCK TABLES `Departamentos` WRITE;
/*!40000 ALTER TABLE `Departamentos` DISABLE KEYS */;
INSERT INTO `Departamentos` VALUES (1,'BG','BiologÃ­a y GeologÃ­a'),(2,'DIB','Dibujo'),(3,'EF','EducaciÃ³n FÃ­sica'),(4,'FIL','FilosofÃ­a'),(5,'FR','FrancÃ©s'),(6,'FQ','FÃ­sica y QuÃ­mica'),(7,'GH','GeografÃ­a e HistorÃ­a'),(8,'GR','Griego'),(9,'IN','InglÃ©s'),(10,'LAT','LatÃ­n'),(11,'LE','Lengua'),(12,'MA','MatemÃ¡ticas'),(13,'MU','MÃºsica'),(14,'REL','ReligiÃ³n'),(15,'ORI','OrientaciÃ³n'),(16,'TEC','TecnologÃ­a'),(17,'INF','InformÃ¡tica'),(18,'ECO','EconomÃ­a'),(19,'COM','Compensatoria');
/*!40000 ALTER TABLE `Departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Libros`
--

DROP TABLE IF EXISTS `Libros`;
CREATE TABLE `Libros` (
  `Id` int(11) NOT NULL auto_increment,
  `Curso` tinyint(4) NOT NULL,
  `Nombre` varchar(30) collate utf8_spanish_ci NOT NULL,
  `Abr` varchar(5) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=54 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `Libros`
--

LOCK TABLES `Libros` WRITE;
/*!40000 ALTER TABLE `Libros` DISABLE KEYS */;
INSERT INTO `Libros` VALUES (1,1,'Ciencias de la Naturaleza','CCNN'),(2,1,'Ciencias Sociales','CSGH'),(3,1,'Ed. PlÃ¡stica','EPV'),(4,1,'Lengua','LCL'),(5,1,'InglÃ©s no BilingÃ¼e','ING'),(6,1,'InglÃ©s BilingÃ¼e','INGB'),(7,1,'MatemÃ¡ticas','MAT'),(8,1,'Musica','MUS'),(9,1,'ReligiÃ³n','REL'),(10,1,'FrancÃ©s no BilingÃ¼e','FR2'),(11,1,'FrancÃ©s BilingÃ¼e','FR2B'),(12,1,'TecnologÃ­a','TAP'),(13,2,'Ciencias de la Naturaleza','CNA'),(14,2,'Ciencias Sociales','CSGH'),(15,2,'Ed. PlÃ¡stica','EPV'),(16,2,'Lengua','LCL'),(17,2,'MatemÃ¡ticas','MAT'),(18,2,'Musica','MUS'),(19,2,'TecnologÃ­a','TEC'),(20,2,'InglÃ©s BilingÃ¼e','INGB'),(21,2,'InglÃ©s no BilingÃ¼e','ING'),(22,2,'ReligiÃ³n','REL'),(23,2,'FrancÃ©s BilingÃ¼e','FRB'),(24,2,'FrancÃ©s no BilingÃ¼e','FR'),(25,3,'BiologÃ­a','BYG'),(26,3,'FÃ­sica y QuÃ­mica','FQU'),(27,3,'Ciencias Sociales','CSGH'),(28,3,'Ed. ciudadanÃ­a','ECDH'),(29,3,'Lengua','LCL'),(30,3,'InglÃ©s BilingÃ¼e','INGB'),(31,3,'InglÃ©s no BilingÃ¼e','ING'),(32,3,'InglÃ©s Div','INGD'),(33,3,'MatemÃ¡ticas','MAT'),(34,3,'TecnologÃ­a','TEC'),(35,3,'ReligiÃ³n','REL'),(36,3,'Amb. SociolingÃ¼istico','ASL'),(37,3,'Amb. Ciencias','ACT'),(38,3,'FrancÃ©s BilingÃ¼e','FRB'),(39,3,'FrancÃ©s no bilingÃ¼e','FR'),(40,4,'BiologÃ­a','BYG'),(41,4,'FÃ­sica y QuÃ­mica','FQU'),(42,4,'Ciencias Sociales','CSGH'),(43,4,'Lengua','LCL'),(44,4,'InglÃ©s BilingÃ¼e','INGB'),(45,4,'Ingles no BilingÃ¼e','ING'),(46,4,'InglÃ©s Div','INGD'),(47,4,'FrancÃ©s BilingÃ¼e','FRB'),(48,4,'FrancÃ©s no BilingÃ¼e','FR'),(49,4,'MatemÃ¡ticas A','MATA'),(50,4,'MatemÃ¡ticas B','MATB'),(51,4,'ReligÃ­on','REL'),(52,4,'Ed. Etico-cÃ­vica','EEC'),(53,4,'LatÃ­n','LAT');
/*!40000 ALTER TABLE `Libros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `LibrosAlumnos`
--

DROP TABLE IF EXISTS `LibrosAlumnos`;
CREATE TABLE `LibrosAlumnos` (
  `Id` int(11) NOT NULL,
  `Idl` int(11) NOT NULL,
  PRIMARY KEY  (`Id`,`Idl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


--
-- Table structure for table `Partes`
--

DROP TABLE IF EXISTS `Partes`;
CREATE TABLE `Partes` (
  `Id` int(11) NOT NULL auto_increment,
  `Ida` int(11) NOT NULL,
  `Tipo` varchar(1) collate utf8_spanish2_ci NOT NULL,
  `Fecha` date NOT NULL,
  `Fecha_fin` date NOT NULL,
  `Sancion` varchar(100) collate utf8_spanish2_ci NOT NULL,
  `Comentario` varchar(400) collate utf8_spanish2_ci default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=1572 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;


--
-- Table structure for table `Perfil`
--

DROP TABLE IF EXISTS `Perfil`;
CREATE TABLE `Perfil` (
  `Id` int(11) NOT NULL auto_increment,
  `Abr` varchar(1) NOT NULL,
  `Perfil` varchar(20) NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Perfil`
--

LOCK TABLES `Perfil` WRITE;
/*!40000 ALTER TABLE `Perfil` DISABLE KEYS */;
INSERT INTO `Perfil` VALUES (1,'a','Administrador'),(2,'u','Usuario'),(3,'s','Secretaria'),(4,'j','Jefe Estudios');
/*!40000 ALTER TABLE `Perfil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Permisos`
--

DROP TABLE IF EXISTS `Permisos`;
CREATE TABLE `Permisos` (
  `Perfil` varchar(1) NOT NULL,
  `Modulo` varchar(10) NOT NULL,
  `Permisos` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Permisos`
--

LOCK TABLES `Permisos` WRITE;
/*!40000 ALTER TABLE `Permisos` DISABLE KEYS */;
INSERT INTO `Permisos` VALUES ('a','General','V,A,E,B'),('j','General','V,A,,'),('s','General',',,,'),('j','alumnos','V,,E'),('s','profes','V,,,'),('s','menuProfes','V,,,'),('s','secretaria','V,A,E,B'),('s','menuSecre','V,,,'),('j','admin',',,,'),('j','menuAdmin',',,,'),('u','General','V,,,'),('s','menuAdmin',',,,'),('j','profesores','V,,,'),('s','profesores','V,,,'),('j','Secretaria','V,,,'),('s','menuCorreo',',,,'),('u','admin',',,,'),('u','menuAdmin',',,,'),('j','partes','V,A,E,B'),('s','menuCont',',,,'),('j','menuCont',',,,'),('u','menuCont',',,,');
/*!40000 ALTER TABLE `Permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Procedencia`
--

DROP TABLE IF EXISTS `Procedencia`;
CREATE TABLE `Procedencia` (
  `Idp` int(11) NOT NULL auto_increment,
  `Procedencia` varchar(30) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`Idp`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `Procedencia`
--

LOCK TABLES `Procedencia` WRITE;
/*!40000 ALTER TABLE `Procedencia` DISABLE KEYS */;
INSERT INTO `Procedencia` VALUES (1,'Sevilla'),(2,'Dos Hermanas'),(4,'Utrera'),(5,'Madrid'),(6,'Los Palacios y Villafranca'),(7,'Constantina'),(8,'Bellavista'),(9,'CÃ³rdoba'),(10,'AlcalÃ¡ de Guadaira'),(11,'Profesor/a del Centro'),(12,'IES GONZALO NAZARENO'),(13,'Montequinto'),(14,'Roquetas de Mar'),(15,'Barcelona'),(16,'Marbella - MÃ¡laga'),(17,'Ecija'),(18,'EL EJIDO. ALMERIA'),(19,'VIGO'),(20,'SANTA CRUZ DE TENERIFE'),(21,'Mairena '),(22,'Reino Unido'),(23,'Algeciras'),(24,'Los Barrios'),(25,'S. Juan de Aznalfarache'),(26,'Marchena'),(27,'VALENCIA'),(28,'Murcia'),(29,'Alicante'),(30,'Camas'),(31,'MÃ¡laga'),(32,'SAN SEBASTIAN'),(33,'Victoria (Ãlava)'),(34,'Bormujo'),(35,'PALENCIA'),(36,'Salamanca'),(37,'Huelva'),(38,'Victoria '),(39,'Bilbao');
/*!40000 ALTER TABLE `Procedencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Profesores`
--

DROP TABLE IF EXISTS `Profesores`;
CREATE TABLE `Profesores` (
  `Id` int(11) NOT NULL auto_increment,
  `Nombre` varchar(20) character set utf8 collate utf8_spanish_ci NOT NULL,
  `Apellidos` varchar(30) collate utf8_spanish2_ci NOT NULL,
  `Telefono` varchar(9) character set utf8 collate utf8_spanish_ci default NULL,
  `Movil` varchar(9) character set utf8 collate utf8_spanish_ci default NULL,
  `Email` varchar(35) character set utf8 collate utf8_spanish_ci default NULL,
  `Departamento` int(11) default NULL,
  `Baja` int(1) NOT NULL default '0',
  `Ce` int(1) NOT NULL default '0',
  `Etcp` int(1) NOT NULL default '0',
  `Tic` int(1) NOT NULL default '0',
  `Bil` int(1) NOT NULL default '0',
  `Tutor` varchar(10) collate utf8_spanish2_ci default NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;


--
-- Table structure for table `Registro`
--

DROP TABLE IF EXISTS `Registro`;
CREATE TABLE `Registro` (
  `Curso` varchar(9) collate utf8_spanish_ci NOT NULL,
  `Fecha` date NOT NULL,
  `Id` int(11) NOT NULL,
  `Tipo` varchar(1) collate utf8_spanish_ci NOT NULL,
  `Idp` int(11) NOT NULL,
  `Idr` int(11) NOT NULL,
  `Idc` int(11) NOT NULL,
  `Contenido` varchar(100) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`Curso`,`Fecha`,`Id`,`Tipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `Remitente`
--

DROP TABLE IF EXISTS `Remitente`;
CREATE TABLE `Remitente` (
  `Idr` int(11) NOT NULL auto_increment,
  `Remitente` varchar(40) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`Idr`)
) ENGINE=MyISAM AUTO_INCREMENT=146 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `Remitente`
--

LOCK TABLES `Remitente` WRITE;
/*!40000 ALTER TABLE `Remitente` DISABLE KEYS */;
INSERT INTO `Remitente` VALUES (1,'DelegaciÃ³n EducaciÃ³n'),(2,'Padres'),(3,'Universidad'),(4,'ConsejerÃ­a EducaciÃ³n'),(5,'Ayuntamiento'),(7,'Arzobispado'),(8,'CEIP LOS MONTECILLOS'),(9,'CEIP IBARBURU'),(10,'CEIP ORIPPO'),(11,'CEIP CARLOS I'),(12,'IES VISTAZUL'),(13,'IES VIRGEN DE VALME'),(14,'IES TORRE DE LOS HERBEROS'),(15,'IES ALVAREDA'),(16,'IES  JESÃšS DEL GRAN PODER'),(17,'IES CANTELY'),(18,'CEIP SAGRADA FAMILIA'),(19,'CC ANTONIO GALA'),(20,'CC ALMINAR'),(21,'CEIP LA MOTILLA'),(22,'IES SANTA EULALIA'),(23,'IES BELLAVISTA'),(24,'Alumnos'),(25,'Solicitud'),(26,'IES SAN FERNANDO'),(27,'Librerias'),(28,'Cabildo'),(29,'Reales AlcÃ¡zares'),(30,'OAPEE     SÃ³crates'),(31,'CEIP '),(32,'Alumno/a'),(33,'Profesor/a del Centro'),(34,'consejo escolar'),(35,'INTERIOR'),(36,'Inspector Jefe'),(37,'Inspector'),(38,'Sr. Delegado EducaciÃ³n'),(39,'DirecciÃ³n'),(40,'JosÃ© MÂª M. Cruz'),(41,'CEIP FERNÃN CABALLERO'),(42,'CEIP Ntra. Sra. del Amparo'),(43,'CEIP FEDERICO GARCIA LORCA'),(44,'C.E.I.P. JOSÃ‰ VARELA'),(45,'C. ANTONIO GALA'),(46,'CEIP LAS PORTADAS'),(47,'C.D.P. GINER DE LOS RIOS'),(48,'IES LUIS CERNUDA'),(49,'IES TORRE DOÃ‘A MARIA'),(50,'IES PABLO PICASSO'),(51,'C.D.P. LA LOMA'),(52,'IES ALMUDEYNE'),(53,'C.E.I.P. VICENTE ALEXANDRE'),(54,'C.D. Roquetas'),(55,'Central Trinity College'),(56,'Presidente Ecija BalompiÃ©'),(57,'Presidente U.D. Marbella'),(58,'EJIDO CP'),(59,'IES SANTA IRENE'),(60,'IES GONZALO NAZARENO'),(61,'EDITORIAL'),(62,'RENFE'),(63,'Tutor/a'),(64,'Encuentro Centros de ESO'),(65,'FISCALIA DE MENORES'),(66,'IES POLÃGONO SUR'),(67,'AMPA'),(68,'ComisiÃ³n de EscolarizaciÃ³n'),(69,'AXA Seguros Generales'),(70,'IES INMACULADA VIEIRA'),(71,'IES GUAZA'),(72,'Servicios Sociales Municipales'),(73,'Limpiador/ra'),(74,'Educador/ra'),(75,'Centro Salud'),(76,'IES INMACULADA VIEIRA'),(77,'AsociaciÃ³n de Mujeres por la Justicia S'),(78,'CEP ALCALA'),(79,'TablÃ³n'),(80,'limpiadora'),(81,'TELEFÃ“NICA'),(82,'Amuradis'),(83,'ASOCIACIÃ“N AMURADIS'),(84,'POLICÃA'),(85,'AM Transnational'),(86,'Guardia Civil'),(87,'CEIP ENRIQUE DÃAZ FERRAS'),(88,'IES LUCA DE TENA'),(89,'CEIP EL PALMARILLO'),(90,'Paulo Rodrigues'),(91,'IES VENTURA MORÃ“N'),(92,'CEIP CERVANTES'),(93,'C.D.P. NTRA. SRA. DEL CARMEN'),(94,'CEIP MARÃA ZAMBRANO'),(95,'PILAR ESCUTIA'),(96,'IES HERMANOS MACHADO'),(97,'IES ALMUDEYNE'),(98,'IES ARENAL'),(99,'Colegio St. Patrick\'s Englesh School'),(100,'IES SIERRA LUNA'),(101,'IES MIRAFLORES DE LOS ANGELES'),(102,'CENTRO PREVENCION RIESGOS LABORALES'),(103,'IES MATEO ALEMÃN'),(104,'IES  LÃ“PEZ DE ARENAS'),(105,'(OAPEE)'),(106,'CONCEJALÃA BIENESTAR SOCIAL'),(107,'ConsejerÃ­a EconomÃ­a y Hacienda'),(108,'IES LA CREUETA'),(109,'SUBDIRECCIÃ“N GENERAL DE BECAS'),(110,'IES RUIZ GIJÃ“N'),(111,'CAJASOL'),(112,'I.E.S. DE BULLAS'),(113,'OAPEE'),(114,'Colegio Ramon Carande'),(115,'CENTRO DE ESTUDIOS PROFESIONALES'),(116,'Agencia Andaluza EvaluaciÃ³n Educativa'),(117,'M.A.E.S. (UNIVERSIDAD)'),(118,'JUZGADO DE INSTANCIA'),(119,'Seguro Helvetia'),(120,'Delegado Urbanismo'),(121,'Medio Ambiente Dos Hermanas'),(122,'C. LOS ÃNGELES'),(123,'Colegio San Prudencio'),(124,'IES Juan Ciudad Duarte'),(125,'IES \"VELAZQUEZ\"'),(126,'CENTRO  PREVENCIÃ“N RIESGOS LABORALES'),(127,'I.E.S. LAS VIÃ‘AS'),(128,'IES SALVADOR TÃVORA'),(129,'IES SEVERO OCHOA'),(130,'CEIP MAESTRO JOSÃ‰ VARELA'),(131,'IES FERNANDO DE HERRERA'),(132,'Paco Alcocer PeÃ±a'),(133,'IES RAMÃ“N CARANDE'),(134,'IES JORGE MANRIQUE'),(135,'\"La Inmaculada\" Colegio Internado'),(136,'Personal Laboral'),(137,'Leandro CalderÃ³n Labrador'),(138,'MÂª Carmen Jurado Cano'),(139,'IES  DIEGO ANGULO'),(140,'Colegio San Prudencio'),(141,'Agencia  EvaluaciÃ³n Educ.'),(142,'MINISTERIO DE EDUCACIÃ“N'),(143,'COMISIÃ“N ESCOLARIZACIÃ“N'),(144,'SUBDIRECCIÃ“ GENERAL ORIENTACIÃ“N F.  PR'),(145,'INS. F.P. ZORNOTZA');
/*!40000 ALTER TABLE `Remitente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `Nombre` varchar(50) collate utf8_spanish2_ci NOT NULL,
  `Usuario` varchar(20) collate utf8_spanish2_ci NOT NULL,
  `Pass` varchar(32) collate utf8_spanish2_ci NOT NULL,
  `Email` varchar(20) collate utf8_spanish2_ci NOT NULL,
  `Perfil` varchar(1) collate utf8_spanish2_ci NOT NULL default 'u',
  PRIMARY KEY  (`Usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-06-16 10:40:18
