//Classe interna, carregada no OnLoad
var Interna = {
	
	// Método principal da classe
	init : function() {
		
		// No evento blur do campo
		$("#cd_coluna").blur(function(){
			
			if (Interna.cd_coluna != $(this).val()) {					
				
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
		
		Interna.validaTipoColuna(Interna.tp_coluna);
		
		// Dispara o evento change do combo TIPO
		$("#tp_coluna_ac").bind( "autocompleteselect", function(event, ui) {
							
			// Executa as validações de acordo com o tipo selecionado
			Interna.validaTipoColuna($(ui.item.option).val());				
			
		});
		
		// Controla o evento BLUR do campo TAMANHO
		$("#tamanho").blur(function() {
			
			var tamanhoCampo = 0;
			var divCampo     = Array();
			
			if ($.trim($(this).val()) != "") {
				
				divCampo     = $(this).val().split(",");
				tamanhoCampo = divCampo[0];
				
				$("#default_value").val("").attr("maxlength", tamanhoCampo);
			
			}
			
		});
		
		
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
    	
    		$("#tamanho").val("").unmask().unmaskMoney().removeClass("decimal").removeClass("numero");
    		$("#default_value").val("").attr("readonly", "readonly");
    		$("#default_value").unmask().datepicker( "destroy" );
    		
    		switch (valCombo)
        	{
    	    	case "B" :
    	    	case "D" :
    	    	case "L" :
    	    		
    	    		if (valCombo == "D") {
    	    			$("#default_value").removeAttr("readonly");
    	    			Mascara.setDataHora("#default_value").datepicker( "enable" ); 	    			
    	    		}
    	    		
    	    		$("#tamanho").val("").attr("readonly", "readonly");
    	    		
    	    		break;
    	    		
    	    	case "N" :
    	    	case "C" :
    	    		
    	    		if (valCombo == "N") {
    	    			Mascara.setDecimal("#tamanho");
    	    		} else {
    	    			Mascara.setNumero("#tamanho");
    	    		}
    	    		
    	    		$("#tamanho").removeAttr("readonly");
    	    		$("#default_value").attr("maxlength",0);
    	    		$("#default_value").removeAttr("readonly").removeAttr("disabled");
    	    		
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