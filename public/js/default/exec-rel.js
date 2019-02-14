
//Classe interna, carregada no OnLoad
var Interna = {
	
	init : function() {
		
		// Joga no primeiro campo da busca o foco
		$("#cd_relatorio").focus();
		
	},
	
	voltar : function() {
		
		document.location.href = paginaAnterior;
		
	},
	
	pesquisar : function() {
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/pesquisar',
			target: ''
		}).submit();
	},
	
	selecionar : function(chave) {
		
		document.location.href = baseUrlController + "/selecionar/" + chave;
		
	},
	
	// Chama a tela de relatórios
	relatorio : function(metodo){
		if(metodo == "pdf") {
			Interna.relPDF();
		}
		else if(metodo == "xls") {
			Interna.relXSL();
		} else {
			Interna.relHTML();
		}
	},
	
	relHTML : function() {
		
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/gera-relatorio-html',
			target: '_blank'
		}).submit();
		
	},
	
	relPDF : function() {
		
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/gera-relatorio-pdf',
			target: '_blank'
		}).submit();
		
	},
	
	relXSL : function() {
		
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/gera-relatorio-xsl',
			target: '_blank'
		}).submit();
		
	}
    
};