
//Classe interna, carregada no OnLoad
var Interna = {
	
	init : function() {
		
		// Aqui será adicionado as funções
		// que manipularão o PickList		
		$("#form_picklist input[type='text']").keypress(function(e){				
			if (e.which == 13) {
				Interna.pesquisar();
			}			
		});
			
		
	},
	
	// Executa a tela de pesquisa
	pesquisar : function() {			
		document.forms[0].onSubmit = "return true;";
		document.forms[0].submit();		
	},
	
	// Volta para a tela anterior
	voltar : function() {
		
		// Fecha a janela corrente
		window.close();
		
		// Seta o foco na janela pai
		window.opener.focus();
		
	},
	
	// Mostra a ajuda
	ajuda : function() {
		Base.ajuda();
	},
	
	// Fecha o documento
	sair : function() {
		Base.sair();
	},
	
	retornoPickList: function(references){		
		window.opener.focus();
		window.opener.Base.retornoPickList(references);
		window.close();
    }   
    
};