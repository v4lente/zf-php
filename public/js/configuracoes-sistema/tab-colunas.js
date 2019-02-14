//Classe interna, carregada no OnLoad
var Interna = {
	
	// Método principal da classe
	init : function() {
		
		// No evento blur do campo
		$("#cd_coluna").blur(function(){
			
			if (Interna.cd_coluna != $(this).val() && $.trim($(this).val()) != "") {					
				
				// Seta a variavel
				Interna.cd_coluna = $(this).val();
				
				// Verifica se existe o codigo da tabela informado
				if (Interna.retornaDadosX($(this)) != "") {
					
					// Limpa o campo
					$(this).val("");
					
					// Mosta mensagem de validação
					Base.montaMensagemSistema(Array("Coluna informada já existente para esta Tabela"), "Atenção", 3, "$('#cd_coluna').focus();");
				}					
			}
											
		});
						
		// Verifica se o campo existe
		if ($("#fl_pk").size() > 0){
			
			if ($("#fl_pk:checked").size() == 1) {
				Form.desabilitaCamposById("fl_null").removeAttr("checked");			
			} else {
				Form.habilitaCamposById("fl_null");
			}
		}
		
		// Controa o campo falg chave primaria
		$("#fl_pk").click(function() {
			
			if ($(this).attr("checked") == true) {
				Form.desabilitaCamposById("fl_null").removeAttr("checked");			
			} else {
				Form.habilitaCamposById("fl_null");
			}
		});
		
		// Se o campo possuir valor, aplica as consistências necessárias
		if($("#tp_coluna").val() != "_null_") {
			Interna.validaTipoColuna($("#tp_coluna").val());
		}
	
		// Dispara o evento change do combo TIPO
		$("#tp_coluna_ac").bind( "autocompleteselect", function(event, ui) {

			// limpa o campo
			$("#tamanho").val("");
			$("#default_value").val("");
			
			// Executa as validações de acordo com o tipo selecionado
			Interna.validaTipoColuna($(ui.item.option).val());				
			
		});
		
		// Seta o valor padrão
		$("#tamanho").blur(function() {
			
			var valor = $(this).val();
			
			// Se o valor for numérico pega a parte inteira
			if(valor.search(",") >= 0) {
				divValor = valor.split(",");
				valor    =  divValor[0];
			}
			
			$("#default_value").attr("maxlength", valor);
			
		});
		
	},
	
	// Especificidade da action 
	initNovoAction : function () {
		
	},
	
	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	// Salva o registro
	salvar : function() {
		
		// Faz a validação do formulário
		valida = Interna.validaFormulario();

		if (valida[0]) {
			$('#form_base').attr({
				onsubmit: 'return true',
				action: baseUrlController + '/salvar',
				target: ''
			}).submit();
		} 
		
	},

	// Excluir o registro
	excluir : function() {
		$('#form_base').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/excluir',
			target: ''
		}).submit();
	},
	
	// Chama a tela de pesquisa
	telaPesquisa : function() {
		
		document.location.href = baseUrlController + "/index";
	},

	// Executa a tela de pesquisa
	pesquisar : function() {
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/pesquisar',
			target: ''
		}).submit();
	},

	// Seleciona um registro, recebendo a chave já criptografada
	selecionar : function(chave) {
		document.location.href = baseUrlController + "/selecionar/" + chave;
	},

	// Chama a tela de relatórios
	relatorio : function(metodo){
		
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/relatorio/_rel_metodo/' + metodo,
			target: '_blank'
		}).submit();
		
	},
	
	// Volta para a listagem
	voltar : function() {		
		document.location.href = paginaAnterior;		
	},

	// Mostra a ajuda
	ajuda : function() {
		Base.ajuda();
	},

	// Fecha o documento
	sair : function() {
		Base.sair();
	},    
	
	// Retorna o registro da coluna referente ao código da tabela informado
    retornaDadosX : function(cd_coluna) {
		
	    var retorno = null;
	    
	    // Retorna o registro da coluna referente ao código da tabela informado
	    $.ajax({
            type    : "GET",            
            async   : false,
            url     : baseUrlController + "/retorna-dados-x/cd_coluna/"+ $(cd_coluna).val(),
            success : function(data) {
            	retorno = data;         	
            }
	    });
	    
        return retorno;
    	
	},
	
	//Valida o tipo da coluna com as regras do sistema
    validaTipoColuna : function(valCombo){
    	
    	if (valCombo != "_null_") {
    		
    		$("#tamanho").unmask().unmaskMoney().unbind("keydown").unbind("keyup");
    		$("#tamanho").removeClass("decimal").removeClass("numero");
    		Form.desabilitaCamposById("default_value");
    		$("#default_value").unmask().datepicker( "destroy" );
    		
    		switch (valCombo)
        	{
    	    	case "B" :
    	    	case "D" :
    	    		// Se o campo for DATE
    	    		if (valCombo == "D") {
    	    			
    	    			Form.habilitaCamposById("default_value");
    	    			Mascara.setDataHora("#default_value").datepicker( "enable" );
    	    		} 
    	    		// Proteje o campo tamanho e limpa
    	    		Form.desabilitaCamposById("tamanho").val("");    	    		
    	    		
    	    		break;
    	    		
    	    	case "N" :
    	    	case "C" :
    	    		
    	    		// Se o campo for NUMBER
    	    		if (valCombo == "N") {
    	    			Mascara.setDecimal("#tamanho");
    	    			Mascara.setNumero("#default_value"); 
    	    			if($("#tamanho").val() == "0,00") {
    	    				$("#tamanho").val("");
    	    			}
    	    		} else {
    	    		// Se o campo for STRING
    	    			Mascara.setNumero("#tamanho");
    	    			Mascara.unsetNumero("#default_value");
    	    		}
    	    		
    	    		Form.habilitaCamposById("default_value");
    	    		Form.habilitaCamposById("tamanho");
    	    		    	    		
    	    		break;
    	    		
    	    	default : /**/ ;
        	}
    		
    	}
    			
    },
    
	//Valida inconsistências e exceções na aplicação.
    validaFormulario : function(){
    	
    	var mensagem   = Array();    	
    	
    	// Chava a validação do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;		
    }
  
};