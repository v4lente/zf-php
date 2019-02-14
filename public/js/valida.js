var Valida = {
		
    // Método principal da classe
	init : function() {
		
		// Validações globais *************************************
		
		// Valida todos os campos que tiverem a classe HORAMINUTO
		if ($(".horaminuto").size() > 0){
			// Quando o foco for removido
			$(".horaminuto").blur(function(){
				// Verifica se é uma tata valida
				if(Data.verificaHoraMinuto($(this).val()) == false) {
					// Limpa o campo
					$(this).val("");
				}			
			});			
		}
		
	},
	
	/**
     * Validação dos formularios
     * validar campos cujos estejam com o atributo (class="required")
     * 
     * @param object	Formulário de cadastro
     * @param boolean	Mostra ou não as exceções.
     * @param array		Exceções específicas da aplicação
     * @param string	Chama um evento ao fechar a tela
     * 
     * @return Array
     */
	formulario : function (form, alerta, mensagensAplicacao, eventoFechar){
		var arrayCamposHidden     = Array();
		var arrayValidacoesHidden = Array();
		var arrayCampos           = Array();
		var arrayValidacoes       = Array();

		// Verifica se foi passado algum ID de FORM
		if ($.trim(form) == '') {
			return Array(false, Array());
		}

		// Verifica se há campos texto no formulário e percorre um a um.
		if ($('#' + form + ' :input').length > 0 ) {			
			
			// Grava os campos radio e checkbox que cairão na validação
			var camposChecadosVerificados = Array();
			
			// Percorre todos os elementos do formulário
			$('#' + form + ' :input').each(function(i) {
				
				var label = "";
				
				// Pega o conteudo do label
				if ($(this).attr('alt')) {
					label = $.trim($(this).attr('alt'));
				} else if ($(this).attr('title')) {
					label = $.trim($(this).attr('title'));
				} else if ($("label[for='" + $(this).attr('name') + "']").text() != "") {
					label = $("label[for='" + $(this).attr('name') + "']").text();
				} else if ($("label[for='" + $(this).attr('id')+"']").text() != "") {
					label = $("label[for='" + $(this).attr('id') + "']").text();
				} else if ($("label[for='" + $(this).attr('id') + "_ac']").text() != "") {
					label = $("label[for='" + $(this).attr('id') + "_ac']").text();
				} else if ($(this).attr('name')) {
					label = $.trim($(this).attr('name'));
				} else {
					label = $.trim($(this).attr('id'));
				}
				
				// Validação de campos requeridos
				if ($(this).hasClass(new Array("required"))) {
					
					var nomeCampo = $(this).attr("name");
					var tipoCampo = $(this).attr("type");
					
					if(tipoCampo == "hidden") {
					
						if ($.trim($(this).val()) == '' ) {
							arrayCamposHidden.push(this);
							arrayValidacoesHidden.push(label + ' Campo Obrigatório');
						}
						
					} else if(tipoCampo == "radio" || tipoCampo == "checkbox") {
						
						if($.inArray(nomeCampo, camposChecadosVerificados) == -1) {
							// Insere o campo no array para não percorrer os demais de seu grupo
							(camposChecadosVerificados).push(nomeCampo);
							
							if($("[name=" + nomeCampo + "]").is(":checked") == false) {
								arrayCampos.push(this);
								arrayValidacoes.push(label + ' Campo Obrigatório');
							}
						}
						
					} else if(tipoCampo == "select-one") {
						
						if ($.trim($(this).val()) == '' ||  $.trim($(this).val()) == '_null_') {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Obrigatório');
						}
						
					} else {
						
						if ($.trim($(this).val()) == '' ) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Obrigatório');
						}
					}
				}
				
				// Validação de campos inconsistentes
				if ($(this).attr("inconsistent") != undefined) {
					
					var valorCampo = ' Campo Inconsistente';
					if($(this).attr("inconsistent") != "") {
						valorCampo = ' ' + $(this).attr("inconsistent"); 
					}
					
					arrayCampos.push(this);
					arrayValidacoes.push(label + valorCampo);
				}

				// Validação de campos data (DD/MM/YYYY)
				if ($(this).hasClass(new Array("data"))) {
					if ($.trim($(this).val()) != '' ) {
						if(! Data.verificaData($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}

				// Validação de campos data e hora (DD/MM/YYYY HH:MM:SS)
				if ($(this).hasClass("datahora")) {
					if ($.trim($(this).val()) != '') {
						if(! Data.verificaDataHora($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}

				// Validação de campos hora (HH:MM:SS)
				if ($(this).hasClass(new Array("hora"))) {
					if ($.trim($(this).val()) != '') {
						if(! Data.verificaHora($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}

				// Validação de campos hora e minuto (HH:MM)
				if ($(this).hasClass(new Array("horaminuto"))) {
					if ($.trim($(this).val()) != '') {
						if(! Data.verificaHoraMinuto($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}
				
				// Validação de campos mes e ano (MM/AAAA)
				if ($(this).hasClass(new Array("mesano"))) {
					if ($.trim($(this).val()) != '') {
						if(! Data.verificaMesAno($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}
				
                // Validação de campos CEP (00000-000)
				if ($(this).hasClass("cep")) {
					if ($.trim($(this).val()) != '') {
						if(! Valida.cep($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}
                
				// Validação de campos CPF (000.000.000-00)
				if ($(this).hasClass("cpf")) {
					if ($.trim($(this).val()) != '') {
						if(! Valida.cpf($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}
				
				// Validação de campos CNPJ
				if ($(this).hasClass("cnpj")) {
					if ($.trim($(this).val()) != '') {
						if(! Valida.cnpj($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}
				
				// Validação de campos E-MAIL
				if ($(this).hasClass("email")) {
					if ($.trim($(this).val()) != '') {
						if(! Valida.email($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}
                
                // Validação de campos E-MAIL
				if ($(this).hasClass("fone")) {
					if ($.trim($(this).val()) != '') {
						if(! Valida.fone($(this).val())) {
							arrayCampos.push(this);
							arrayValidacoes.push(label + ' Campo Inválido');
						}
					}
				}

			});
		}
		
		// Inicializa a variavel que conterá as mensagens
		var arrayMensagens = Array();
		
		// Monta as mensagens não automáticas, que vieram da transação
		if (mensagensAplicacao != undefined && mensagensAplicacao[0] != ""){
			// Monta mensagens de erro da aplicação
			$(mensagensAplicacao).each(function(i){
				if (mensagensAplicacao[i] != ""){
					arrayMensagens.push(mensagensAplicacao[i]);
				}
			});
		}

		// Se tiver algum campo com exceção do padrão ou da aplicação
		if (arrayCampos.length > 0) {
			// Monta mensagens de erro padrão
			$(arrayValidacoes).each(function(i){
				arrayMensagens.push(arrayValidacoes[i]);
			});
			
		} else if(arrayCamposHidden.length > 0) {
			// Monta mensagens de erro padrão
			$(arrayValidacoesHidden).each(function(i){
				arrayMensagens.push(arrayValidacoesHidden[i]);
			});
		}
		
		if(arrayMensagens.length > 0) {
			// Verifica se é pra mostrar as mensagens na view
			if(alerta == null || alerta == true) {
				
				if(eventoFechar == undefined) {
					eventoFechar = "";
				}
				// Alert na tela das inconsistências
				msg = Base.montaMensagemSistema(arrayMensagens, 'ATENÇÃO!', 4, eventoFechar);
			}
			
			// Esconde a tela de carregando
			Base.escondeTelaCarregando();
			
			// Retorna falso
			return Array(false, arrayCampos);
			
		} else {
			// Retorna verdadeiro
			return Array(true, Array());
		}
				
	},

	/**
	 * Valida o vento passado para a 
	 * 
	 * @param event
	 * 
	 * @return boolean
	 */ 
    validaEventoPickList: function(evento){

    	var codigoEvento = null;
		try {
			if (isNaN(evento)) {
				codigoEvento = (evento.keyCode ? evento.keyCode : evento.which);
			} else {
				codigoEvento = evento;
			}

			if(typeof(codigoEvento) == 'undefined'){
				codigoEvento = 9;
			}

		} catch(e){
			codigoEvento = evento;
			
			// Esconde a tela de carregando
			Base.escondeTelaCarregando();
		}		

		// Verifica o evento do usuário
		//     codigoEvento
		//            - 9:  tab
		//            - 13: Enter		
		if (codigoEvento != "undefined" && (codigoEvento == 9 || codigoEvento == 13)) {	
			return true;
		}

		return false;    	
    },
    
    /**
     * Valida o CEP
     *
     * @param string 
     * 
     * @return boolean
     */
    cep : function(CEP) {
        
        //Nova variável "cep" somente com dígitos.
        var cep = CEP.replace(/\D/g, '');

        // Verifica se campo cep possui valor informado.
        if (cep != "") {

            // Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;

            // Valida o formato do CEP.
            if(validacep.test(cep)) {
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
        
    },
	
	/**
     * Valida o CPF
     *
     * @param string 
     * 
     * @return boolean
     */
    cpf : function(CPF){

    	if (CPF.charAt(3) == '.') {
    		CPF = CPF.substr(0,3) + CPF.substr(4);
    	}

    	if (CPF.charAt(6) == '.') {
    		CPF = CPF.substr(0,6) + CPF.substr(7);
    	}

    	if (CPF.charAt(9) == '-') {
    		CPF = CPF.substr(0,9) + CPF.substr(10);
    	}

    	if (CPF.length != 11 || CPF == "00000000000" || CPF == "11111111111" ||
    	CPF == "22222222222" || CPF == "33333333333" || CPF == "44444444444" ||
    	CPF == "55555555555" || CPF == "66666666666" || CPF == "77777777777" ||
    	CPF == "88888888888" || CPF == "99999999999") {
    		return false;
    	}
    		
    	soma = 0;
    	for (i=0; i < 9; i ++) {
    		soma += parseInt(CPF.charAt(i)) * (10 - i);
    	}    		
    	
    	resto = 11 - (soma % 11);
    	
    	if (resto == 10 || resto == 11) {
    		resto = 0;
    	}
    		
    	if (resto != parseInt(CPF.charAt(9))){
    		return false;
    	}
    		
    	soma = 0;    	
    	for (i = 0; i < 10; i ++){
    		soma += parseInt(CPF.charAt(i)) * (11 - i);
    	}
    		
    	resto = 11 - (soma % 11);
    	
    	if (resto == 10 || resto == 11){
    		resto = 0;
    	}
    		
    	if (resto != parseInt(CPF.charAt(10))){
    		return false;
    	}    		

    	return true;
    },
    
    /**
     * Valida o CNPJ
     * 
     * @param String
     * 
     * @return boolean
     */
    cnpj : function (cnpj){
		
		var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
		digitos_iguais = 1;
		cnpj = cnpj.replace(/\D+/g, '');
		
		if (cnpj.length != 14){
			return false;
		}

		for (i = 0; i < cnpj.length - 1; i++)
			if (cnpj.charAt(i) != cnpj.charAt(i + 1)){
				digitos_iguais = 0;
				break;
			}
			if (!digitos_iguais){
				tamanho = cnpj.length - 2;
				numeros = cnpj.substring(0,tamanho);
				digitos = cnpj.substring(tamanho);
				soma = 0;
				pos = tamanho - 7;
				for (i = tamanho; i >= 1; i--){
					soma += numeros.charAt(tamanho - i) * pos--;
					if (pos < 2)
						pos = 9;
				}
				resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
				if (resultado != digitos.charAt(0)){
					return false;
				}

				tamanho = tamanho + 1;
				numeros = cnpj.substring(0,tamanho);
				soma = 0;
				pos = tamanho - 7;
				for (i = tamanho; i >= 1; i--){
					soma += numeros.charAt(tamanho - i) * pos--;
					if (pos < 2)
						pos = 9;
				}
				resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
				if (resultado != digitos.charAt(1)){
					return false;
				}
				else {
					return true;
				}
			}
		else{
			return false;
		}		
		
    },
    
    /**
     * Valida o FONE
     *
     * @param string 
     * 
     * @return boolean
     */
    fone : function(fone) {
        
        // Remove os underline da mascara
        fone = fone.replace(/_/g, '');
        
        // Verifica se campo cep possui valor informado.
        if (fone != "") {

            // Expressão regular para validar o CEP.
            var validafone = /^1\d\d(\d\d)?$|^0800 ?\d{3} ?\d{4}$|^(\(0?([1-9a-zA-Z][0-9a-zA-Z])?[1-9]\d\) ?|0?([1-9a-zA-Z][0-9a-zA-Z])?[1-9]\d[ .-]?)?(9|9[ .-])?[2-9]\d{3}[ .-]?\d{4}$/gm;

            // Valida o formato do CEP.
            if(validafone.test(fone)) {
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
        
    },
    
    /**
     * Faz a validação dos e-mails
     */
    email : function(email) {
    	
    	//retorna true se a regra for obdecida 
    	var expr = /^[\w!#$%&'*+\/=?^`{|}~-]+(\.[\w!#$%&'*+\/=?^`{|}~-]+)*@(([\w-]+\.)+[A-Za-z]{2,6}|\[\d{1,3}(\.\d{1,3}){3}\])$/;
    	return expr.test(email);
    	
    },
    
/**
	* Valida a CNH (modelo novo). CNH velhas (com nove dígitos) devem ser completadas com zeroa esquerda ou chamar Valida.pgu()
	*
        *  Leonardo Porto
        *  
	* @param String
	* 
	* @return boolean
	*/
	cnh : function (cnh) {

		var retorno = true;

		// retira espaços vazios
		cnh = $.trim(cnh);
        
		if (cnh != "") {

			if (cnh.length == 10) {
				cnh = "0" + cnh;
			}else if (cnh.length == 9) {
				cnh = "00" + cnh;
			}else if (cnh.length == 8) {
				cnh = "000" + cnh;
			}else if (cnh.length == 7) {
				cnh = "0000" + cnh;
			}
            else if (cnh.length != 11) {
				retorno = false;
			} 
           
			var cnh_inicio = cnh.substring(0,9);
			var cnh_dv = cnh.substring(9,11);

			var incr_dv2 = 0;
			var soma = 0;
			var mult = 9;

			for (i = 0;i < 9;i++) {
				soma = eval(parseInt(soma,10) + (parseInt(cnh_inicio[i],10) * parseInt(mult,10)));
				mult = eval(parseInt(mult - 1,10));
			}

			var dv1 = eval(parseInt(soma,10) % parseInt(11,10));
			var dv2 = 0;

			if (parseInt(dv1,10) == 10) {
				incr_dv2 = -2;
			}

			if (parseInt(dv1,10) > 9) {
				dv1 = 0;
			}

			soma = 0;
			mult = 1;

			for (i = 0;i < 9;i++) {
				soma = eval(parseInt(soma,10) + (parseInt(cnh_inicio[i],10) * parseInt(mult,10)));
				mult = eval(parseInt(mult + 1,10));
			}

			if (eval((parseInt(soma,10) % parseInt(11,10)) + parseInt(incr_dv2,10)) < 0) {
				dv2 = eval(parseInt(11,10) + (parseInt(soma,10) % parseInt(11,10)) + parseInt(incr_dv2,10));
			}

			if (eval((parseInt(soma,10) % parseInt(11,10)) + parseInt(incr_dv2,10)) >= 0) {
				dv2 = eval((parseInt(soma,10) % parseInt(11,10)) + parseInt(incr_dv2,10));
			}

			if (dv2 > 9) {
				dv2 = 0;
			}

			var dv_final = dv1 +""+ dv2;

			if ((retorno == true) && (cnh_dv == dv_final)) {
				// retorno = 'CNH Válida!';
				retorno = true;
			} else {
				// Testa como CNH antiga,sem foto
				retorno = Valida.pgu(cnh.substring(2,11));
			}

			return retorno;
		}
	},

	/**
	* Valida a PGU (modelo velho)
	* 
	* @param String
	* 
	* @return boolean
	*/
	pgu : function (cnh) {

		var retorno = true;

		// retira espaços vazios
		cnh = $.trim(cnh);

		if (cnh != "") {

			if (cnh.length != 9) {
				retorno = false;
			}

			var cnh_inicio = cnh.substring(0,8);
			var cnh_dv = cnh.substring(8,9);

			var soma = 0;
			var mult = 2;

			for (i = 0;i < 8;i++) {
				soma = eval(parseInt(soma,10) + (parseInt(cnh_inicio[i],10) * parseInt(mult,10)));
				mult = eval(parseInt(mult + 1,10));
			}

			var dv1 = eval(parseInt(soma,10) % parseInt(11,10));

			if (parseInt(dv1,10) > 9) {
				dv1 = 0;
			}

			if ((retorno == true) && (cnh_dv == dv1)) {
				// retorno = 'CNH Válida!';
				retorno = true;
			} else {
				// retorno = 'CNH Inválida!';
				retorno = false;
			}

			return retorno;
		}
	}    
    
};