//Classe interna, carregada no OnLoad
var Interna = {
	
	// Método principal da classe
	init : function() {
		
		// Quando a action for NOVO
		if (no_action == 'novo') {
			
			// No evento blur do campo
			$("#cd_tabela").blur(function(){
				
				if (Interna.cd_tabela != $(this).val()) {					
					
					// Seta a variavel
					Interna.cd_tabela = $(this).val();
					
					// Verifica se existe o codigo da tabela informado
					if (Interna.retornaDadosX($(this)) != "") {
						
						// Limpa o campo
						$(this).val("");
						
						// Mosta mensagem de validação
						Base.montaMensagemSistema(Array("Tabela informada já existente"), "Atenção", 3, "$('#cd_tabela').focus();");
					}					
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
		valida = Interna.validaFormulario();
		
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
	
	// Retorna o registro da tabela_r referente ao código da tabela informado
    retornaDadosX : function(cd_tabela) {
		
	    var retorno = null;
	    
	    // Retorna o registro da tabela_r referente ao código da tabela informado
	    $.ajax({
            type    : "GET",            
            async   : false,
            url     : baseUrlController + "/retorna-dados-x/cd_tabela/"+ $(cd_tabela).val(),
            success : function(data) {
            	retorno = data;         	
            }
	    });
	    
        return retorno;
    	
	},
	
	//Valida inconsistências e exceções na aplicação.
    validaFormulario : function(){
    	
    	var mensagem   = Array();    	
    	
    	// Chava a validação do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;		
    }
  
};