//Classe interna, carregada no OnLoad
var Interna = {
	
	indice : 0,
	
	
	// Método principal da classe
	init : function() {
		
		// Aplica o autocomplete no campo do código do sistema
		$("#cd_sistema").autocomplete({
			source: baseUrlController + "/auto-complete-x/campo/cd_sistema",
			minLength: 1,
			select: function( event, ui ) {
				// Preenche os campos com o retorno do AJAX
				$("#no_sistema").val(ui.item.descricao);				
			}
		});
		
		
		// Aplica o autocomplete no campo do nome do sistema
		$("#no_sistema").autocomplete({
			source: baseUrlController + "/auto-complete-x/campo/no_sistema/",			
			minLength: 1,
			select: function( event, ui ) {
				// Preenche os campos com o retorno do AJAX
				$("#cd_sistema").val(ui.item.codigo);
			}
		});
		
		
		// Contrala o icone de excluir da listagem
		if ($("div.excluirLinha").size() > 0) {
			
			// Seta o cursor para o icone
			$("div.excluirLinha").css("cursor","pointer");
			
			// Quando executado o evento click do botão excluir
			$("div.excluirLinha").click(function(){				
				
				// Pega o atributo KEY da linha da tabela
				Interna.indice = $(this).closest("tr").attr("key");
				
				// Gera uma mensagem de adivertencia para o usuário, caso confirme é disparado o evento excluir da classe
				Base.confirm("Deseja excluir o registro selecionado?", "ATENÇÃO", "Interna.excluir();");
				
			});	
		}
		
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
			
		} else {
			
			$("#cd_sistema").val("").focus();
    		$("#no_sistema").val("");
		}
		
	},

	// Excluir o registro
	excluir : function() {
		
		// Declara as variaveis
		var ret = null;
	
		// Retorna os chassis
	    $.ajax({
            type    : "GET",            
            async   : false,
            url     : baseUrlController + "/excluir/" + Interna.indice,
            success : function(data) {
            	ret = data;
            }
	    });
		
	    // Caso tenha excluido o registro
	    if (ret.retorno == 1) {	    	
	    	
	    	// Mosta a tela de carregando
	    	Base.mostraTelaCarregando();
	    	
	    	// Direciona a tela do usuário para a listagem
	    	document.location.href = baseUrlController + "/index";
	    }
	    
	    
	},
	
	// Chama a tela de pesquisa
	telaPesquisa : function() {
		document.location.href = baseUrlController + "/index";
	},

	
	// Seleciona um registro, recebendo a chave já criptografada
	selecionar : function(chave) {
		document.location.href = baseUrlController + "/selecionar/" + chave;
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
	    
	// Retorna o sitema vinculado a tabela pai
	retornaSistemaVinculadoTabelaX : function() {
		
		// Declara as variaveis
		var retorno = null;
		
		if ($.trim($("#cd_sistema").val()) != "") {
			
			// Retorna os chassis
		    $.ajax({
	            type    : "GET",            
	            async   : false,
	            url     : baseUrlController + "/retorna-sistema-vinculado-tabela-x/cd_sistema/" + $("#cd_sistema").val(),
	            success : function(data) {
	            	retorno = data;
	            }
		    });		    
		}
		
		return retorno;
		
	},
	
	//Valida inconsistências e exceções na aplicação.
    validaFormulario : function(){
    	
    	var mensagem   = Array();    	
    	
    	var valor = Interna.retornaSistemaVinculadoTabelaX();
    	
    	// Verifica se existe o sistema informado
    	if (valor == -1) {
    		mensagem.push("O Sistema informado não existe");
    	}
    	
    	// Verifica se existem algum vinculo do sistema com a tabela pai
    	if (valor > 0) {    		
    		mensagem.push("O Sistema já encontra-se vinculado a essa Tabela");
    	}
    	
    	 // Chava a validação do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;    	
    }
  
};