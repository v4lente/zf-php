//Classe interna, carregada no OnLoad
var Interna = {
		
	// M�todo principal da classe
	init : function() {

		$("#botao-relatorio").unbind("click");
		$("#botao-relatorio").bind("click", function() {
			Eventos.tipoRelatorio();
		});

	},
/*
    initSelecionarAction : function() {
        
        $(":input").unbind("keydown");
        $(":input").bind("keydown", function(event) {
            if (event.which == 13) {
                Eventos.tipoRelatorio();
            }
		});
        
    },
*/
	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},
	
	// Salva o registro
	salvar : function() {},

	// Excluir o registro
	excluir : function() {},

	// Chama a tela de pesquisa
	telaPesquisa : function() {
		document.location.href = baseUrlController + "/index";
	},

	// Executa a tela de pesquisa
	pesquisar : function() {
		// Chama a pesquisa da classe form devido 
		// ao erro de sess�o com POST
		Form.pesquisar();
	},

	// Seleciona um registro, recebendo a chave j� criptografada
	selecionar : function(chave) {
		document.location.href = baseUrlController + "/selecionar/" + chave;
	},

	// Chama a tela de relat�rios
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

    //Verfica se h� algum conteudo digitado em algum campo para poder realizar a busca.
    verificaBusca : function(){
        var existe = false;
       
        $("form :input[type!='button']").each(function(i, e){
            if($.trim($(e).val()) != '') {
                existe = true;  
            }
        });

        return existe;

    }
    
};