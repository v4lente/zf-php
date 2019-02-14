//Classe interna, carregada no OnLoad
var Interna = {
		
	// Método principal da classe
	init : function() {
		
		// Aplica o autocomplete nos usuários do sistema
		$("#usuario").autocomplete({
			source: baseUrlController + "/retorna-usuario-x/",
			minLength: 3,
			change: function(event, ui) {
				if (ui.item == undefined) {
					// Limpa os dados
					$("#cd_usuario").val("");
				}
			},
			select: function( event, ui ) {
				
				// Preenche os campos com o retorno do AJAX
				$("#cd_usuario").val(ui.item.id);
			}
		});
		
		
		// Aplica o autocomplete nas TABELAS
		$("#no_tabela").autocomplete({
			source: baseUrlController + "/retorna-tabelas-x/",
			minLength: 3,
			change: function(event, ui) {
				if (ui.item == undefined || ui.item == "") {
					// Limpa os dados
					$("#no_tabela").val("");
					$("#ds_tabela").val("");
				}
			},
			select: function( event, ui ) {
				
				// Preenche os campos com o retorno do AJAX
				$("#no_tabela").val(ui.item.id);
				$("#ds_tabela").val(ui.item.label);
			}
		});
		
		$("#no_tabela").blur(function() {
			if($(this).val() == "") {
				// Limpa os dados
				$("#ds_tabela").val("");
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
		
		// Faz a validação do formulário
		valida = Interna.validaFormulario();

		if (valida[0]) {
				
			$('#form_pesquisar').attr({
				onsubmit: 'return true',
				action: baseUrlController + '/relatorio/_rel_metodo/' + metodo,
				target: '_blank'
			}).submit();
			
		}
	},
	
	// Volta para a listagem
	voltar : function() {},

	// Mostra a ajuda
	ajuda : function() {
		Base.ajuda();
	},

	// Fecha o documento
	sair : function() {
		Base.sair();
	},
	
	//Valida inconsistências e exceções na aplicação.
    validaFormulario : function(){
    	var mensagem   = Array();    	
    	var dtIni      = $("#dthr_operacao_ini_log").val();
    	var dtFim      = $("#dthr_operacao_fim_log").val();
    	
    	// Verifica se os campos data foram preenchidos
    	if ($.trim(dtIni) == "" || $.trim(dtFim) == ""){
    		mensagem.push("Datas: Campo Obrigatório");
    	
    	} else {
    		
    		// Verifica o intervalo de datas é valido
    		if ( Data.verificaIntervaloDataHora(dtIni, dtFim) == false) {
    			mensagem.push("Período inválido: A data inicial deve ser menor que data final.");
    		} 
    		
        }
    	
        // Chava a validação do sistema
        valida = Valida.formulario('form_pesquisar', true, mensagem);
        
        return valida;		
    }
	
};