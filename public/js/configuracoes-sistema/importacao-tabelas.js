//Classe interna, carregada no OnLoad
var Interna = {
		
	// Método principal da classe
	init : function() {
		Base.escondeTelaCarregando();
		
		// Checkbox de controle para marcar ou desmarcar todos os itens da tela
		$("#controle").click(function() {
			
			if($(this).is(":checked")) {
				$(":input:checkbox").each(function() {
					$(this).attr("checked", true);
				});
				
			} else {
				$(":input:checkbox").each(function() {
					$(this).attr("checked", false);
				});
			}
			
		});
		
		// Checkbox de controle para os campos dependentes
		$(".marca").click(function() {
			
			var ehTabela = $(this).attr("eh-tabela");
			var ehColuna = $(this).attr("eh-coluna");
			var ehCampo  = $(this).attr("eh-campo");
			var tabela   = $(this).attr("tabela");
			var coluna   = $(this).attr("coluna");
			
			if($(this).is(":checked")) {
				
				if(ehTabela == 1) {
					$('.marca[tabela="'+tabela+'"]').each(function() {
						$(this).attr("checked", true);
					});
					
				} else if(ehColuna == 1) {
					$('.marca[tabela="'+tabela+'"][eh-tabela="1"]').each(function() {
						$(this).attr("checked", true);
					});
					
					$('.marca[coluna="'+coluna+'"]').each(function() {
						$(this).attr("checked", true);
					});
					
				} else if(ehCampo == 1) {
					$('.marca[tabela="'+tabela+'"]').each(function() {
						if($(this).attr("eh-tabela") == 1) {
							$(this).attr("checked", true);
						}
						
						if($(this).attr("eh-coluna") == 1 && $(this).attr("coluna") == coluna) {
							$(this).attr("checked", true);
						}
					});
				}
				
			} else {
				
				if(ehTabela == 1) {
					$('.marca[tabela="'+tabela+'"]').each(function() {
						$(this).attr("checked", false);
					});
					
				} else if(ehColuna == 1) {
					$('.marca[coluna="'+coluna+'"]').each(function() {
						$(this).attr("checked", false);
					});
					
				}
			}
			
		});
		
		// Zebra a tabela
		$('#tabela-importacao tr:odd').addClass('par');
				
	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	// Salva o registro
	salvar : function() {
		
		// Cria os campos hidden para o envio dos checkbox.
		var input0 = "<input type='hidden' name='marca[]' value='0' />";
		var i      = 0;
		$(".marca").each(function() {
			if(! $(this).is(':checked')) {
				$(this).after(input0);
				i += 1;
			}
		});
		
		// Salva os dados
		$('#form_base').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/salvar',
			target: ''
		}).submit();
		
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
	}  
	
};