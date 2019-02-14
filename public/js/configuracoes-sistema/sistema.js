//Classe interna, carregada no OnLoad
var Interna = {
		
	// Método principal da classe
	init : function() {
		
		if(no_action == "novo") {
			
			// Trata o nome do sistema e pré-cria
			// um nome de pasta para as transações
			$("#no_sistema").blur(function() {
				var valor = $(this).val();
				valor = Form.retirarAcento(this);
				valor = valor.toLowerCase();
				
				// Captura as palavras sem acentos maiores que 3 digitos 
				var divVal = valor.match(/[A-Za-z1-9]{3,}/gi);
				
				var valor2 = "";
				for(var i=0; i < divVal.length; i++) {
					if(valor2 != "") {
						valor2 += "-";
					}
					
					valor2 += divVal[i];
				}
				
				$("#no_pasta_zend").val($.trim(valor2));
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
	}  
	
};