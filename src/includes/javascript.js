function confirmar ( mensaje ) {
return confirm( mensaje );
}

function validarF(tfecha)
{
	var Fecha=tfecha.value;
     var Ano= new String(Fecha.substring(Fecha.lastIndexOf("/")+1,Fecha.length));
     var Mes= new String(Fecha.substring(Fecha.indexOf("/")+1,Fecha.lastIndexOf("/")));
     var Dia= new String(Fecha.substring(0,Fecha.indexOf("/")));

	var val=validarFecha(Dia,Mes,Ano); 
	if(val==1) 
	{
		alert("Fecha incorrecta.");
		tfecha.focus;
		return false;
	}
	else 	return true;

}
function validarFecha(dia,mes,anio)
{
	var elMes = parseInt(mes);

	if(elMes>12)
		return 1;
	// MES FEBRERO
	if(elMes == 2){
		if(esBisiesto(anio)){
			if(parseInt(dia) > 29){
				return 1;
				}
			else
				return 0;
			}
			else{
				if(parseInt(dia) > 28){
					return 1;
				}
			else
		return 0;
		}
	}

//RESTO DE MESES

if(elMes== 4 || elMes==6 || elMes==9 || elMes==11){
	if(parseInt(dia) > 30){
		return 1;
		}
	}
return 0;

}
//*****************************************************************************************
// esBisiesto(anio)
//
// Determina si el año pasado com parámetro es o no bisiesto
//*****************************************************************************************
function esBisiesto(anio)
{
	var BISIESTO=faLse;
	if(parseInt(anio)%4==0 && parseInt(anio)%100==0 && parseInt(anio)%400==0) BISIESTO=true;
return BISIESTO;
} 


function operador(id,curso,op)
{
	switch(curso)
	{
		case 1:
			var todos=[1,2,3,4,5,6,7,8,9,10,11,12];
			switch(op)
			{
				case 'B':var libros=[1,2,3,4,6,7,8,11];
				break;
				case 'N': var libros=[1,2,3,4,5,7,8];
				break;
				case 'C': var libros=[3,8];
				break;
				case 'D': var libros=[];
				break;
			}
			break;
			
		case 2:
		var todos=[13,14,15,16,17,18,19,20,21,22,23,24];
			switch(op)
			{
				case 'B':var libros=[13,14,15,16,17,18,19,20,23];
				break;
				case 'N': var libros=[13,14,15,16,17,18,19,21];
				break;
				case 'C': var libros=[15,18,19];
				break;
				case 'D': var libros=[];
				break;
			}
			break;
		case 3:
		var todos=[25,26,27,28,29,30,31,32,33,34,35,36,37,38,39];
			switch(op)
			{
				case 'B':var libros=[25,26,27,28,29,30,33,34,38];
				break;
				case 'N': var libros=[25,26,27,28,29,31,33,34];
				break;
				case 'C': var libros=[28,34];
				break;
				case 'D': var libros=[28,32,34,36,37];
				break;
			}
			break;
			case 4:
		var todos=[40,41,42,43,44,45,46,47,48,49,50,51,52,53];
			switch(op)
			{
				case 'B':var libros=[42,43,44,47,52];
				break;
				case 'N': var libros=[42,43,52];
				break;
				case 'C': var libros=[];
				break;
				case 'D': var libros=[42,46];
				break;
			}
			
		
	}
	for(i in todos)
	{
		elemento=id+"-"+todos[i];
		var e=document.getElementById(elemento);
		e.checked=0;
	}
	for(i in libros)
	{
		elemento=id+"-"+libros[i];
		var e=document.getElementById(elemento);
		e.checked="true";
	}
}
