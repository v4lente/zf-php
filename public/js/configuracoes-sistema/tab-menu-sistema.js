//Classe interna, carregada no OnLoad
var Interna = {

	// Seta alguns atributos da classe
	cd_sistema : "",
		
	// Método principal da classe
	init : function() {
        
		// Aplica as funções ajax somente para estas ações
		//if(no_action == "novo" || no_action == "salvar" || no_action == "selecionar") {
			
			// Inicializa os atributos
			Interna.cd_sistema = $("#cd_sistema").val();
		
			// Guarda o valor do cd_sistema ao setar o foco
			$("#cd_sistema_ac").focus(function(event) {
				Interna.cd_sistema = $("#cd_sistema").val();
			});
			
			// Gerencia o campo cd_sistema
			$("#cd_sistema_ac").blur(function(event) {
				valorAtual = $("#cd_sistema").val();
				if(Interna.cd_sistema != valorAtual) {
					if(valorAtual != "") {
						Interna.retornaMenuSistemaX(valorAtual);
					}
				} 
			});
		//}
		
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
	}
    
};