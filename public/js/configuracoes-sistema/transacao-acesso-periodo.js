//Classe interna, carregada no OnLoad
var Interna = {
		
	// M�todo principal da classe
	init : function() {
		
		// Captura o vento click do bot�o limpar
		$("#botao-limpar").bind("click", function() {
			$("#ambiente").html("");
			Form.desabilitaCamposById("cd_transacao");
			Form.atualizaCombo($("#cd_transacao"));
			$("#agrupamento-T").attr("checked", true);
		});
		
		// Valida o campo sistema no evento onblur
		$("#cd_sistema_ac").blur(function(){
			
			if ($(this).val() == " -- Selecione -- ") {				
				$("#ambiente").html("");				
				// Desabilita o combo transa��o
				Form.desabilitaCamposById("cd_transacao");
				Form.atualizaCombo($("#cd_transacao"));
			}
			
		});
		
		// Troca o t�tulo do label porto origem/destino conforme
		// o tipo de movimenta��o e a origem da pesquisa dos portos
		$("#cd_sistema_ac").bind("autocompleteselect", function(event, ui) {
			
			// Captura o valor do tipo de movimenta��o
			var valor    = $(ui.item.option).val();
			var ambiente = $(ui.item.option).attr("ambiente");
			
			// Mostra o ambiente
			if(valor != "_null_") {
				$("#ambiente").html(ambiente);
				
				// Habilita o combo transa��o
				Form.habilitaCamposById("cd_transacao");
				
				// Popula o combo transa��es filtrando pelo c�digo do sistema
				Interna.retornaTransacoesX(valor, ambiente);
				
			} else {
				
				$("#ambiente").html("");
				
				// Desabilita o combo transa��o
				Form.desabilitaCamposById("cd_transacao");
				Form.atualizaCombo($("#cd_transacao"));
			}			
		});


		
		// Aplica o autocomplete no nome do usu�rio			 
		$("#no_usuario").autocomplete({
			source: baseUrlController + "/retorna-usuario-x/",
			minLength: 3,
			change : function (event, ui) {
				
				if(ui.item == null) {
					$("#no_usuario").val("");
				}
			},
			search: function(event, ui) {				
				// Limpa os campos
				$("#cd_usuario").val("");
			},
			select: function(event, ui) {
				
				// Joga os valores nos campos
				$("#cd_usuario").val(ui.item.cd_usuario);
								
			}
		});
		
		

	},

	// Retorna todas as transa��es filtrando pelo ambiente [WEB|CLIENTE/SERVIDOR]
    retornaTransacoesX : function(sistema, ambiente) {
    	
		// Executa a requisi��o AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-transacoes-x/"
		, { ambiente     : ambiente
			, cd_sistema : sistema } // Seta os parametros a ser passado por ajax	
		, function (retorno){        // Pega o retorno 
			
			// Combo, objeto de retorno, campos de c�digo e descri��o
			Form.montaCombo($("#cd_transacao"), retorno, Array("CD_TRANSACAO", "NO_TRANSACAO"));
			Form.atualizaCombo($("#cd_transacao"));
			
		} );		
	},
	
	
	// Gera um novo documento
	novo : function() {
		
	},

	// Salva o registro
	salvar : function() {
        
	},

	// Excluir o registro
	excluir : function() {
		
	},

	// Chama a tela de pesquisa
	telaPesquisa : function() {
		
	},

	// Executa a tela de pesquisa
	pesquisar : function() {
		
	},

	// Seleciona um registro, recebendo a chave j� criptografada
	selecionar : function(chave) {
		
	},

	// Chama a tela de relat�rios
	relatorio : function(metodo){
		
		if(metodo == "pdf") {
			// Valida os dados da pesquisa
			var valida = Interna.valida();
			
			if(valida[0]) {
				$('#form_pesquisar').attr({
					onsubmit: 'return true',
					action: baseUrlController + '/relatorio/_rel_metodo/' + metodo,
					target: '_blank'
				}).submit();
			}
		} else {
			
			// Para outros tipos de relat�rio n�o ser� disponibilizado a visualiza��o
			Base.montaMensagemSistema(Array("Relat�rio n�o dispon�vel para este formato."), "ATEN��O!", 4);
			
		}
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
	
	// Valida os dados para gera��o do relat�rio
	valida : function() {
		
		// Instancia as vari�veis de controle
		var mensagem = Array();
		
		// Verifica se foi removido o usu�rio
		if($("#no_usuario").val() == "") {
			$("#cd_usuario").val("");
		}
		
		// Captura os dados da pesquisa
		var dtAtual = Data.retornaDataAtual();
		var cdSistema   = $("#cd_sistema").val();
		var cdUsuario = $("#cd_usuario").val();
		var dtIni     = $("#dt_acesso_ini").val();
		var dtFim     = $("#dt_acesso_fim").val();
				
		// Verifica se a data inicial � v�lida
		if(dtIni != "" && ! Data.verificaData(dtIni)) {
			mensagem.push("A data inicial do per�odo n�o � v�lida.");
		}
		
		// Verifica se a data final � v�lida
		if(dtFim != "" && ! Data.verificaData(dtFim)) {
			mensagem.push("A data Final do per�odo n�o � v�lida.");
		}
		
		// Verifica se a data inicial � menor que a data final
		if(dtIni != "" && dtFim != "" && ! Data.verificaIntervaloData(dtIni, dtFim)) {
			mensagem.push("A data inicial do per�odo n�o pode ser maior que data final do mesmo.");
		}
		
		// Verifica se um destes campos est� preenchido		
		if(cdSistema == "_null_" && cdUsuario == "" && dtIni == "") {
			mensagem.push("Pelo menos um dos campos da pesquisa deve estar preenchido.");
		}
		
		// Chama a valida��o do sistema
        valida = Valida.formulario('form_pesquisar', true, mensagem);
        
        return valida;
		
	}
};