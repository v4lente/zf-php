//Classe interna, carregada no OnLoad
var Interna = {
		
	// M�todo principal da classe
	init : function() {
        
        $("#botao-pesquisar")
            .attr("src", baseUrl + "/images/botoes/reset.png")
            .attr("title", "Atualizar").tooltip({ showURL: false, positionLeft: true });
        
		$("#botao-salvar")
            .attr("src", baseUrl + "/images/botoes/fechar2.png")
            .attr("title", "Desconectar Usu�rios").tooltip({ showURL: false, positionLeft: true });
        
        // Ao clicar no checkbox, marca ou desmarca
		$(".cbsel").click(function() {

            if($(this).is(":checked")) {
                $(this).attr("value", "1");
			} else {
				$(this).attr("value", "0");
			}

		});
        
	},
	
	// Gera um novo documento
	novo : function() { },
    
	// Salva o registro
	salvar : function() {
        
        // Faz a valida��o do formul�rio
		valida = Interna.validaFormulario();

		if (valida[0]) {
            Base.escondeTelaCarregando();
            Base.confirm("Deseja desconectar os usu�rios selecionados?", "Aten��o", "Interna.desconectar();");
            
		}
	},
    
    desconectar : function() {
        
        // Cria os campos hidden
        $(".cbsel").each(function() {

            // verifica se est� marcado
            if($(this).attr("value") == "1") {

                // Adicina o campo com o c�digo da pessoa
                $("#form_base").prepend("<input type='hidden' name='usuarios[]' value='"+ $(this).attr("sid") + "@" + $(this).attr("serial") + "' />");

            }

        });

        $('#form_base').attr({
            onsubmit: 'return true',
            action: baseUrlController + '/salvar',
            target: ''
        }).submit();
        
    },

	// Excluir o registro 
	excluir : function() { },

	// Chama a tela de pesquisa
	telaPesquisa : function() { },

	// Executa a tela de pesquisa
	pesquisar : function() {
        window.location.href = baseUrlController + '/index';
    },

	// Seleciona um registro, recebendo a chave j� criptografada
	selecionar : function(chave) { },

	// Chama a tela de relat�rios
	relatorio : function(metodo){ },
	
	// Volta para a listagem
	voltar : function() { },

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
        
      	var mensagem = Array();
    	var valida = false; 
    	
		if ( ! valida ) {	
			mensagem.push("");
		}
        
        var selecionado = 0;

		//Para cada checbox
		$(".cbsel").each(function() {

			// verifica se est� marcado
			if($(this).attr("value") == "1") {

				// Indica que existe pelo menos um checkbox marcado
				selecionado++;

			}
		});

		if (selecionado == 0) {
            mensagem.push("� necess�rio marcar pelo menos um usu�rio para poder desconectar!");
		}
        
        // Chama a valida��o do sistema
        valida = Valida.formulario('form_base', true, mensagem);


        return valida ;

    }
    
};