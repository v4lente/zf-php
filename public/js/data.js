// Classe respons�vel por trabalhar com data e hora
var Data = {
		
    // M�todo principal da classe
	init : function() {
		
	},
	
    // M�todo que valida a hora e o minuto
	verificaHora : function(hr){

		if (hr != ""){
			var horaFull = hr.split(":"); 
			var hora     = horaFull[0];
			var min      = horaFull[1];
			var seg      = horaFull[2];

            if ((hora > 23) || (min > 59) || (seg > 59)) {
            	return false;
	        }else{
	        	return true;
	        }
		}else{
			return true;
		}
	},

    // M�todo que valida a hora e o minuto
	verificaHoraMinuto : function(hr){

		if (hr != ""){
			var horamin = hr.split(":"); 
			var hora    = horamin[0];
			var min     = horamin[1];

            if ((hora > 23) || (min > 59)) {
            	return false;
	        }else{
	        	return true;
	        }
		}else{
			return true;
		}
	},
	
	// M�todo que verifica se a data � v�lida
	verificaData : function(dt){
		if (dt != ""){
			var dia = dt.substr(0,2);
            var mes = dt.substr(3,2);
            var ano = dt.substr(6,4);

            
			var bissexto = 0;
	        var tam = dt.length;

	        if (tam == 10) {
	            if ((ano > 1900)||(ano < 2100)) {
	                switch (mes) 
	                {
	                    case '01':
	                    case '03':
	                    case '05':
	                    case '07':
	                    case '08':
	                    case '10':
	                    case '12':
	                        if  (dia <= 31){
	                        	return true;
	                        }else{
	                        	return false;
	                        }
	                        break;

	                    case '04':              
	                    case '06':
	                    case '09':
	                    case '11':
	                        if  (dia <= 30){
	                        	return true;
	                        }else{
	                        	return false;
	                        }
	                        break;
	                    case '02':
	                        /* Validando ano Bissexto / fevereiro / dia */ 
	                        if ((ano % 4 == 0) || (ano % 100 == 0) || (ano % 400 == 0)){ 
	                            bissexto = 1; 
	                        }
	                        if (bissexto == 1){ 
	                        	if (dia <= 29){ 
	                        		return true;                             
	                        	}else{
	                        		return false;
	                        	}
	                        }else{
	                        	if (dia <= 28){ 
	                        		return true; 
	                        	}else{
	                        		return false;
	                        	}
	                		}                     
	                        break;                                    
	                }
	            }

	        }
	    	return false;
		}
	},
	
	// M�todo que verifica se a data e hora s�o v�lidas 
	verificaDataHora : function(dt){
		if (dt != undefined && dt != "") {

			var tam = dt.length;

	        if (tam == 16 || tam == 19) {

				var dthr = dt.split(" "); 
				var data = dthr[0];
				var hora = dthr[1];
					
				if(hora.length == 5) {
					if(Data.verificaHoraMinuto(hora) == false) {
						return false;
					}
					
				} else if(hora.length == 8) {
					if (Data.verificaHora(hora) == false){
						return false;
					}
					
				} else {
					return false;
				}
				
				if(Data.verificaData(data) == false) {
					return false;
				}
					
				// Se validar corretamente retorna true
	            return true;

	        } else {
	        	return false;
	        }
	    	
		} else {
			return false;
		}
	},
	
    // M�todo que valida mes e ano (MM/AAAA)
	verificaMesAno : function(mesano){

		if (mesano != ""){
			var arrMesAno = mesano.split(":"); 
			var mes       = parseInt(arrMesAno[0], 10);
			var ano       = parseInt(arrMesAno[1], 10);

            if ( (mes < 1) || (mes > 12) || (ano < 1900) || (ano > 2100) ) {
            	return false;
	        }    
            else{
	        	return true;
	        }
		}else{
			return true;
		}
	},
	
	// M�todo que valida o per�odo entre duas datas
	verificaIntervaloData : function(dtIni, dtFim){

		// Verifica se foi passado os parametros
		if ( dtIni == "" || dtFim == "" ) {
			return false;
		}

		// Verifica se as datas s�o v�lidas
		if(! Data.verificaData(dtIni) || ! Data.verificaData(dtFim)) {
			return false;
		}

		// Verifica se a data inicial � maior que a data final 
		if (Data.dataInvertidaSemBarra(dtIni) > Data.dataInvertidaSemBarra(dtFim)) {
			return false;
		}

		return true;
	},
	
	// M�todo que valida o per�odo entre duas datas com horas inclu�das
	verificaIntervaloDataHora : function(dtIni, dtFim){
		
		// Verifica se foi passado os parametros
		if ( dtIni == "" || dtFim == "" ) {
			return false;
		}
		
		// Verifica se as datas s�o v�lidas
		if(! Data.verificaDataHora(dtIni) || ! Data.verificaDataHora(dtFim)) {
			return false;
		}
	    
		// Quebra as datas
		diaDtIni = dtIni.substring(0,2);
		mesDtIni = dtIni.substring(3,5);
		anoDtIni = dtIni.substring(6,10);
		horDtIni = dtIni.substring(11,13);
		minDtIni = dtIni.substring(14,16);
		secDtIni = dtIni.substring(17,19);
		
		diaDtFim = dtFim.substring(0,2);
		mesDtFim = dtFim.substring(3,5);
		anoDtFim = dtFim.substring(6,10);
		horDtFim = dtFim.substring(11,13);
		minDtFim = dtFim.substring(14,16);
		secDtFim = dtFim.substring(17,19);
		
		// Instancia a data de in�cio
		dtInicio = new Date();
		dtInicio.setFullYear(anoDtIni != "" ? anoDtIni   : "1900");
		dtInicio.setMonth(mesDtIni    != "" ? mesDtIni-1 : "01");
		dtInicio.setDate(diaDtIni     != "" ? diaDtIni   : "01");
		dtInicio.setHours(horDtIni    != "" ? horDtIni   : "00");
		dtInicio.setMinutes(minDtIni  != "" ? minDtIni   : "00");
		dtInicio.setSeconds(secDtIni  != "" ? secDtIni   : "00");
		
		// Instancia a data de t�rmino
		dtTermino = new Date();
		dtTermino.setFullYear(anoDtFim != "" ? anoDtFim   : "1900");
		dtTermino.setMonth(mesDtFim    != "" ? mesDtFim-1 : "01");
		dtTermino.setDate(diaDtFim     != "" ? diaDtFim   : "01");
		dtTermino.setHours(horDtFim    != "" ? horDtFim   : "00");
		dtTermino.setMinutes(minDtFim  != "" ? minDtFim   : "00");
		dtTermino.setSeconds(secDtFim  != "" ? secDtFim   : "00");

		// Compara as datas
		if (dtInicio.getTime() <= dtTermino.getTime()){
			return true;
		}else{
			return false;
        }
		
	},
	
	// Retorna um inteiro
    dataInvertidaSemBarra : function (data) {
    	
    	var dia = data.substr(0,2);
        var mes = data.substr(3,2);
        var ano = data.substr(6,4);
        
        return parseInt(ano+""+mes+""+dia, 10);        
    },
    
    // Retorna um objeto tipo Date
    ajustaData : function (data) {
    	
    	var dia = data.substr(0,2);
        var mes = data.substr(3,2);
        var ano = data.substr(6,4);
        
    	return new Date(ano+"/"+mes+"/"+dia);
    },

    // Retorna a data atual
    retornaDataAtual : function () {
    	
    	var data = new Date();    	
    	var dia  = data.getDate();
    	var mes  = data.getMonth() + 1;
    	var ano  = data.getFullYear();
    	
    	
    	if (dia < 10) {
    		dia = "0" + dia;  
    	}
    	
    	if (mes < 10) {
    		mes = "0" + mes;  
    	}
    	
    	return dia+"/"+mes+"/"+ano;
    },
    
    
    // Retorna a data hora atual
    retornaDataHoraAtual : function () {
    	
    	var data = new Date();    	
    	var dia  = data.getDate();
    	var mes  = data.getMonth() + 1;
    	var ano  = data.getFullYear();
    	var hora    = data.getHours();
    	var minuto  = data.getMinutes();
    	
    	
    	if (dia < 10) {
    		dia = "0" + dia;  
    	}
    	
    	if (mes < 10) {
    		mes = "0" + mes;  
    	}
    	
    	if (hora < 10) {
    		hora = "0" + hora;
    	}
    	
    	if (minuto < 10) {
    		minuto = "0" + minuto;
    	}
    	
    	return dia+"/"+mes+"/"+ano+" "+hora+":"+minuto;
    },
    // Retorna a data atual sem barras
    retornaDataAtualInvertidaSemBarra : function () {
    	
    	var data = new Date();    	
    	var dia  = data.getDate();
    	var mes  = data.getMonth() + 1;
    	var ano  = data.getFullYear();
    	
    	
    	if (dia < 10) {
    		dia = "0" + dia;  
    	}
    	
    	if (mes < 10) {
    		mes = "0" + mes;  
    	}
    	
    	return parseInt(ano+""+mes+""+dia, 10);    	
    },
    
    // M�todo que retorna em horas a diferen�a entre duas datas com hora
	retornaIntervaloDataHora : function(dtIni, dtFim){
		
		// Verifica se foi passado os parametros
		if ( dtIni == "" || dtFim == "" ) {
			return false;
		}
		
		// Verifica se as datas s�o v�lidas
		if(! Data.verificaDataHora(dtIni) || ! Data.verificaDataHora(dtFim)) {
			return false;
		}
	    
		// Quebra as datas
		diaDtIni = dtIni.substring(0,2);
		mesDtIni = dtIni.substring(3,5);
		anoDtIni = dtIni.substring(6,10);
		horDtIni = dtIni.substring(11,13);
		minDtIni = dtIni.substring(14,16);
		secDtIni = dtIni.substring(17,19);
		
		diaDtFim = dtFim.substring(0,2);
		mesDtFim = dtFim.substring(3,5);
		anoDtFim = dtFim.substring(6,10);
		horDtFim = dtFim.substring(11,13);
		minDtFim = dtFim.substring(14,16);
		secDtFim = dtFim.substring(17,19);
		
		// Instancia a data de in�cio
		dtInicio = new Date();
		dtInicio.setFullYear(anoDtIni != "" ? anoDtIni   : "1900");
		dtInicio.setMonth(mesDtIni    != "" ? mesDtIni-1 : "01");
		dtInicio.setDate(diaDtIni     != "" ? diaDtIni   : "01");
		dtInicio.setHours(horDtIni    != "" ? horDtIni   : "00");
		dtInicio.setMinutes(minDtIni  != "" ? minDtIni   : "00");
		dtInicio.setSeconds(secDtIni  != "" ? secDtIni   : "00");
		
		// Instancia a data de t�rmino
		dtTermino = new Date();
		dtTermino.setFullYear(anoDtFim != "" ? anoDtFim   : "1900");
		dtTermino.setMonth(mesDtFim    != "" ? mesDtFim-1 : "01");
		dtTermino.setDate(diaDtFim     != "" ? diaDtFim   : "01");
		dtTermino.setHours(horDtFim    != "" ? horDtFim   : "00");
		dtTermino.setMinutes(minDtFim  != "" ? minDtFim   : "00");
		dtTermino.setSeconds(secDtFim  != "" ? secDtFim   : "00");

		// Compara as datas
		return (dtTermino.getTime() - dtInicio.getTime()) /1000/60/60;		
		
	},
    
    // Adiciona dias em uma data e retorna-a atualizada
    adicionaDiasNaData : function(data, dias) {
    	
    	// Tratamento das Variaveis.
        // var txtData = "01/01/2007"; //poder ser qualquer outra
        // var DiasAdd = 10 // Aqui vem quantos dias voc� quer adicionar a data
    	
        var d = new Date();
        // Aqui eu "mudo" a configura��o de datas.
        // Crio um obj Date e pego o campo txtData e 
        // "recorto" ela com o split("/") e depois dou um
        // reverse() para deixar ela em padr�o americanos YYYY/MM/DD
        // e logo em seguida eu coloco as barras "/" com o join("/")
        // depois, em milisegundos, eu multiplico um dia (86400000 milisegundos)
        // pelo n�mero de dias que quero somar a txtData.
        d.setTime(Date.parse(data.split("/").reverse().join("/"))+(86400000*(dias)));
        
        // Crio a var da DataFinal                      
        var DataFinal;

        // Aqui comparo o dia no objeto d.getDate() e vejo se � menor que dia 10.                       
        if(d.getDate() < 10) {
            // Se o dia for menor que 10 eu coloca o zero no inicio
            // e depois transformo em string com o toString()
            // para o zero ser reconhecido como uma string e n�o
            // como um n�mero.
            DataFinal = "0" + d.getDate().toString();
            
        } else {       
            // Aqui a mesma coisa, por�m se a data for maior do que 10
            // n�o tenho necessidade de colocar um zero na frente.
            DataFinal = d.getDate().toString();     
        }
        
        // Aqui, j� com a soma do m�s, vejo se � menor do que 10
        // se for coloco o zero ou n�o.
        if((d.getMonth()+1) < 10) {
        	DataFinal += "/0" + (d.getMonth()+1).toString() + "/" + d.getFullYear().toString();
        } else {
            DataFinal += "/" + (d.getMonth()+1).toString() + "/" + d.getFullYear().toString();
        }
    	
        return DataFinal; 
    }

};