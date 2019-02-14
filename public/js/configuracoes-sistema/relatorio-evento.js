//Classe interna, carregada no OnLoad
var Interna = {
		
	// M�todo principal da classe
	init : function() {
		
		// Aplica o autocomplete no NOME do MOTORISTA			 
		$("#usuario").autocomplete({
			source: baseUrlController + "/auto-complete-x/",
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

	// Seleciona um registro, recebendo a chave j� criptografada
	selecionar : function(chave) {
		
	},

	// Chama a tela de relat�rios
	relatorio : function(metodo){
		
		// Faz a valida��o do formul�rio
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
	
	//Valida inconsist�ncias e exce��es na aplica��o.
    validaFormulario : function(){
    	var mensagem   = Array();    	
    	var dtIni      = $("#dthr_acesso_ini").val();
    	var dtFim      = $("#dthr_acesso_fim").val();
    	
    	// Verifica se os campos data foram preenchidos
    	if ($.trim(dtIni) == "" || $.trim(dtFim) == ""){
    		mensagem.push("Datas: Campo Obrigat�rio");
    	
    	} else {
    		
    		// Verifica o intervalo de datas � valido	
    		if ( Data.verificaIntervaloData(dtIni, dtFim) == false) {    	
    			mensagem.push("Per�odo inv�lido: A data inicial deve ser menor que data final.");
    		} 
    		
        }
    	
        // Chava a valida��o do sistema
        valida = Valida.formulario('form_pesquisar', true, mensagem);
        
        return valida;		
    }
	
};