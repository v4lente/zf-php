//Classe interna, carregada no OnLoad
var Interna = {

	// Seta alguns atributos da classe
	cd_sistema 	  : "",
	ord_transacao : "",
		
	// Método principal da classe
	init : function() {

		// Aplica as funções ajax somente para estas ações
		if(no_action == "novo" || no_action == "salvar" || no_action == "selecionar") {
			
			// Guarda o valor do cd_sistema toda vez ao setar o foco
			/*$("#cd_sistema_ac").focus(function(event) {
				Interna.cd_sistema = $("#cd_sistema").val();
			});*/
			
			
			// Gerencia o campo cd_sistema
			$("#cd_sistema_ac").bind("autocompleteselect", function(event, ui) {
				var valorAtualCdSistema = $(ui.item.option).val();
				
				if(valorAtualCdSistema == "" || valorAtualCdSistema == "_null_") {
					Form.montaCombo(("#cd_menu"),"","");
					Form.atualizaCombo(("#cd_menu"));
					$("#cd_menu_ac").attr("readonly", "readonly");
				}
				else if($("#cd_sistema").val() != $(ui.item.option).val()) {
					Interna.retornaMenuSistemaX(valorAtualCdSistema);
				}
				$("#ord_transacao").val('');
				
			});
			
			// Gerencia o campo ord_transacao
			$("#ord_transacao").blur(function(event) {
				var valorAtualOrdTransacao = $(this).val();
				if(Interna.ord_transacao != valorAtualOrdTransacao) {
					if(valorAtualOrdTransacao != "") {
						var cd_menu = $("#cd_menu").val();
						Interna.verificaOrdemExibicaoX(cd_menu, valorAtualOrdTransacao);
					}
				}
			});
		}
        
	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	initSalvarAction : function () {
		
		if ( $('#possuiTabsVinculadas').val() == 1 )  {
            
			try {
				$.ajax({
					type : "GET",
					dataType : "json",
                    url  : baseUrl + '/default/jasper/excluir/',
					data : {'nomeRelatorio'	: $('#nome').val(), 
							'sistema' 		: $('#sistema').val(),
							'ambiente' 		: $('#ambiente').val()},
					success: function(obj){
						
						$.ajax({
							type : "GET",
							dataType : "json",
							url  : baseUrl + '/default/jasper/enviar/',
							data : {  'cdConsulta'		: $('#cd_consulta').val(), 
									  'nomeRelatorio'	: $('#nome').val(), 
									  'titulo' 			: $('#titulo').val(),
									  'descricao' 		: $('#descricao').val(),
									  'sistema' 		: $('#sistema').val(), 
									  'ambiente' 		: $('#ambiente').val()
                            },
							success: function(obj2){
								// Limpa o campo com o nome do menu
								//alert ("Enviou!");
							},
							error: function(o) {
								alert('Erro: ' + o);
							}
						});
						
					},
					error: function(o) {
						alert('Erro: ' + o);
					}
				});
				
			} catch(e) {
				alert(e);
			}
		}
		
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
    
    salvarY : function() {
        
        $("#salvar_silencioso").val("1");
        
        $('#form_base').attr({
            onsubmit: 'return true',
            action: baseUrlController + '/salvar',
            target: ''
        }).submit();
        
    },

	// Excluir o registro
	excluir : function() {
		
		try {
			$.ajax({
				type : "GET",
				dataType : "json",
				url  : baseUrl + '/default/jasper/excluir/',
				data : { 'nomeRelatorio' : $('#nome').val(), 
						 'sistema' 		 : $('#sistema').val(),
						 'ambiente' 	 : $('#ambiente').val()
                },  // Seta o parametro a ser passado por ajax
				success: function(obj){
					
					$('#form_base').attr({
						onsubmit: 'return true',
						action: baseUrlController + '/excluir',
						target: ''
					}).submit();
					
				},
				error: function(o) {
					alert('Erro: ' + o);
				}
			});
			
		} catch(e) {
			alert(e);
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
            if($.trim($(e).val()) != '') {
                existe = true;  
            }
        });
       
        return existe;
        
    },
    
    // Retorna os menus dos sistemas
	retornaMenuSistemaX : function(cd_sistema) {
		
		if(cd_sistema != "") {
			// Executa a requisição AJAX com retorno JSON
			$.getJSON(baseUrlController + "/retorna-menu-sistema-x/"
			, { 'cd_sistema' : cd_sistema } // Seta o parametro a ser passado por ajax	
			, function (retorno){ // Pega o retorno 
				
				// Limpa o campo com o nome do menu
				$('#cd_menu_ac').val("");
		/*	
				// Recupera os menus do sistema selecionado
				var options = "<option label=' -- Selecione -- ' value=' '> -- Selecione -- </option>";
				$(retorno).each(function(i, v) {
					options = options + "<option value='" + v.CD_MENU + "'>" + v.NO_MENU + "</option>";
				});
				
				// Seta os options no menu dependendo do sistema selecionado
				$('#cd_menu').html(options);
		*/
				// Combo, objeto de retorno, campos de código e nome
				Form.montaCombo($("#cd_menu"), retorno, Array("CD_MENU", "NO_MENU"));
					
				// Remove o atributo readonly do campo
				$('#cd_menu_ac').removeAttr("readonly");
				
			} );
			
		}
	},
	
	// Verifica se a ordem de exibição para o menu selecionado já existe
	verificaOrdemExibicaoX : function(cd_menu, ord_transacao) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/verifica-ordem-exibicao-x/"
		, { 'cd_menu' : cd_menu, 'ord_transacao' : ord_transacao } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor e se caso sim, mostra a mensagem na tela
			if(retorno.CD_TRANSACAO != undefined && retorno.CD_TRANSACAO != "") {
				
				var no_transacao = $("#no_transacao").val(); 
				var mensagem     = "A transação " + no_transacao + " já encontra-se vinculada ao valor ";
				mensagem        += "informado como ordem de exibição, informe outro valor para ordenação.";
				
				Base.montaMensagemSistema(Array(mensagem), "Valor já existente", 4);
			}
			
		} );
		
	}
    
};