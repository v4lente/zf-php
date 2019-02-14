//Classe interna, carregada no OnLoad
var Interna = {
		
	// Método principal da classe
	init : function() {
		
		// Aplica as funções ajax somente para estas ações
		if(no_action == "novo") {
			
			$(".ui-autocomplete-input").bind("autocompleteselect", function(event, ui) {
				// Captura a linha do campo
				var objTr    	= $(this).closest('tr').get(0); 
				var indice  	= $(objTr).attr("indice");
				
				// Captura o id do combo
				var idAC     	= $(this).attr("id");
				var idSelect 	= idAC.substring(0, idAC.length - 3);
				var classSelect = $("#" + idSelect).attr("class");
				var valorCombo  = $(ui.item.option).val();
				
				// Se for descelecionado o combo limpa os campos
				if(valorCombo == "" || valorCombo == "_null_") {
					
					Form.atualizaCombo($('#cd_grupo2_' + indice));
					$('#cd_grupo_' + indice).val("");
					
				} else {
					
					// Verifica se já não foi selecionado em outro campo o mesmo valor
					var selecionou = false;
					
					// Verifica se item selecionado não é o primeiro
					if(indice > 0) {
						
						// Captura o indice anterior
						var indiceAnterior = indice - 1;
						
						// Verifica se o código tem valor
						if($("#cd_grupo_" + indiceAnterior).val() != "_null_") {
							
							// Se o valor for diferente de nulo retorna os dados
							if($(ui.item.option).val() != "_null_") {
								
								// Passa o índice da linha e o valor do combo
								Interna.retornaGrupoX(indice, $(ui.item.option).val());
								
							}
							
						} else {
							
							// Caso o grupo não selecione as ações corretamente na sequência,
							// mostrará uma mensagem para ele não pular nenhum campo
							Base.montaMensagemSistema(Array("Seleciona os grupos na sequência."), 'ATENÇÃO!', 4);
						}
						
					} else {
						
						// Passa o índice da linha e o valor do combo
						Interna.retornaGrupoX(indice, $(ui.item.option).val());
					}
					
				}
				
			});
			
		} else if(no_action == "selecionar") {
			
			$(".ui-autocomplete-input").bind("autocompleteselect", function(event, ui) {
				
				// Captura o id do combo
				var idAC     	= $(this).attr("id");
				var idSelect 	= idAC.substring(0, idAC.length - 3);
				var classSelect = $("#" + idSelect).attr("class");
				var valorCombo  = $(ui.item.option).val();
				
				// Se for descelecionado o combo limpa os campos
				if(valorCombo == "" || valorCombo == "_null_") {
					
					Form.atualizaCombo($('#cd_usuario2'), Interna.cd_usuario2);
					
				} else {
					
					// Passa o índice da linha e o valor do combo
					Interna.retornaGrupoX(-1, valorCombo);
					
				}
				
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
		if(no_action == "novo") {
			var valida = Interna.validaNovo();
		} else if(no_action == "selecionar") {
			var valida = Interna.validaSelecionar();
		}

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
		
		// Captura a data atual
		var dtAtual = Data.retornaDataAtual();
		
		// Captura a data inicial
		var dtIni = $("#dt_acesso_ini").val();
		
		// Verifica se a data inicial é maior ou igual a data atual
		// Retorna falso na verificação se a data inicial for maior 
		// que a data final(Data Atual)
		if(dtIni == dtAtual || ! Data.verificaIntervaloData(dtIni, dtAtual)) {
			
			$('#form_base').attr({
				onsubmit: 'return true',
				action: baseUrlController + '/excluir',
				target: ''
			}).submit();
			
		} else {
			
			// Mostra a mensagem de erro
			Base.montaMensagemSistema(Array("Não é possível excluir o grupo de acesso se o período inicial for menor que a data atual."), "ALERTA", 4);
			
		}

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
	
	// Valida os dados antes de salvar
	validaNovo : function() {
		// Instancia as variáveis de controle
		var mensagem = Array();
		
		// Captura a data atual
		var dtAtual = Data.retornaDataAtual();
		
		// Controla os grupos e períodos
		var totalLinhasTabela = $("#tabelaCadastro > tbody > tr").size();
		
		// Percorre todos os campos e se possuir valor no campo
		// código verifica se não tem outro campo com mesmo valor 
		// e na mesma data
		var totalGruposCadastro = false;
		for(var i=0; i < totalLinhasTabela; i++) {
			
			// Se possuir valor no campo
			var codigo  = $("#cd_grupo_" + i).val();
			if(codigo != "") {
				
				// Ao menos um grupo deve possuir valor
				totalGruposCadastro = true;
				
				var dtIni   = $("#dt_acesso_ini_" + i).val();
				var dtFim   = $("#dt_acesso_fim_" + i).val();
				var dsGrupo = $("#cd_grupo2_" + i + "_ac").val();
				if(codigo != "") {
					
					// Verifica se as datas são válidas
					if($(mensagem).size() == 0) {
						
						// Verifica se a data inicial foi preenchida
						if($.trim(dtIni) == "") {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' deve ser preenchida.");
							break;
						}
						
						// Verifica se a data inicial é válida
						if(dtIni != "" && ! Data.verificaData(dtIni)) {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' não é válida.");
							break;
						}
						
						// Verifica se a data final é válida
						if(dtFim != "" && ! Data.verificaData(dtFim)) {
							mensagem.push("A data Final do grupo '" + dsGrupo + "' não é válida.");
							break;
						}
						
						// Verifica se a data inicial é menor que a data final
						if(dtIni != "" && dtFim != "" && (dtIni == dtFim || ! Data.verificaIntervaloData(dtIni, dtFim))) {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' não pode ser maior ou igual a data final do mesmo.");
							break;
						}
						
						// Verifica se a data inicial é menor que a data atual
						if(dtIni != "" && ! (dtIni == dtAtual || ! Data.verificaIntervaloData(dtIni, dtAtual))) {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' não pode ser menor que a data atual.");
							break;
						}
					}
					
					// Percorre os outros campos para comparação
					for(var j=0; j < totalLinhasTabela; j++) {
						
						// Se possuir valor no campo
						var codigo2 = $("#cd_grupo_" + j).val();
						if(codigo2 != "") {
							
							var dtIni2  = $("#dt_acesso_ini_" + j).val();
							var dtFim2  = $("#dt_acesso_fim_" + j).val();
							
							// Se os código forem iguais, verificas as datas
							if(i != j && codigo == codigo2) {
								
								// Verifica se somente um grupo possui data final de acesso
								if($(mensagem).size() == 0) {
									if(dtFim == "" && dtFim2 == "") {
										mensagem.push("O grupo '" + dsGrupo + "' não pode ter dois registros sem data final.");
										break;
									}
								}
								
								// Verifica se o grupo sem data final tem a data inicial maior que a dos outros
								// Na comparação retorna true se a data inicial (parametro 1) é menor que a 
								// data final (parametro 2)
								if($(mensagem).size() == 0) {
									if((dtFim  == "" && ! Data.verificaIntervaloData(dtIni2, dtIni)) || 
									   (dtFim2 == "" && ! Data.verificaIntervaloData(dtIni, dtIni2))) {
										mensagem.push("O grupo '" + dsGrupo + "' que não possui data final não pode ter a data inicial menor que os demais de mesmo código.");
										break;
									}
								}
								
								// Verifica se as datas iniciais são iguais para o mesmo grupo
								if($(mensagem).size() == 0) {
									if(dtIni == dtIni2) {
										mensagem.push("O grupo '" + dsGrupo + "' não pode ter duas datas iniciais iguais.");
										break;
									}
								}
								
							}
							
							// Se já tiver mensagem sai do laço para mostrar uma a uma
							if($(mensagem).size() == 0) {
								break;
							}
							
						}
						
					}
					
				}
				
			}
			
		}
		
		// Verifica se um grupo ao menos possui valor para inserção
		if(! totalGruposCadastro) {
			mensagem.push("Ao menos um grupo deve possuir valor.");
		}
				        
        // Chama a validação do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;
	},
	
	// Valida os dados da edição
	validaSelecionar : function() {
		
		// Instancia as variáveis de controle
		var mensagem = Array();
		
		// Captura a data atual
		var dtAtual = Data.retornaDataAtual();
		var dtIni   = $("#dt_acesso_ini").val();
		var dtFim   = $("#dt_acesso_fim").val();
		var dsGrupo = $("#no_grupo").val();
				
		// Verifica se a data inicial é válida
		if(dtIni != "" && ! Data.verificaData(dtIni)) {
			mensagem.push("A data inicial do grupo '" + dsGrupo + "' não é válida.");
		}
		
		// Verifica se a data final é válida
		if(dtFim != "" && ! Data.verificaData(dtFim)) {
			mensagem.push("A data Final do grupo '" + dsGrupo + "' não é válida.");
		}
		
		// Verifica se a data inicial é menor que a data final
		if(dtIni != "" && dtFim != "" && (dtIni == dtFim || ! Data.verificaIntervaloData(dtIni, dtFim))) {
			mensagem.push("A data inicial do grupo '" + dsGrupo + "' não pode ser maior ou igual a data final do mesmo.");
		}
		
		// Chama a validação do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;
		
	},
	
	// Retorna os dados da transação passada
	retornaGrupoX : function(indice, cd_grupo) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-grupo-x/"
		, { 'cd_grupo' : cd_grupo } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Se possuir indice é por que a requisição foi feita na tela de novo, 
			// caso contrário foi feita na tela de edição
			if(indice >= 0) {
				// Seta o valor nos campos
				$('#cd_grupo_'  + indice).val(retorno.CD_GRUPO);
				
			} else {
				$('#cd_grupo').val(retorno.CD_GRUPO);
			}
				
		} );
		
	}
    
};