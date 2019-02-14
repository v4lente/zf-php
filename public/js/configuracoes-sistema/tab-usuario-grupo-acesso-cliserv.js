//Classe interna, carregada no OnLoad
var Interna = {
		
	// M�todo principal da classe
	init : function() {
		
		// Aplica as fun��es ajax somente para estas a��es
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
					
					// Verifica se j� n�o foi selecionado em outro campo o mesmo valor
					var selecionou = false;
					
					// Verifica se item selecionado n�o � o primeiro
					if(indice > 0) {
						
						// Captura o indice anterior
						var indiceAnterior = indice - 1;
						
						// Verifica se o c�digo tem valor
						if($("#cd_grupo_" + indiceAnterior).val() != "_null_") {
							
							// Se o valor for diferente de nulo retorna os dados
							if($(ui.item.option).val() != "_null_") {
								
								// Passa o �ndice da linha e o valor do combo
								Interna.retornaGrupoX(indice, $(ui.item.option).val());
								
							}
							
						} else {
							
							// Caso o grupo n�o selecione as a��es corretamente na sequ�ncia,
							// mostrar� uma mensagem para ele n�o pular nenhum campo
							Base.montaMensagemSistema(Array("Seleciona os grupos na sequ�ncia."), 'ATEN��O!', 4);
						}
						
					} else {
						
						// Passa o �ndice da linha e o valor do combo
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
					
					// Passa o �ndice da linha e o valor do combo
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
		// Faz a valida��o do formul�rio
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
		
		// Verifica se a data inicial � maior ou igual a data atual
		// Retorna falso na verifica��o se a data inicial for maior 
		// que a data final(Data Atual)
		if(dtIni == dtAtual || ! Data.verificaIntervaloData(dtIni, dtAtual)) {
			
			$('#form_base').attr({
				onsubmit: 'return true',
				action: baseUrlController + '/excluir',
				target: ''
			}).submit();
			
		} else {
			
			// Mostra a mensagem de erro
			Base.montaMensagemSistema(Array("N�o � poss�vel excluir o grupo de acesso se o per�odo inicial for menor que a data atual."), "ALERTA", 4);
			
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

	// Seleciona um registro, recebendo a chave j� criptografada
	selecionar : function(chave) {
		document.location.href = baseUrlController + "/selecionar/" + chave;
	},

	// Chama a tela de relat�rios
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
		// Instancia as vari�veis de controle
		var mensagem = Array();
		
		// Captura a data atual
		var dtAtual = Data.retornaDataAtual();
		
		// Controla os grupos e per�odos
		var totalLinhasTabela = $("#tabelaCadastro > tbody > tr").size();
		
		// Percorre todos os campos e se possuir valor no campo
		// c�digo verifica se n�o tem outro campo com mesmo valor 
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
					
					// Verifica se as datas s�o v�lidas
					if($(mensagem).size() == 0) {
						
						// Verifica se a data inicial foi preenchida
						if($.trim(dtIni) == "") {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' deve ser preenchida.");
							break;
						}
						
						// Verifica se a data inicial � v�lida
						if(dtIni != "" && ! Data.verificaData(dtIni)) {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' n�o � v�lida.");
							break;
						}
						
						// Verifica se a data final � v�lida
						if(dtFim != "" && ! Data.verificaData(dtFim)) {
							mensagem.push("A data Final do grupo '" + dsGrupo + "' n�o � v�lida.");
							break;
						}
						
						// Verifica se a data inicial � menor que a data final
						if(dtIni != "" && dtFim != "" && (dtIni == dtFim || ! Data.verificaIntervaloData(dtIni, dtFim))) {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' n�o pode ser maior ou igual a data final do mesmo.");
							break;
						}
						
						// Verifica se a data inicial � menor que a data atual
						if(dtIni != "" && ! (dtIni == dtAtual || ! Data.verificaIntervaloData(dtIni, dtAtual))) {
							mensagem.push("A data inicial do grupo '" + dsGrupo + "' n�o pode ser menor que a data atual.");
							break;
						}
					}
					
					// Percorre os outros campos para compara��o
					for(var j=0; j < totalLinhasTabela; j++) {
						
						// Se possuir valor no campo
						var codigo2 = $("#cd_grupo_" + j).val();
						if(codigo2 != "") {
							
							var dtIni2  = $("#dt_acesso_ini_" + j).val();
							var dtFim2  = $("#dt_acesso_fim_" + j).val();
							
							// Se os c�digo forem iguais, verificas as datas
							if(i != j && codigo == codigo2) {
								
								// Verifica se somente um grupo possui data final de acesso
								if($(mensagem).size() == 0) {
									if(dtFim == "" && dtFim2 == "") {
										mensagem.push("O grupo '" + dsGrupo + "' n�o pode ter dois registros sem data final.");
										break;
									}
								}
								
								// Verifica se o grupo sem data final tem a data inicial maior que a dos outros
								// Na compara��o retorna true se a data inicial (parametro 1) � menor que a 
								// data final (parametro 2)
								if($(mensagem).size() == 0) {
									if((dtFim  == "" && ! Data.verificaIntervaloData(dtIni2, dtIni)) || 
									   (dtFim2 == "" && ! Data.verificaIntervaloData(dtIni, dtIni2))) {
										mensagem.push("O grupo '" + dsGrupo + "' que n�o possui data final n�o pode ter a data inicial menor que os demais de mesmo c�digo.");
										break;
									}
								}
								
								// Verifica se as datas iniciais s�o iguais para o mesmo grupo
								if($(mensagem).size() == 0) {
									if(dtIni == dtIni2) {
										mensagem.push("O grupo '" + dsGrupo + "' n�o pode ter duas datas iniciais iguais.");
										break;
									}
								}
								
							}
							
							// Se j� tiver mensagem sai do la�o para mostrar uma a uma
							if($(mensagem).size() == 0) {
								break;
							}
							
						}
						
					}
					
				}
				
			}
			
		}
		
		// Verifica se um grupo ao menos possui valor para inser��o
		if(! totalGruposCadastro) {
			mensagem.push("Ao menos um grupo deve possuir valor.");
		}
				        
        // Chama a valida��o do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;
	},
	
	// Valida os dados da edi��o
	validaSelecionar : function() {
		
		// Instancia as vari�veis de controle
		var mensagem = Array();
		
		// Captura a data atual
		var dtAtual = Data.retornaDataAtual();
		var dtIni   = $("#dt_acesso_ini").val();
		var dtFim   = $("#dt_acesso_fim").val();
		var dsGrupo = $("#no_grupo").val();
				
		// Verifica se a data inicial � v�lida
		if(dtIni != "" && ! Data.verificaData(dtIni)) {
			mensagem.push("A data inicial do grupo '" + dsGrupo + "' n�o � v�lida.");
		}
		
		// Verifica se a data final � v�lida
		if(dtFim != "" && ! Data.verificaData(dtFim)) {
			mensagem.push("A data Final do grupo '" + dsGrupo + "' n�o � v�lida.");
		}
		
		// Verifica se a data inicial � menor que a data final
		if(dtIni != "" && dtFim != "" && (dtIni == dtFim || ! Data.verificaIntervaloData(dtIni, dtFim))) {
			mensagem.push("A data inicial do grupo '" + dsGrupo + "' n�o pode ser maior ou igual a data final do mesmo.");
		}
		
		// Chama a valida��o do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;
		
	},
	
	// Retorna os dados da transa��o passada
	retornaGrupoX : function(indice, cd_grupo) {
		
		// Executa a requisi��o AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-grupo-x/"
		, { 'cd_grupo' : cd_grupo } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Se possuir indice � por que a requisi��o foi feita na tela de novo, 
			// caso contr�rio foi feita na tela de edi��o
			if(indice >= 0) {
				// Seta o valor nos campos
				$('#cd_grupo_'  + indice).val(retorno.CD_GRUPO);
				
			} else {
				$('#cd_grupo').val(retorno.CD_GRUPO);
			}
				
		} );
		
	}
    
};