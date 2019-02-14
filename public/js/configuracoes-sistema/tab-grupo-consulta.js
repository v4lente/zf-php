//Classe interna, carregada no OnLoad
var Interna = {
	// M�todo principal da classe
	init : function() {
		
		// Aplica as fun��es ajax somente para estas a��es
		if(no_action == "novo") {
			
			//autocompletes dos campos T�tulo Consulta
			$(".i_titulo").autocomplete({
					//source: baseUrlController + "/auto-complete-titulo-x/cd_grupo/" + $("#cd_grupo").val(),
					source: baseUrlController + "/auto-complete-titulo-x/cd_grupo/",
					minLength: 3,
					search: function() {
						var id = $(this).attr("id");
						// Limpa o alvo sempre ao fazer alguma consulta
						$("#"+$("#"+id).attr("target")).val("");
						
						//quebra o id para pegar o �ndice da linha
						id = id.split("_");
						var indice = id[1];
						
						//limpa os campos
						$("#cd_consulta_"+indice).val("");
						$("#responsavel_"+indice).val("");
						$("#dthr_cadastro_"+indice).val("");
						$("#fl_ativo_"+indice).val("");
					},
					select: function( event, ui ) {
						//pega id do campo
						var id = $(this).attr("id");
						//quebra o id para pegar o �ndice da linha
						id = id.split("_");
						var indice = id[1];
						
						//divide o retorno e preenche os 4 campos
						var quebra=ui.item.target;
					    quebra=quebra.split("-");
						$("#cd_consulta_"+indice).val(quebra[0]);
						$("#titulo_"+indice).val(quebra[1]);
						$("#dthr_cadastro_"+indice).val(quebra[2]);
						$("#responsavel_"+indice).val(quebra[3]);
						$("#fl_ativo_"+indice).val(quebra[4]);
					}
			});
			
			//ao sair dos campos T�tulo Consulta
			$(".i_titulo").blur(function(){
				//pega id do campo
				var id = $(this).attr("id");
				//quebra o id para pegar o �ndice da linha
				id = id.split("_");
				var indice = id[1];
				
				//limpa os demais campos da linha
				if($("#titulo_"+indice).val() == ""){
					$("#cd_consulta_"+indice).val("");
					$("#dthr_cadastro_"+indice).val("");
					$("#responsavel_"+indice).val("");
					$("#fl_ativo_"+indice).val("");
				}
			});
			
			//ao sair dos campos de C�digo Consulta
			$(".i_cd_consulta").blur(function(){
				//pega id do campo
				var id = $(this).attr("id");
				//quebra o id para pegar o �ndice da linha
				id = id.split("_");
				var indice = id[2];
								
				//se estiver preenchido chama fun��o de validar c�digo informado e que preenche os demais campos da linha
				if($(this).val() != ""){
					//chama fun��o de valida��o passando c�digo da consulta informado, c�digo do grupo e o indice do campo
					//Interna.validaCodigoX($(this).val(), $("#cd_grupo").val(), indice);
					Interna.validaCodigoX($(this).val(), indice);
				}else{
					//limpa campos
					$("#titulo_"+indice).val("");
					$("#responsavel_"+indice).val("");
					$("#dthr_cadastro_"+indice).val("");
					$("#fl_ativo_"+indice).val("");
				}
			});
			
			
		}else if(no_action == "selecionar"){
			$('#botao-salvar').hide();
		}
		
	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	// Salva o registro
	salvar : function() {
		//flag usada para saber se alguma linha foi preenchida 1 = salva, 0 = n�o salva
		var salvar = 0;
		
		//vetor para armazenar n�mero das linhas repetidas para informar em mensagem de erro
		var linhas = new Array();
		
		//percorre campos para verificar se alguma linha foi preenchida e se linhas foram preenchidas com valores iguais
		for(var i = 0; i < 10; i++){
			//se linha estiver preenchida
			if(($("#cd_consulta_"+i).val() != "") && ($("#titulo_"+i).val() != "")){
				//seta flag para permitir salvar
				salvar = 1;
				for(var j = 0; j < 10; j++){
					//testa se linhas s�o diferentes
					if(i != j){
						//se encontrar valores iguais em linhas diferentes
						if($("#cd_consulta_"+i).val() == $("#cd_consulta_"+j).val()){
							//procura n�mero da linha repetida no vetor e caso n�o encontre, insere no vetor
							if(jQuery.inArray(i+1, linhas) == -1){
								linhas.push(i+1);
							}
						}
					}
				}
			}
		}
	
		if(salvar == 1){
			//se n�o possuir valores repetidos nas linhas
			if(linhas.length == 0){
				
				// Faz a valida��o do formul�rio
				valida = Valida.formulario('form_base', true);
			
				if (valida[0]){
					$('#form_base').attr({
						onsubmit: 'return true',
						action: baseUrlController + '/salvar',
						target: ''
					}).submit();
				}
			
			//caso contr�rio exibe mensagem de erro e informa em quais linhas ocorrem repeti��o
			}else{
				//monta mensagem de erro
				var msg = "N�o foi poss�vel salvar, pois linhas foram preenchidas com valores repetidos. Rever linhas: ";
				for(var x=0;x<linhas.length;x++){
					msg=msg+linhas[x]+"   ";
				}				
				
				Base.montaMensagemSistema(Array(msg), 'ATEN��O!', 4);
			}
		//exibe mensagem de erro
		}else{
			Base.montaMensagemSistema(Array("Para salvar � necess�rio preencher os campos de pelo menos uma das linhas."), 'ATEN��O!', 4);
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
		// Chama a pesquisa da classe form devido ao erro de sess�o com POST
		Form.pesquisar();
		
	},

	// Seleciona um registro, recebendo a chave j� criptografada
	selecionar : function(chave) {
		document.location.href = baseUrlController + "/selecionar/" + chave;
	},

	//chama o relatorio
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
            if($.trim($(e).val()) != ''){
                existe = true;
            }
        });
        return existe;
    },
	
	// Valida c�digo informado e preenche os demais campos
	//validaCodigoX : function(cd_consulta, cd_grupo, indice) {
	validaCodigoX : function(cd_consulta, indice) {
		
		// Executa a requisi��o AJAX com retorno JSON
		$.getJSON(baseUrlController + '/valida-codigo-x/'
		//, { 'cd_consulta' : cd_consulta, 'cd_grupo' : cd_grupo } // Seta os parametros a serem passados por ajax	
		, { 'cd_consulta' : cd_consulta} // Seta os parametros a serem passados por ajax	
		, function (retorno){ // Pega o retorno 
			
			//se mensagem de erro vier vazia, preenche os outros campos
			if(retorno.MSG == ''){
				$("#cd_consulta_"+indice).val(retorno.CD_CONSULTA);
				$("#titulo_"+indice).val(retorno.TITULO);
				$("#responsavel_"+indice).val(retorno.CD_USUARIO);
				$("#dthr_cadastro_"+indice).val(retorno.DTHR_CADASTRO);
				$("#fl_ativo_"+indice).val(retorno.FL_ATIVO);
			}
			//sen�o exibe mensagem de erro e limpa campos
			else{
				Base.montaMensagemSistema(Array(retorno.MSG), 'ATEN��O!', 4);
				$("#titulo_"+indice).val("");
				$("#responsavel_"+indice).val("");
				$("#dthr_cadastro_"+indice).val("");
				$("#fl_ativo_"+indice).val("");
			}
			
			
		});
	
	}

};