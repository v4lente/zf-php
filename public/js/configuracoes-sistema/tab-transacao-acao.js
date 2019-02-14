//Classe interna, carregada no OnLoad
var Interna = {

	// Seta alguns atributos da classe
	ord_acao : "",
		
	// Método principal da classe
	init : function() {
        
		// Aplica as funções ajax somente para estas ações
		if(no_action == "novo") {
			
			$(".ui-autocomplete-input").bind("autocompleteselect", function(event, ui) {
				// Captura a linha do campo
				var objTr  = $(this).closest('tr').get(0); 
				var indice = $(objTr).attr("indice");
				
				// Se for descelecionado o combo limpa os campos
				if($(ui.item.option).val() == "_null_") {
					
					for(i=indice; i < 10; i++) {
						$("#cd_acao_" 	   + i).val("");
						$("#ds_acao_" 	   + i).val("");
						$("#lnk_img_acao_" + i).val("");
						$("#fl_menu_" 	   + i).val("");
						$("#no_acao_" 	   + i + "_ac").val(" -- Selecione -- ");
					}
					
				} else {
				
					if(indice > 0) {
						
						// Captura o indice anterior
						var indiceAnterior = indice - 1;
						
						// Verifica se o código tem valor
						if($("#cd_acao_" + indiceAnterior).val() != "") {
							
							// Se o valor for diferente de nulo retorna os dados
							if($(ui.item.option).val() != "_null_") {
								
								// Verifica se já não foi selecionado em outro campo o mesmo valor
								var selecionou = false;
								
								// Captura o valor do campo
								var valor  = $(ui.item.option).html();
								
								// Percorre todos os campos anteriores ao selecionado
								// para ver se o campo já não foi escolhido
								for(i=indiceAnterior; i >= 0; i--) {
									var valor2 = $("#no_acao_" + i + "_ac").val();
									if($.trim(valor) == $.trim(valor2)) {
										selecionou = true;
									}
								}
								
								// Se não foi selecionado em nenhum outro campo retorna os dados
								if(! selecionou) {
									// Passa o índice da linha e o valor do combo
									Interna.retornaAcoesX(indice, $(ui.item.option).val());
									
								} else {
									// Desceleciona o nome da ação
									var tempo = setTimeout("Interna.trocaValorCombo('" + indice + "');", 500);
									
									// Caso o usuário não selecione as ações corretamente na sequência,
									// mostrará uma mensagem para ele não pular nenhum campo
									Base.montaMensagemSistema(Array("A ação '" + valor + "' já encontra-se vinculada a essa transação ou preenchida nesta tela."), 'ATENÇÃO!', 4);
								}
							}
							
						} else {
							
							// Desceleciona o nome da ação
							var tempo = setTimeout("Interna.trocaValorCombo('" + indice + "');", 500);
														
							// Caso o usuário não selecione as ações corretamente na sequência,
							// mostrará uma mensagem para ele não pular nenhum campo
							Base.montaMensagemSistema(Array("Seleciona as ações na sequência."), 'ATENÇÃO!', 4);
						}
						
					} else {
						
						// Se o valor for diferente de nulo retorna os dados
						if($(ui.item.option).val() != "_null_") {
							// Passa o índice da linha e o valor do combo
							Interna.retornaAcoesX(indice, $(ui.item.option).val());
						}
					}
				}
			});
			
		}
	
		// Aplica as funções ajax somente para estas ações
		if(no_action == "salvar" || no_action == "selecionar") {
			
			// Controla o combobox da ação
			$("#cd_acao_ac").bind("autocompleteselect", function(event, ui) {
				
				// Se for descelecionado o combo limpa os campos
				if($(ui.item.option).val() != "_null_" && 
				   $(ui.item.option).val() != $("#_cd_acao").val()) {
					
					// Retorna os dados da ação selecionada
					Interna.retornaAcoes2X($(ui.item.option).val());
				}
				
			});
			
			// Gerencia o campo ord_transacao
			$("#ord_acao").blur(function(event) {
				var valorAtualOrdAcao = $(this).val();
				// Verifica se o valor atual é o mesmo que está na memória da classe
				if(Interna.ord_acao != valorAtualOrdAcao && 
				   valorAtualOrdAcao != "" && 
				   valorAtualOrdAcao != "0") {
					
					if(valorAtualOrdAcao != "") {
						var cd_transacao = $("#cd_transacao").val();
						Interna.verificaOrdemExibicaoX(cd_transacao, valorAtualOrdAcao);
					}
					
				} else {
					
					// Se estiver setado algum campo inconsistente, remove-os da validação
					Form.removeCampoInconsistente($("#ord_acao"));
				}
			});
		}
		
	},
	
	// Altera o valor do combo para o padrão
	trocaValorCombo : function(indice) {
		// Desceleciona o nome da ação
		$("#no_acao_" + indice).val("_null_");
		$("#no_acao_" + indice + "_ac").val(" -- Selecione -- ");
	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	// Salva o registro
	salvar : function() {
        // Faz a validação do formulário
		valida = Valida.formulario('form_base', true);

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
    
    // Retorna os menus dos sistemas
	retornaMenuSistemaX : function(sel_cd_sistema) {
		if(sel_cd_sistema != "") {
			// Executa a requisição AJAX com retorno JSON
			$.getJSON(baseUrlController + "/retorna-menu-sistema-x/"
			, { cd_sistema : sel_cd_sistema } // Seta o parametro a ser passado por ajax	
			, function (retorno){ // Pega o retorno 
					// Limpa o campo com o nome do menu
					$('#cd_menu_ac').val("");
				
					// Recupera os menus do sistema selecionado
					var options = "";
					$(retorno).each(function(i, v) {
						options = options + "<option value='" + v.CD_MENU + "'>" + v.NO_MENU + "</option>";
					});
					
					// Seta os options no menu dependendo do sistema selecionado
					$('#cd_menu').html(options);
			} );
			
		}
	},
	
	// Verifica se a ordem de exibição da ação selecionada já existe
	verificaOrdemExibicaoX : function(cd_transacao, ord_acao) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/verifica-ordem-exibicao-x/"
		, { 'cd_transacao' : cd_transacao, 'ord_acao' : ord_acao } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor e se caso sim, mostra a mensagem na tela
			if(retorno.CD_TRANSACAO != undefined && retorno.CD_TRANSACAO != "") {
				
				var no_acao   = $("#cd_acao_ac").val(); 
				var mensagem  = "A transação selecionada, já possui a ação '" + no_acao + "' vinculada ";
				mensagem     += "ao valor informado como ordem de exibição, informe outro valor para ordenação.";
				
				Base.montaMensagemSistema(Array(mensagem), "Valor já existente", 4);
				
				// Passa para  validação os campos inconsistentes
				Form.adicionaCampoInconsistente($("#ord_acao"));
				
			} else {
				
				// Se estiver setado algum campo inconsistente, remove-os da validação
				Form.removeCampoInconsistente($("#ord_acao"));
			}
			
		} );
		
	},
	
	
	// Retorna os dados das ações
	retornaAcoesX : function(indiceLinha,  cd_acao) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-acoes-x/"
		, { 'cd_acao' : cd_acao } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor e joga na linha da tabela
			if(retorno.CD_ACAO != undefined && retorno.CD_ACAO != "") {
				$("#cd_acao_" 	   + indiceLinha).val(retorno.CD_ACAO);
				$("#ds_acao_" 	   + indiceLinha).val(retorno.DS_ACAO);
				$("#lnk_img_acao_" + indiceLinha).val(retorno.LNK_IMG_ACAO);
				var fl_menu_acao = "Não";
				if(retorno.FL_MENU_ACAO == "1") {
					fl_menu_acao = "Sim";
				}
				$("#fl_menu_" 	   + indiceLinha).val(fl_menu_acao);
			}
			
		} );
		
	},
	
	
	// Retorna os dados da ação selecionada
	retornaAcoes2X : function(cd_acao) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-acoes-x/"
		, { 'cd_acao' : cd_acao } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor e joga na linha da tabela
			if(retorno.CD_ACAO != undefined && retorno.CD_ACAO != "") {
				
				// Verifica se a ação selecionada é diferente da escolhida, 
				// e caso seja, esconde o botão excluir
				if(Interna.cd_acao != retorno.CD_ACAO) {
					$("#botao-excluir").css("display", "none");
				} else {
					$("#botao-excluir").css("display", "block");
				}
				
				$("#__cd_acao").val(retorno.CD_ACAO);
				$("#ds_acao").val(retorno.DS_ACAO);
				var fl_menu_acao = "Não";
				if(retorno.FL_MENU_ACAO == "1") {
					fl_menu_acao = "Sim";
				}
				$("#fl_menu").val(retorno.FL_MENU_ACAO);
				$("#_fl_menu").val(fl_menu_acao);
			}
			
		} );
		
	}
    
};