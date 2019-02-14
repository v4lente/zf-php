//Classe interna, carregada no OnLoad
var Interna = {

	limpaNoGrupo : false,

	// Método principal da classe
	init : function() {

		// Aplica as funções ajax somente para estas ações
		if (no_action == "novo") {

			$(".noGrupo").autocomplete({
				source: baseUrlController + "/auto-complete-grupo-x/",
				minLength: 1,
				search: function(event, ui) {
					$("#cd_grupo"+$(this).attr('indice')).val("");
					$("#ds_grupo"+$(this).attr('indice')).val("");
				},
				select: function( event, ui ) {
					limpaNoGrupo = true;
					//joga o retorno para o campo cd_tabela
					$("#cd_grupo"+$(this).attr('indice')).val(ui.item.cd_grupo);
					$("#ds_grupo"+$(this).attr('indice')).val(ui.item.ds_grupo);
				}
			}).blur(function() {
				if($("#cd_grupo"+$(this).attr('indice')).val() == "") {
					$(this).val("");
					$("#cd_grupo"+$(this).attr('indice')).val("");
					$("#ds_grupo"+$(this).attr('indice')).val("");
				}
			});
			
			$(".cdGrupo").each(function(){
				$(this).blur(function () {
					if ($(this).val() != ''){
						objLinha = $(this);
						// Executa a requisição AJAX com retorno JSON
						$.getJSON(baseUrlController + "/auto-complete-grupo-x/"
						, { 'cd_grupo' : $(this).val() } // Seta o parametro a ser passado por ajax	
						, function (retorno){ // Pega o retorno 
						
							$('#cd_grupo'+$(objLinha).attr('indice')).val('');
							$('#no_grupo'+$(objLinha).attr('indice')).val('');
							$('#ds_grupo'+$(objLinha).attr('indice')).val('');

							$(retorno).each(function(i, v) {
								$('#cd_grupo'+$(objLinha).attr('indice')).val(v.cd_grupo);
								$('#no_grupo'+$(objLinha).attr('indice')).val(v.label);
								$('#ds_grupo'+$(objLinha).attr('indice')).val(v.ds_grupo);
							});
						});
					}

				});
			});
			
			//ao sair dos campos de Código Consulta
			$(".i_cd_consulta").blur(function(){
				//pega id do campo
				var id = $(this).attr("id");
				//quebra o id para pegar o índice da linha
				id = id.split("_");
				var indice = id[2];
								
				//se estiver preenchido chama função de validar código informado e que preenche os demais campos da linha
				if($(this).val() != ""){
					//chama função de validação passando código da consulta informado, código do grupo e o indice do campo
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

		var linhaPreenchida = false;
		var linhaDuplicada	= false;

		// Teste para verificar se pelo menos uma linha foi preenchida
		$('.cdGrupo').each( function() { 
			var cdGrupoRef = $(this);
			if ( $(cdGrupoRef).val()  != '' ) {
				linhaPreenchida	= true;
			}

			$('.cdGrupo').each( function() { 
				if ( $(cdGrupoRef).attr('indice') != $(this).attr('indice')) {
					if ( $(cdGrupoRef).val() == $(this).val() && $(this).val() != '') {

						linhaDuplicada = true;
						return false;
					}
				}
			});
			if (linhaDuplicada == true){
				return false;
			}
		});

        // Faz a validação do formulário
		valida = Valida.formulario('form_base', true);

		if ( linhaPreenchida==true && linhaDuplicada==false) {
			if (valida[0]) {
				$('#form_base').attr({
					onsubmit: 'return true',
					action: baseUrlController + '/salvar',
					target: ''
				}).submit();
			}
		}else{
			if ( linhaPreenchida==false ) {
				Base.montaMensagemSistema(Array("É necessário preencher ao menos uma linha!"), 'ATENÇÃO!', 4);
			}else if (linhaDuplicada==true){
				Base.montaMensagemSistema(Array("Há linhas duplicadas. Verifique!"), 'ATENÇÃO!', 4);
			}
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
		// Chama a pesquisa da classe form devido ao erro de sessão com POST
		Form.pesquisar();
		
	},

	// Seleciona um registro, recebendo a chave já criptografada
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
	
	// Valida código informado e preenche os demais campos
	//validaCodigoX : function(cd_consulta, cd_grupo, indice) {
	validaCodigoX : function(cd_consulta, indice) {
		
		// Executa a requisição AJAX com retorno JSON
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
			//senão exibe mensagem de erro e limpa campos
			else{
				Base.montaMensagemSistema(Array(retorno.MSG), 'ATENÇÃO!', 4);
				$("#titulo_"+indice).val("");
				$("#responsavel_"+indice).val("");
				$("#dthr_cadastro_"+indice).val("");
				$("#fl_ativo_"+indice).val("");
			}
			
			
		});
	
	}

};