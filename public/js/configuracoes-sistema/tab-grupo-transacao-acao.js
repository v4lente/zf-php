//Classe interna, carregada no OnLoad
var Interna = {

	// Seta alguns atributos da classe
	ord_acao : "",
		
	// Método principal da classe
	init : function() { },
	
	initNovoAction : function () {
		
		// Controla os campos data de inicio e fim
		$(".dt_acesso_ini, .dt_acesso_fim").change(function() {
			//
			var objTr    	  = $(this).closest('tr').get(0); 
			var indice  	  = $(objTr).attr("indice");
			var dt_acesso_ini = $('#dt_acesso_ini_' + indice).val(); 
			var dt_acesso_fim = $('#dt_acesso_fim_' + indice).val();
	
			
			Form.atualizaCombo(('#cd_acao_' + indice));
			$("#_todas_acoes_" + indice).attr("checked", false);
			Form.habilitaCamposById('cd_acao_' + indice + '_ac');
			
			// Caso seja informado o checkbox de todas as ações
			if ($("#_todas_acoes_" + indice).attr("checked")) {				
				// Verifica se tem algum registro informado para o período
				Interna.verificaAcoesX(indice, cd_transacao, 0, dt_acesso_ini, dt_acesso_fim);					
			}
			
		});
        
		/*
		$(".ui-autocomplete-input").bind("autocompleteselect", function(event, ui) {
			
			// Captura a linha do campo
			var objTr    	  = $(this).closest('tr').get(0); 
			var indice  	  = $(objTr).attr("indice");
			
			// Pega os valores
			var fl_permissao  = $('#fl_permissao_'  + indice).val();
			var cd_transacao  = $('#cd_transacao_'  + indice).val();
			var dt_acesso_ini = $('#dt_acesso_ini_' + indice).val();
			var dt_acesso_fim = $('#dt_acesso_fim_' + indice).val();
			
			// Captura o id do combo
			var idAC     	= $(this).attr("id");						
			var valorCombo  = $.trim($(ui.item.option).val());			
            			
		});
		*/
        
		// Se um código for selecionado faz todo o processo
		// de seleção dos campos automaticamente
		$(".i__cd_transacao").blur(function() {
			
			var objTr  = $(this).closest('tr').get(0); 
			var indice = $(objTr).attr("indice");
			var valor  = $(this).val();
			
			// Passa o índice da linha e o valor do compo
			// e retorna todos os sistemas, menus e transações 
			// já selecionadas conforme o código da transação
			if(valor != "" && valor != " -- Selecione -- ") {
                Interna.retornaDadosX(indice, valor);
			}
			
		});
        
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
				
				// Limpa a linha caso seja descelecionada
				if(idAC == ("cd_sistema_" + indice + "_ac")) {
					// Volta com o valor inicial selecionado
					Form.atualizaCombo(('#cd_menu_'      + indice));
					Form.atualizaCombo(('#cd_transacao_' + indice));
					
					// Desabilita o campo
					$("#cd_menu_" + indice).attr("readonly", "readonly");
					$("#cd_menu_" + indice + "_ac").attr("readonly", "readonly");
					$("#cd_transacao_" + indice).attr("readonly", "readonly");
					$("#cd_transacao_" + indice + "_ac").attr("readonly", "readonly");
				}
				
				if(idAC == ("cd_menu_" + indice + "_ac")) {
					Form.atualizaCombo(('#cd_transacao_' + indice));
					Form.atualizaCombo(('#fl_permissao_' + indice));
					
					// Desabilita o campo
					$("#cd_transacao_" + indice).attr("readonly", "readonly");
					$("#cd_transacao_" + indice + "_ac").attr("readonly", "readonly");

				}
				
				if(idAC == ("cd_transacao_" + indice + "_ac")) {
					Form.atualizaCombo(('#fl_permissao_' + indice));
                }
                
                $('#_cd_transacao_' + indice).val("");
				
			} else {
				
				// Entra na condição se for escolhido o sistema
				if($("#" + idSelect).hasClass("i_cd_sistema")) {
					
                    Form.atualizaCombo(('#cd_transacao_' + indice));
					Form.atualizaCombo(('#fl_permissao_' + indice));
                    
                    $('#_cd_transacao_' + indice).val("");
                    
                    $("#cd_transacao_" + indice).attr("readonly", "readonly");
					$("#cd_transacao_" + indice + "_ac").attr("readonly", "readonly");
                    
					// Passa o índice da linha e o valor do combo
					Interna.retornaMenusX(indice, $(ui.item.option).val());
					
				}
				
				// Entra na condição se for escolhido o menu
				if($("#" + idSelect).hasClass("i_cd_menu")) {
                    
                    $('#_cd_transacao_' + indice).val("");
                    					
					// Passa o índice da linha e o valor do combo
					Interna.retornaTransacoesX(indice, $(ui.item.option).val());
					
				}
                
                // Entra na condição se for escolhido a transacao
				if($("#" + idSelect).hasClass("i_cd_transacao")) {
					
					// Passa o índice da linha o valor da transacao
                    $("#_cd_transacao_" + indice).val($(ui.item.option).val());
					
				}
				
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
			Base.montaMensagemSistema(Array("Não é possível excluir a transação se o período inicial for menor que a data atual."), "ALERTA", 4);
			
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

    //Verfica se há algum conteudo digitado em algum campo para poder realizar a busca.
    verificaBusca : function(){
        var existe = false;
       
        $("form :input[type!='button']").each(function(i, e){
            if($.trim($(e).val()) != ''){    
                existe = true;  
            }
        });
       
        return existe;
        
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
		var totalTransacaoCadastro = false;
		for(var i=0; i < totalLinhasTabela; i++) {
			
			// Se possuir valor no campo
			var codigo  = $("#_cd_transacao_" + i).val();			
			if(codigo != "") {
				
				// Ao menos um grupo deve possuir valor
				totalTransacaoCadastro = true;
				
				var dtIni        = $("#dt_acesso_ini_" + i).val();
				var dtFim        = $("#dt_acesso_fim_" + i).val();
				var dsTransacao  = $("#cd_transacao_"  + i + "_ac").val();
				var fl_permissao = $("#fl_permissao_"  + i).val();
				
				if(codigo != "") {
					
					// Verifica se as datas são válidas
					if($(mensagem).size() == 0) {
						
						// Verifica se tem ação vinculada
						if (fl_permissao == "_null_") {
							mensagem.push("A Tranção '" + dsTransacao + "' não tem Permissão vinculada.");
							break;
						}
						
						// Verifica se a data inicial foi preenchida
						if($.trim(dtIni) == "") {
							mensagem.push("A data inicial da transação '" + dsTransacao + "' deve ser preenchida.");
							break;
						}
						
						// Verifica se a data inicial é válida
						if(dtIni != "" && ! Data.verificaData(dtIni)) {
							mensagem.push("A data inicial da transação '" + dsTransacao + "' não é válida.");
							break;
						}
						
						// Verifica se a data final é válida
						if(dtFim != "" && ! Data.verificaData(dtFim)) {
							mensagem.push("A data Final da transação '" + dsTransacao + "' não é válida.");
							break;
						}
						
						// Verifica se a data inicial é menor que a data final
						if(dtIni != "" && dtFim != "" && (dtIni == dtFim || ! Data.verificaIntervaloData(dtIni, dtFim))) {
							mensagem.push("A data inicial da transação '" + dsTransacao + "' não pode ser maior ou igual a data final do mesmo.");
							break;
						}
						
						// Verifica se a data inicial é menor que a data atual
						if(dtIni != "" && ! (dtIni == dtAtual || ! Data.verificaIntervaloData(dtIni, dtAtual))) {
							mensagem.push("A data inicial da transação '" + dsTransacao + "' não pode ser menor que a data atual.");
							break;
						}
					}
										
					// Percorre os outros campos para comparação
					for(var j=0; j < totalLinhasTabela; j++) {
						
						// Se possuir valor no campo
						var codigo2 = $("#_cd_transacao_" + j).val();
						
						if(codigo2 != "") {
							
							var dtIni2 = $("#dt_acesso_ini_" + j).val();
							var dtFim2 = $("#dt_acesso_fim_" + j).val();
							
							if(i != j && codigo == codigo2) {
								
								// Verifica se somente um grupo possui data final de acesso
								if($(mensagem).size() == 0) {
									if(dtFim == "" && dtFim2 == "") {
										mensagem.push("A Tranção '" + dsTransacao + "' não pode ter dois registros sem data final.");
										break;
									}
								}
								
								// Verifica se o grupo sem data final tem a data inicial maior que a dos outros
								// Na comparação retorna true se a data inicial (parametro 1) é menor que a 
								// data final (parametro 2)
								if($(mensagem).size() == 0) {
									if((dtFim  == "" && ! Data.verificaIntervaloData(dtIni2, dtIni)) || 
									   (dtFim2 == "" && ! Data.verificaIntervaloData(dtIni, dtIni2))) {
										mensagem.push("A Tranção '" + dsTransacao + "' que não possui data final não pode ter a data inicial menor que os demais de mesmo código.");
										break;
									}
								}
								
								// Verifica se as datas iniciais são iguais para o mesmo grupo
								if($(mensagem).size() == 0) {
									if(dtIni == dtIni2) {
										mensagem.push("A Tranção '" + dsTransacao + "' não pode ter duas datas iniciais iguais.");
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
		if(! totalTransacaoCadastro) {
			mensagem.push("Ao menos uma transação deve possuir valor.");
		}
				        
        // Chama a validação do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;
	},
    
	// Valida os dados da edição
	validaSelecionar : function() {
		
		// Instancia as variáveis de controle
		var mensagem    = Array();

		// Captura a data atual
		var dtAtual     = Data.retornaDataAtual();
		var dtIni       = $("#dt_acesso_ini").val();
		var dtFim       = $("#dt_acesso_fim").val();
		var dsTransacao = $("#no_transacao").val();
				
		// Verifica se a data inicial é válida
		if(dtIni != "" && ! Data.verificaData(dtIni)) {
			mensagem.push("A data inicial da transação '" + dsTransacao + "' não é válida.");
		}
		
		// Verifica se a data final é válida
		if(dtFim != "" && ! Data.verificaData(dtFim)) {
			mensagem.push("A data Final da transação '" + dsTransacao + "' não é válida.");
		}
		
		// Verifica se a data inicial é menor que a data final
		if(dtIni != "" && dtFim != "" && (dtIni == dtFim || ! Data.verificaIntervaloData(dtIni, dtFim))) {
			mensagem.push("A data inicial da transação '" + dsTransacao + "' não pode ser maior ou igual a data final do mesmo.");
		}

		// Verifica se a data final é menor que a data atual
		if(dtFim != "" && ( ! Data.verificaIntervaloData(dtAtual, dtFim))) {
			mensagem.push("A data final da transação '" + dsTransacao + "' não pode ser menor que a data atual.");
		}
		
		// Chama a validação do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;

	},
	
	// Retorna os menus ligados ao sistema selecionado
	retornaMenusX : function(indice, cd_sistema) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-menus-x/"
		, { 'cd_sistema' : cd_sistema } // Seta o parametro a ser passado por ajax 	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor
			if(retorno[0].CD_MENU != undefined && retorno[0].CD_MENU != "") {
				
				// Habilita o campo
				$("#cd_menu_" + indice).removeAttr("readonly");
				$("#cd_menu_" + indice + "_ac").removeAttr("readonly");
				
				// Combo, objeto de retorno, campos de código e descrição
				Form.montaCombo($("#cd_menu_" + indice), retorno, Array("CD_MENU", "NO_MENU"));
				
				// Volta com o valor inicial selecionado
				Form.atualizaCombo(('#cd_menu_' + indice));
				
			}
			
		} );
		
	},
	
	
	// Retorna as transações ligadas ao menu
	retornaTransacoesX : function(indice, cd_menu) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-transacoes-x/"
		, { 'cd_menu' : cd_menu } // Seta o parametro a ser passado por ajax 	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor
			if(retorno[0] != undefined && retorno[0].CD_TRANSACAO != "") {
				
				// Habilita o campo
				$("#cd_transacao_" + indice).removeAttr("readonly");
				$("#cd_transacao_" + indice + "_ac").removeAttr("readonly");
				
				// Combo, objeto de retorno, campos de código e descrição
				Form.montaCombo($("#cd_transacao_" + indice), retorno, Array("CD_TRANSACAO", "NO_TRANSACAO"));
				
				// Volta com o valor inicial selecionado
				Form.atualizaCombo(('#cd_transacao_' + indice));
				
			}
			
		} );
		
	},
	
	// Retorna os dados da transação passada
	retornaDadosX : function(indice, cd_transacao) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-dados-x/"
		, { 'cd_transacao' : cd_transacao } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor e joga na linha da tabela
			if(retorno[2][0] != undefined && retorno[2][0] != "") {
				
                // Seta o foco no último campo
                $('#fl_permissao_' + indice + "_ac").focus();
                
                window.setTimeout(function() {
                    
                    // ---------------------------------------------------------------------------------
                    // Ativa o campo
                    $('#cd_sistema_' + indice + "_ac").removeAttr("readonly");

                    // Não precisa recuperar os sistemas pois eles já vem pré-selecionados
                    // Volta com o valor inicial selecionado
                    Form.atualizaCombo($('#cd_sistema_' + indice), retorno[0][0]);

                    // ---------------------------------------------------------------------------------
                    // Ativa o campo
                    $('#cd_menu_' + indice + "_ac").removeAttr("readonly");

                    // Recupera os menus
                    // Combo, objeto de retorno, campos de código e descrição
                    Form.montaCombo($("#cd_menu_" + indice), retorno[1][1], Array("CD_MENU", "NO_MENU"));

                    // Volta com o valor inicial selecionado
                    Form.atualizaCombo($('#cd_menu_' + indice), retorno[1][0]);

                    // ---------------------------------------------------------------------------------
                    // Ativa o campo
                    $('#cd_transacao_' + indice + "_ac").removeAttr("readonly");

                    // Recupera as transações
                    // Combo, objeto de retorno, campos de código e descrição
                    Form.montaCombo($("#cd_transacao_" + indice), retorno[2][1], Array("CD_TRANSACAO", "NO_TRANSACAO"));

                    // Volta com o valor inicial selecionado
                    Form.atualizaCombo($('#cd_transacao_' + indice), retorno[2][0]);

                }, 400);
                
			}
			
		} );
		
	}
    
};