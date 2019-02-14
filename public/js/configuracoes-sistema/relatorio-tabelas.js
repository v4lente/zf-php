//Classe interna, carregada no OnLoad
var Interna = {
		
	// Método principal da classe
	init : function() {
		
		// Aplica o autocomplete no campo do CÓDIGO da TABELA
		$("#cd_tabela").autocomplete({
			source: baseUrlController + "/auto-complete-x/campo/cd_tabela",
			minLength: 1,
			select: function( event, ui ) {				
				// Preenche os campos com o retorno do AJAX
				$("#no_tabela").val(ui.item.descricao);				
			}
		});
		
		// Aplica o autocomplete no campo do NOME da TABELA
		$("#no_tabela").autocomplete({
			source: baseUrlController + "/auto-complete-x/campo/no_tabela/",			
			minLength: 1,
			select: function( event, ui ) {			
				// Preenche os campos com o retorno do AJAX
				$("#cd_tabela").val(ui.item.codigo).toUpperCase();
			}
		});
		
		// Aplica o autocomplete no campo do CÓDIGO do sistema
		$("#cd_sistema").autocomplete({
			source: baseUrlController + "/auto-complete-x/campo/cd_sistema",
			minLength: 1,
			select: function( event, ui ) {				
				// Preenche os campos com o retorno do AJAX
				$("#no_sistema").val(ui.item.descricao);				
			}
		});
		
		// Aplica o autocomplete no campo do NOME do sistema
		$("#no_sistema").autocomplete({
			source: baseUrlController + "/auto-complete-x/campo/no_sistema/",			
			minLength: 1,
			select: function( event, ui ) {			
				// Preenche os campos com o retorno do AJAX
				$("#cd_sistema").val(ui.item.codigo).toUpperCase();
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

    	
    	if ($.trim($("#cd_tabela").val()) == "" && $.trim($("#cd_sistema").val()) == "") {
    		mensagem.push("Preencha um dos campos");
    	}
    	
        // Chava a validação do sistema
        valida = Valida.formulario('form_pesquisar', true, mensagem);
        
        return valida;	
    }
	
};