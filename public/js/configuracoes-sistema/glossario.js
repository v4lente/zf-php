
//Classe interna, carregada no OnLoad
var Interna = {

	// Método principal da classe
	init : function() {
        
        //Redimensiona o tamanmho do textarea da descrição
        $("#ds_palavra").css("width","612px");
        $("#ds_palavra").css("height","80px");

        $("#buscar").click(function(){
            Interna.pesquisar();
        });
        $("#salvar").click(function(){

            Interna.salvar();
        });
        $("#voltar").click(function(){

            Base.voltar();
        });
        $("#fechar").click(function(){

            Base.sair();
        });
        $("#limpar").click(function(){

            Base.limpar();
        });
        $("#imprime").click(function(){

            Interna.relatorio();
        });
        $("#excluir").click(function(){
            if(confirm("Tem certeza que deseja excluir o registro selecionado?")){
                Interna.excluir();
            }
        });
        //verifica se a algum campo preenchido no formulario de busca para realizar a busca.
        $("form").submit(function(){
            var retorno = Interna.verificaBusca();
            if(retorno){
                return retorno;
            }else{
                alert("Preencha um ou mais campos para realizar a pesquisa/impressão");
                return retorno;
            }
        });



	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	// Salva o registro
	salvar : function() {
        // Faz a validação do formulário
		$valida = Valida.formulario('form_base', true);

		if ($valida[0]) {
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
	telapesquisa : function() {
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

	// Seleciona um registro
	selecionar : function(obj) {
		var key = $(obj).attr("key");
		document.location.href = baseUrlController + "/selecionar/"+ key;
	},

	// Chama a tela de relatórios
	relatorio : function(metodo){
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/relatorio/_rel_metodo/' + metodo,
			target: '_blank'
		}).submit();
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
        
        
    }
};
