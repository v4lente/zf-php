//Classe interna, carregada no OnLoad
var Interna = {
		
	// Método principal da classe
	init : function() {
        
		// Troca o título do label porto origem/destino conforme
		// o tipo de movimentação e a origem da pesquisa dos portos
		$("#cd_grupo_ac").bind("autocompleteselect", function(event, ui) {
			
			// Captura o valor do tipo de movimentação
			var valor    = $(ui.item.option).val();
			var ambiente = $(ui.item.option).attr("ambiente");
			
			// Mostra o ambiente
			if(valor != "_null_") {
				$("#ambiente").html(ambiente);
			} else {
				$("#ambiente").html("");
			}
			
		});
		
		// Aplica o autocomplete no nome do usuário			 
		$("#no_usuario").autocomplete({
			source: baseUrlController + "/retorna-usuario-x/",
			minLength: 3,
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

	// Seleciona um registro, recebendo a chave já criptografada
	selecionar : function(chave) {
		
	},

	// Chama a tela de relatórios
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
			
			// Para outros tipos de relatório não será disponibilizado a visualização
			Base.montaMensagemSistema(Array("Relatório não disponível para este formato."), "ATENÇÃO!", 4);
			
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
	
	// Valida os dados para geração do relatório
	valida : function() {
		
		// Instancia as variáveis de controle
		var mensagem = Array();
		
		// Verifica se foi removido o usuário
		if($("#no_usuario").val() == "") {
			$("#cd_usuario").val("");
		}
		
		// Captura os dados da pesquisa
		var dtAtual = Data.retornaDataAtual();
		var cdGrupo   = $("#cd_grupo").val();
		var cdUsuario = $("#cd_usuario").val();
		var dtIni     = $("#dt_acesso_ini").val();
		var dtFim     = $("#dt_acesso_fim").val();
				
		// Verifica se a data inicial é válida
		if(dtIni != "" && ! Data.verificaData(dtIni)) {
			mensagem.push("A data inicial do período não é válida.");
		}
		
		// Verifica se a data final é válida
		if(dtFim != "" && ! Data.verificaData(dtFim)) {
			mensagem.push("A data Final do período não é válida.");
		}
		
		// Verifica se a data inicial é menor que a data final
		if(dtIni != "" && dtFim != "" && ! Data.verificaIntervaloData(dtIni, dtFim)) {
			mensagem.push("A data inicial do período não pode ser maior que data final do mesmo.");
		}
		
		// Verifica se um destes campos está preenchido		
		if(cdGrupo == "_null_" && cdUsuario == "" && dtIni == "") {
			mensagem.push("Pelo menos um dos campos da pesquisa deve estar preenchido.");
		}
		
		// Chama a validação do sistema
        valida = Valida.formulario('form_pesquisar', true, mensagem);
        
        return valida;
		
	}
};