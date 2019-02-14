//Classe interna, carregada no OnLoad
var Interna = {

	bExcedeuPerc 	: false,
	valExcedeu 		: 0,
	bPosCadastrada	: false,

	// Método principal da classe
	init : function() {
		bExcedeuPerc = false;
		bPosCadastrada = false;
		valExcedeu = 0;

		// Aplica as funções ajax somente para estas ações
		if(no_action == "novo") {
            var parametros = "?fl_possui_tabs=" + $("#fl_possui_tabs").val() + '&cd_consulta=' + $("#cd_consulta").val();
			//autocompletes das tabelas
			$("#no_tabela").autocomplete({
				source: baseUrlController + "/auto-complete-tabela-x" + parametros,
				minLength: 2,
				change: function(event, ui) {
					
					// Verifica se teve retorno
					if (ui.item == null) {
						//limpa o campo
						$("#cd_tabela").val("");
						$("#ds_tabela").val("");

						var table = $('table.nao-selecionavel');
						$(table).find('tbody').html('');
                        
                        // Limpa o combo
                        $("#fk_tabela").html("<option label=' -- Selecione -- ' value='_null_'> -- Selecione -- </option>");
                        $("#fk_tabela").val("_null_");

					}
				},
				select: function( event, ui ) {
				
					//joga o retorno para o campo cd_tabela
					$("#cd_tabela").val(ui.item.cd_tabela);
					$("#ds_tabela").val(ui.item.ds_tabela);
					
					Interna.geraDadosTabela();
                    Interna.retornaConexoesTabelas();
				}
			});

		} else if(no_action == "selecionar") {
            $("#no_tabela").attr("disabled", "disabled");
        }
		
		//Chama o salvar da janela pai
		if($("#controleSalvar").val() == "1") {
            Base.mostraTelaCarregando();
			window.parent.Interna.salvar();
		}
        
        // Seta o alias da tabela referênciada
		$("#fk_tabela_ac").bind("autocompleteselect", function(event, ui) {
			
            var aliasTabelaNova = $(ui.item.option).attr("alias-tabela-nova");
            var aliasTabelaRef  = $(ui.item.option).attr("alias-tabela-ref");
            
            if(aliasTabelaNova != "") {
                $("#alias_tabela_nova").val(aliasTabelaNova);
                $("#alias_tabela_ref").val(aliasTabelaRef);
            }
            
        });

	},
	
	initSelecionarAction : function () {

		$('#tabs-vinculadas > tbody > tr').click ( function () {
			$('#tabs-vinculadas > tbody > tr').removeClass('hover');
			$(this).addClass('hover');

			$('#no_tabela').val($(this).text()); 
			$('#cd_tabela').val($('#cd_tabela_vinc_'           + $(this).attr('id')).val()); 
			$('#ds_tabela').val($('#ds_tabela_vinc_'           + $(this).attr('id')).val()); 
            $('#alias_tabela_nova').val($('#alias_tabela_vinc_' + $(this).attr('id')).val()); 
            
			Interna.geraDadosTabela();
            Interna.retornaConexoesTabelas();
		});
		
		Interna.geraDadosTabela();
        Interna.retornaConexoesTabelas();
	},
	
	// Gera os dados da tabela que foi selecionada no autocompletex
	geraDadosTabela : function () {
        
        // Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-colunas-tabela-x/"
		, { cd_tabela : $("#cd_tabela").val(), alias_tabela : $("#alias_tabela_nova").val() } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 

			// Recupera os menus do sistema selecionado
			var bodyTable = "";
            
            var somaLargura = 0;
            
			$(retorno).each(function(i, v) {

				var posicoes_ja_cads = new Array();
				var strPosJaCads 	 = "";
				var cd_cons_coluna   = 0;
				var fl_mostra        = 0;
				var cd_coluna        = "";
                var no_coluna        = "";
				var tp_coluna        = "";
				var ds_cabecalho     = "";
				var nr_posicao       = 0;
				var tam_largura      = 0;
				var nr_ordem         = 0;
				var fl_soma          = 0;
				var fl_filtro        = 0;
                var fl_visivel       = 0;

				var readonly         = '';
				var disabled         = '';
				var disabledSoma     = '';
				var somaChecked      = '';
				var mostraChecked    = '';
				var filtroChecked    = '';
                var visivelChecked   = '';
                
                

				if (v.posicoes_ja_cads.length > 0){
					$(v.posicoes_ja_cads).each(function (ip, vp) {
						posicoes_ja_cads[ip] = vp.NR_POSICAO;
					});
					strPosJaCads = posicoes_ja_cads.join();
					$('#posicoes_ja_cads').val(strPosJaCads);
				}

				if ($.trim(v.cd_cons_coluna) != ""){
					cd_cons_coluna = v.cd_cons_coluna;
				}

				if ($.trim(v.fl_mostra) != ""){
					fl_mostra = v.fl_mostra;
				}

                cd_coluna = v.cd_coluna;
				no_coluna = v.no_coluna;

				if ($.trim(v.ds_cabecalho) != ""){
					ds_cabecalho = v.ds_cabecalho;
				}else{
					ds_cabecalho = v.no_coluna;
				}
				
				if ($.trim(v.tp_coluna) != ""){
					tp_coluna = v.tp_coluna;
				}

				if ($.trim(v.nr_posicao) != ""){
					nr_posicao = v.nr_posicao;
				}

				if ($.trim(v.tam_largura) != ""){
					tam_largura = v.tam_largura;
                    somaLargura += Number(tam_largura);
				}

				if ($.trim(v.nr_ordem) != ""){
					nr_ordem = v.nr_ordem;
				}

				if ($.trim(v.fl_soma) != 0){
					fl_soma = v.fl_soma;
					somaChecked = 'checked="checked"';
				}

				if ($.trim(v.fl_filtro) != "" || $.trim(v.fl_filtro) > 0){
					fl_filtro = 1;
					filtroChecked = 'checked="checked"';
				}
                
                if ($.trim(v.fl_visivel) != 0){
					fl_visivel = v.fl_visivel;
					visivelChecked = 'checked="checked"';
				}

				if (fl_mostra == 0){
					readonly = 'readonly="readonly"';
					disabled = "disabled";
					disabledSoma = "disabled";
					mostraChecked = '';
				}else{
					mostraChecked = 'checked="checked"';
					if (tp_coluna == 'N' || tp_coluna == 'NUMERIC' || tp_coluna == 'MONEY' || tp_coluna == 'INT' || tp_coluna == 'TINYINT' || tp_coluna == 'SMALLINT' || tp_coluna == 'BIGINT') {
						disabledSoma = "";
					}else{
						disabledSoma = "disabled";
					}

				}

				bodyTable = bodyTable + '<tr>';
                bodyTable = bodyTable +     '<td>';
                bodyTable = bodyTable +         '<input type="hidden" name="cd_cons_coluna[]" id="cd_cons_coluna_'+i+'" value="'+cd_cons_coluna+'" indice="'+i+'">';
                bodyTable = bodyTable +         '<input type="hidden" name="cd_coluna[]" id="cd_coluna_'+i+'" value="'+cd_coluna+'" indice="'+i+'">';
				bodyTable = bodyTable +         '<input type="checkbox" class="chkHabilitar" name="fl_mostra[]" id="fl_mostra_'+i+'" value="'+fl_mostra+'" indice="'+i+'" '+mostraChecked+' tp_coluna="'+tp_coluna+'">';
                bodyTable = bodyTable +     '</td>';
				bodyTable = bodyTable +     '<td><input type="text" class="noColuna" name="no_coluna[]" id="no_coluna_'+i+'" value="'+no_coluna+'" readonly="readonly" indice="'+i+'"></td>';
				bodyTable = bodyTable +     '<td><input type="text" class="noCabecalho" name="ds_cabecalho[]" id="ds_cabecalho_'+i+'" maxlength="50" value="'+ds_cabecalho+'" '+readonly+' indice="'+i+'"></td>';
				bodyTable = bodyTable +     '<td><input type="text" class="nrPosicao" name="nr_posicao[]" id="nr_posicao_'+i+'" value="'+nr_posicao+'" maxlength="2" '+readonly+' indice="'+i+'"></td>';
				bodyTable = bodyTable +     '<td><input type="text" class="tamLargura" name="tam_largura[]" id="tam_largura_'+i+'" value="'+tam_largura+'" maxlength="3" '+readonly+' indice="'+i+'"></td>';
				bodyTable = bodyTable +     '<td><input type="text" class="nrOrdenacao" name="nr_ordem[]" id="nr_ordem_'+i+'" value="'+nr_ordem+'" maxlength="2" '+readonly+' indice="'+i+'"></td>';
				bodyTable = bodyTable +     '<td><input type="checkbox" no-hidden="no-hidden" class="chkSoma" name="fl_soma[]" id="fl_soma_'+i+'" value="'+fl_soma+'" '+disabledSoma+' indice="'+i+'" '+somaChecked+'></td>';
				bodyTable = bodyTable +     '<td><input type="checkbox" no-hidden="no-hidden" class="chkFiltro" name="fl_filtro[]" id="fl_filtro_'+i+'" value="'+fl_filtro+'" '+disabled+' indice="'+i+'" '+filtroChecked+' tp_coluna="'+tp_coluna+'"></td>';
                bodyTable = bodyTable +     '<td><input type="checkbox" no-hidden="no-hidden" class="chkVisivel" name="fl_visivel[]" id="fl_visivel_'+i+'" value="'+fl_visivel+'" '+disabled+' indice="'+i+'" '+visivelChecked+'></td>';
				bodyTable = bodyTable + '</tr>';

			});

			// Seta o corpo da table na tela
			var table = $('table.nao-selecionavel');
			$(table).find('tbody').html(bodyTable);

			$(".chkHabilitar").each(function(){
				$(this).click(function () {
					Interna.habilitar($(this));
				});
			});

			$(".chkSoma").each(function(){
				$(this).click(function () {
					Interna.setSoma($(this));
				});
			});

			$(".chkFiltro").each(function(){
				$(this).click(function () {
					Interna.addFiltro($(this));
				});

				if(no_action == "selecionar") {
					if ($(this).is(":checked")){
						Interna.addFiltroSelecionar($(this), $(this).attr('indice'));
					}
				}
			});
            
            $(".chkVisivel").each(function(){
				$(this).click(function () {
					Interna.setVisivel($(this));
				});
			});
            
            $(".nrPosicao").each(function(){
                $(this).change(function () {
                    var posAtual = $(this);
                    if ($(this).val() != 0) {
                        $(".chkHabilitar").each(function(){
                            if ($(this).is(":checked")){
                                var posJaCadastradas = $('#posicoes_ja_cads').val().split(',');

                                for (x in posJaCadastradas){
                                    if (posJaCadastradas[x] == parseInt(posAtual.val())){
                                        Base.montaMensagemSistema(Array('A posição "'+posJaCadastradas[x]+'" já está em uso. As seguintes posições não podem ser usadas: "'+posJaCadastradas.sort()+'"'), 'Atenção', 4, "");
                                        bPosCadastrada = true;
                                        return false;
                                    }else{
                                        bPosCadastrada = false;
                                    }
                                }

                                if ( $(this).attr('indice') != $(posAtual).attr('indice')) {
                                    if (parseInt($('#nr_posicao_'+$(this).attr('indice')).val()) == parseInt(posAtual.val())) {
                                        Base.montaMensagemSistema(Array('Posição já informada na tela. Verifique!'), 'Atenção', 4, "");
                                        return false;
                                    }
                                }
                            }
                        });
                    }else{
                        Base.montaMensagemSistema(Array('Posição já informada na tela. Verifique!'), 'Atenção', 4, "");
                        return false;
                    }
                });
            });

    /* NÃO APAGAR, REVER CÓDIGO
            $(".nrPosicao").each(function(){
                $(this).change(function () {
                    var nrPos = $(this);
                    var qtde = $(".chkHabilitar:checked").size();
                    $(".chkHabilitar").each(function(){
                        if ($(this).is(":checked")){
                            if ( $('#nr_posicao_'+$(this).attr('indice')).val() >= $(nrPos).val() ){
                                if ( $('#nr_posicao_'+$(this).attr('indice')).val() < qtde ) {
                                    $('#nr_posicao_'+$(this).attr('indice')).not($(nrPos)).val( parseInt($('#nr_posicao_'+$(this).attr('indice')).val()) + parseInt(1) )
                                }else if ( $('#nr_posicao_'+$(this).attr('indice')).val() >= qtde ) {
                                    $('#nr_posicao_'+$(this).attr('indice')).not($(nrPos)).val( parseInt(qtde) - parseInt(1) );
                                }
                            }
                        }
                    });
                });
            });
    

            // Seta o somatório de largura da tela
            $("#perc_colunas_tela").val(somaLargura);

            var totTela = 0;
            var totTelaInicio = 0;
            var tamDigitado = 0;
            var tamAnterior = 0;
            $(".tamLargura").each(function(){

                if ($(this).attr('readonly') == false) {
                    totTelaInicio = Number(totTela) + Number($(this).val());
                    totTela = Number(totTela) + Number($(this).val());
                }

                $(this).change(function () {
                    
                    tamDigitado = $(this).val();
                    var percTotal = $('#perc_colunas').val();
                    var aux = Number(totTela) - Number(tamAnterior) + Number(tamDigitado);
                    if ( ( (Number(percTotal)- Number(totTelaInicio)) + Number(aux)) > 100 ){
                        valExcedeu = ((Number(percTotal)- Number(totTelaInicio)) + Number(aux)) - 100;
                        Base.montaMensagemSistema(Array('Largura informada excede em '+valExcedeu+'% o tamanho total do relatório. Verifique as informações de "Largura %", inclusive de tabelas já gravadas anteriormente (se for o caso). A soma não deve exceder 100%!'), 'Atenção', 4, "");

                        bExcedeuPerc = true;
                    }else{
                        bExcedeuPerc = false;
                    }
                     totTela = Number (totTela) - Number(tamAnterior) + Number(tamDigitado);
                });

                $(this).focus(function () {
                    tamAnterior = $(this).val();
                });

            });
    */
	
        });
       
	},
	
	// Função responsável por habilitar ou desabilitar a edição das colunas
	habilitar : function(chk) {

		var tr = $(chk).parent().parent();
//		var qtdeChecks = $(".chkHabilitar:checked").size();
//		alert ($(chk).parent().parent().get(0).tagName);
		if ($(chk).is(":checked")){
			//Habilita os campos da linha
			$("input[type=text]", tr).not($('.noColuna')).attr("readonly", false);

			if ($(chk).attr("tp_coluna").toUpperCase() == 'N' || $(chk).attr("tp_coluna").toUpperCase() == 'NUMERIC' || $(chk).attr("tp_coluna").toUpperCase() == 'MONEY' || $(chk).attr("tp_coluna").toUpperCase() == 'SMALLINT' || $(chk).attr("tp_coluna").toUpperCase() == 'INT' || $(chk).attr("tp_coluna").toUpperCase() == 'BIGINT' || $(chk).attr("tp_coluna").toUpperCase() == 'TINYINT'){
				$("input[type=checkbox]", tr).removeAttr("disabled");
			}else{
				$("input[type=checkbox]", tr).not($('.chkSoma')).removeAttr("disabled");
			}
			$("#fl_mostra_"+$(chk).attr("indice")).val(1);
            $("#fl_visivel_"+$(chk).attr("indice")).val(1).attr("checked", "checked");
		}else{
			//Desabilita os campos da linha
			$("input[type=text]", tr).attr("readonly", true);
			$("input[type=checkbox]", tr).not($('.chkHabilitar')).attr("disabled", "disabled");
			$("#fl_mostra_"+$(chk).attr("indice")).val(0);
            $("#fl_visivel_"+$(chk).attr("indice")).val(0).removeAttr("checked");
			Interna.limpaCampos(tr);
		}

	},
	
	// Função responsável por setar para 1 o valor do campo soma quando for selecionado
	setSoma : function(chk) {
		if ($(chk).is(":checked")){
			//Seta 1 no value por id
			$("#fl_soma_"+$(chk).attr("indice")).val(1);
		}else{
			//Seta 0 no value por id
			$("#fl_soma_"+$(chk).attr("indice")).val(0);
		}
	},

	// Função responsável por adicionar o trecho do filtro, quando o check do mesmo for selecionado
	addFiltro : function(chk) {

		var tr = $(chk).parent().parent();

		if ($(chk).is(":checked")){
/*
			var cssHeadFiltro =	'background-color: #A12729;' +
								'border-bottom: 1px solid #AAAAAA;' +
								'color: #FFFFFF;' +
								'font-weight: bold;' +
								'background-color: #A12729;';
*/
			$("#fl_filtro_"+$(chk).attr("indice")).val(1);
			var iFiltro = 0;
			var tableFiltro = 	'<tr class="trFiltro"><td><input type="hidden" name="cd_cons_filtro_'+$(chk).attr("indice")+'[]" value=""></td>' +
								'<td>' +
								'	<table class="'+$(chk).attr('id')+'">' +
								'		<thead>' +
								'			<tr>' +
								'				<th>Tipo Filtro</th>' + 
								'				<th>Valor Filtro</th>' + 
								'				<th class="botoes_tab" column="adicionar">' +
								'					<span id="bt_add_'+$(chk).attr('id')+'" style="cursor: pointer;"><img alt="Adicionar Filtro" src="'+ baseUrl +'/public/images/botoes/16x16_adicionar.png"></span>' +
								'				</th>' + 
								'			</tr>' + 
								'		</thead>' + 
								'		<tbody>' + 
								'			<tr>'+
								'				<td>' +
								'   				<select class="tipoFiltro" name="cd_tp_filtro_'+$(chk).attr("indice")+'[]">' +
								'  						<option value="1">É igual (=)</option> ' +
								'  						<option value="2">É diferente (<>)</option> ' +
								'  						<option value="3">Maior que (>)</option> ' +
								'  						<option value="4">Menor que (<)</option> ' +
								'  						<option value="5">Maior igual que (>=)</option> ' +
								'  						<option value="6">Menor igual que (<=)</option> ' +
								'					</select>' +
								'				</td>' +
								'				<td><input type="text" col="'+$(chk).attr("indice")+'" class="ds_filtro" name="ds_filtro_'+$(chk).attr("indice")+'[]" maxlength="15" value=""></td>' + 
								'				<td></td>' + 
								'			</tr>' + 
								'		</tbody>' + 
								'	</table>' +
								'</td></tr>';

			$(tableFiltro).insertAfter($(tr).closest('tr'));
			
			$("#bt_add_"+$(chk).attr('id')).click(function() {

				var conteudo = 	'<tr>'+
								'	<td>' +
								'   	<select class="tipoFiltro" name="cd_tp_filtro_'+$(chk).attr("indice")+'[]">' +
								'  			<option value="1">É igual (=)</option> ' +
								'  			<option value="2">É diferente (<>)</option> ' +
								'  			<option value="3">Maior que (>)</option> ' +
								'  			<option value="4">Menor que (<)</option> ' +
								'  			<option value="5">Maior igual que (>=)</option> ' +
								'  			<option value="6">Menor igual que (<=)</option> ' +
								'		</select>' +
								'	</td>' + 
								'	<td><input type="text" class="ds_filtro" col="'+$(chk).attr("indice")+'" name="ds_filtro_'+$(chk).attr("indice")+'[]" maxlength="15" value=""></td>' + 
								'	<td class="botoes_tab" column="Remover">' +
								'		<span class="bt_del_'+$(chk).attr('id')+'" style="cursor: pointer;"><img alt="Excluir Filtro" src="'+ baseUrl +'/public/images/botoes/16x16_deletar.png"></span>' +
								'	</td>' + 
								'</tr>';

				$("table."+$(chk).attr('id')).find('tbody > tr:last').after(conteudo);

				$(".bt_del_"+$(chk).attr('id')).each(function(){
					$(this).click(function () {
						$(this).parent().parent().remove();
					});
				});

				$('.ds_filtro').each(function() {
					if ($(chk).attr("tp_coluna").toUpperCase() == 'N' || $(chk).attr("tp_coluna").toUpperCase() == 'NUMERIC' || $(chk).attr("tp_coluna").toUpperCase() == 'MONEY' || $(chk).attr("tp_coluna").toUpperCase() == 'SMALLINT' || $(chk).attr("tp_coluna").toUpperCase() == 'INT' || $(chk).attr("tp_coluna").toUpperCase() == 'BIGINT' || $(chk).attr("tp_coluna").toUpperCase() == 'TINYINT'){
						$($('input[col="'+$(chk).attr("indice")+'"]')).keyup(function() {  
							var $this = $(this);  
							var valor = $this.val().replace(/[^0-9]+/g,'');
							$this.val( valor );
						});  
					}else if ($(chk).attr("tp_coluna").toUpperCase() == 'D' || $(chk).attr("tp_coluna").toUpperCase() == 'DATETIME' || $(chk).attr("tp_coluna").toUpperCase() == 'TIMESTAMP' ) {
						$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
						$($('input[col="'+$(chk).attr("indice")+'"]')).mask('99/99/9999');
					}else{
						$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
					}
				});

			});

			$('.ds_filtro').each(function() {
				if ($(chk).attr("tp_coluna").toUpperCase() == 'N' || $(chk).attr("tp_coluna").toUpperCase() == 'NUMERIC' || $(chk).attr("tp_coluna").toUpperCase() == 'MONEY' || $(chk).attr("tp_coluna").toUpperCase() == 'SMALLINT' || $(chk).attr("tp_coluna").toUpperCase() == 'INT' || $(chk).attr("tp_coluna").toUpperCase() == 'BIGINT' || $(chk).attr("tp_coluna").toUpperCase() == 'TINYINT'){
					$($('input[col="'+$(chk).attr("indice")+'"]')).keyup(function() {  
						var $this = $(this);  
						var valor = $this.val().replace(/[^0-9]+/g,'');
						$this.val( valor );
					});  
				}else if ($(chk).attr("tp_coluna").toUpperCase() == 'D' || $(chk).attr("tp_coluna").toUpperCase() == 'DATETIME' || $(chk).attr("tp_coluna").toUpperCase() == 'TIMESTAMP' ){
					$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
					$($('input[col="'+$(chk).attr("indice")+'"]')).mask('99/99/9999');
				}else{
					$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
				}
			});

		} else {
			$("#fl_filtro_"+$(chk).attr("indice")).val(0);
			$("table."+$(chk).attr('id')).parent().parent().remove();
		}

	},

	// Função responsável por adicionar o trecho do filtro, quando o check do mesmo for selecionado
	addFiltroSelecionar : function(chk, indice) {

		var tr = $(chk).parent().parent();
		var cdConsColuna = $('#cd_cons_coluna_'+$(chk).attr("indice")).val()

		if ($(chk).is(":checked")){

			// Executa a requisição AJAX com retorno JSON
			$.getJSON(baseUrlController + "/retorna-colunas-filtro-x/"
			, { cd_cons_coluna : cdConsColuna } // Seta o parametro a ser passado por ajax	
			, function (retorno){ // Pega o retorno 

				var cd_cons_coluna = 0;
				var cd_cons_filtro = 0;
				var cd_tp_filtro = "";
				var ds_filtro = "";

				var tableFiltro = 	'<tr class="trFiltro"><td></td>' +
									'<td>' +
									'	<table class="'+$(chk).attr('id')+'">' +
									'		<thead>' +
									'			<tr>' +
									'				<th>Tipo Filtro</th>' + 
									'				<th>Valor Filtro</th>' + 
									'				<th class="botoes_tab" column="adicionar">' +
									'					<span id="bt_add_'+$(chk).attr('id')+'" style="cursor: pointer;"><img alt="Adicionar Filtro" src="'+ baseUrl +'/public/images/botoes/16x16_adicionar.png"></span>' +
									'				</th>' + 
									'			</tr>' + 
									'		</thead>' + 
									'		<tbody>';

				$(retorno).each(function(i, v) {

					var selected1 = ( v.cd_tp_filtro == 1) ? ' selected' : '';
					var selected2 = ( v.cd_tp_filtro == 2) ? ' selected' : '';
					var selected3 = ( v.cd_tp_filtro == 3) ? ' selected' : '';
					var selected4 = ( v.cd_tp_filtro == 4) ? ' selected' : '';
					var selected5 = ( v.cd_tp_filtro == 5) ? ' selected' : '';
					var selected6 = ( v.cd_tp_filtro == 6) ? ' selected' : '';
					var selected7 = ( v.cd_tp_filtro == 7) ? ' selected' : ''; 
					// a opção 7 seria SQL Livre.
					//'<option value="7"'+selected7+'>Filtro SQL Livre</option> ' +

					tableFiltro = tableFiltro + 
									'<tr>' +
									'	<td><input type="hidden" name="cd_cons_filtro_'+$(chk).attr("indice")+'[]" value="'+v.cd_cons_filtro+'">' +
									'   	<select class="tipoFiltro" name="cd_tp_filtro_'+$(chk).attr("indice")+'[]">' +
									'  			<option value="1"'+selected1+'>É igual (=)</option> ' +
									'  			<option value="2"'+selected2+'>É diferente (<>)</option> ' +
									'  			<option value="3"'+selected3+'>Maior que (>)</option> ' +
									'  			<option value="4"'+selected4+'>Menor que (<)</option> ' +
									'  			<option value="5"'+selected5+'>Maior igual que (>=)</option> ' +
									'  			<option value="6"'+selected6+'>Menor igual que (<=)</option> ' +
									'		</select>' +
									'	</td>' +
									'	<td><input type="text" class="ds_filtro" col="'+$(chk).attr("indice")+'" name="ds_filtro_'+$(chk).attr("indice")+'[]" maxlength="15" value="'+v.ds_filtro+'"></td>' + 
									'	<td class="botoes_tab" column="Remover">' +
									'		<span class="bt_del_'+$(chk).attr('id')+'" style="cursor: pointer;"><img alt="Excluir Filtro" src="'+ baseUrl +'/public/images/botoes/16x16_deletar.png"></span>' +
									'	</td>' + 
									'</tr>';

//					$("table."+$(chk).attr('id')).find('tbody > tr:last').after(trFiltro);

					//alert (v.cd_cons_coluna +' - '+ v.cd_cons_filtro);
				});

				tableFiltro = tableFiltro +
					'		</tbody>' + 
					'	</table>' +
					'</td></tr>';

				$(tableFiltro).insertAfter($(tr).closest('tr'));

				$("#bt_add_"+$(chk).attr('id')).click(function() {
					var conteudo = 	'<tr>'+
									'	<td>' +
									'   	<select class="tipoFiltro" name="cd_tp_filtro_'+$(chk).attr("indice")+'[]">' +
									'  			<option value="1">É igual (=)</option> ' +
									'  			<option value="2">É diferente (<>)</option> ' +
									'  			<option value="3">Maior que (>)</option> ' +
									'  			<option value="4">Menor que (<)</option> ' +
									'  			<option value="5">Maior igual que (>=)</option> ' +
									'  			<option value="6">Menor igual que (<=)</option> ' +
									'		</select>' +
									'	</td>' + 
									'	<td><input type="text" class="ds_filtro" col="'+$(chk).attr("indice")+'" name="ds_filtro_'+$(chk).attr("indice")+'[]" maxlength="30" value=""></td>' + 
									'	<td class="botoes_tab" column="Remover">' +
									'		<span class="bt_del_'+$(chk).attr('id')+'" style="cursor: pointer;"><img alt="Excluir Filtro" src="'+ baseUrl +'/public/images/botoes/16x16_deletar.png"></span>' +
									'	</td>' + 
									'</tr>';
									
					$("table."+$(chk).attr('id')).find('tbody > tr:last').after(conteudo);

					$(".bt_del_"+$(chk).attr('id')).each(function(){
						$(this).click(function () {
							$(this).parent().parent().remove();
						});
					});
					
					$('.ds_filtro').each(function() { 
						if ($(chk).attr("tp_coluna").toUpperCase() == 'N' || $(chk).attr("tp_coluna").toUpperCase() == 'NUMERIC' || $(chk).attr("tp_coluna").toUpperCase() == 'MONEY' || $(chk).attr("tp_coluna").toUpperCase() == 'SMALLINT' || $(chk).attr("tp_coluna").toUpperCase() == 'INT'|| $(chk).attr("tp_coluna").toUpperCase() == 'BIGINT' || $(chk).attr("tp_coluna").toUpperCase() == 'TINYINT'){
							$($('input[col="'+$(chk).attr("indice")+'"]')).keyup(function() {  
								var $this = $(this);  
								var valor = $this.val().replace(/[^0-9]+/g,'');
								$this.val( valor );
							});
						}else if ($(chk).attr("tp_coluna").toUpperCase() == 'D' || $(chk).attr("tp_coluna").toUpperCase() == 'DATETIME' || $(chk).attr("tp_coluna").toUpperCase() == 'TIMESTAMP'){
							$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
							$($('input[col="'+$(chk).attr("indice")+'"]')).mask('99/99/9999');
						}else{
							$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
						}
					});

				});

				$(".bt_del_"+$(chk).attr('id')).each(function(){
					$(this).click(function () {
						$(this).parent().parent().remove();
					});
				});

				$('.ds_filtro').each(function(obj, i) {

					if ($(chk).attr("tp_coluna").toUpperCase() == 'N' || $(chk).attr("tp_coluna") == 'NUMERIC' || $(chk).attr("tp_coluna") == 'MONEY' || $(chk).attr("tp_coluna") == 'SMALLINT' || $(chk).attr("tp_coluna") == 'INT'|| $(chk).attr("tp_coluna").toUpperCase() == 'BIGINT' || $(chk).attr("tp_coluna").toUpperCase() == 'TINYINT'){
						$($('input[col="'+$(chk).attr("indice")+'"]')).keyup(function() {  
							var $this = $(this);
							var valor = $this.val().replace(/[^0-9]+/g,'');
							$this.val( valor );
						});  
					}else if ($(chk).attr("tp_coluna").toUpperCase() == 'D' || $(chk).attr("tp_coluna") == 'DATETIME' || $(chk).attr("tp_coluna") == 'TIMESTAMP'){
						$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
						$($('input[col="'+$(chk).attr("indice")+'"]')).mask('99/99/9999');
					}else{
						$($('input[col="'+$(chk).attr("indice")+'"]')).unmask();
					}
				});

			});

		}
		/*else{
			$("#fl_filtro_"+$(chk).attr("indice")).val(0);
			$("table."+$(chk).attr('id')).remove();
		}*/
	},
    
    // Função responsável por limpar alguns campos se não for aparecer no relatório o campo selecionado
	setVisivel : function(chk) {
		if ($(chk).is(":checked")){
			$("#nr_posicao_"+$(chk).attr("indice")).attr("disabled", false);
            $("#tam_largura_"+$(chk).attr("indice")).attr("disabled", false);
            $("#nr_ordem_"+$(chk).attr("indice")).attr("disabled", false);
		}else{
			$("#nr_posicao_"+$(chk).attr("indice")).attr("disabled", true).val("0");
            $("#tam_largura_"+$(chk).attr("indice")).attr("disabled", true).val("0");
            $("#nr_ordem_"+$(chk).attr("indice")).attr("disabled", true).val("0");
		}
	},

	// Limpa os campos que, por padrão, deves estar nulos ou zeros.
	limpaCampos : function(tr) {
		// Desceleciona o nome da ação
		$('input[type=text]', tr).not($('.noColuna')).val("0");
		$("input[type=checkbox]", tr).not($('.chkHabilitar')).attr('checked',false);
		$('.noCabecalho', tr).val($('.noColuna', tr).val());
		// Remove a linha correspondente ao filtro (se houver)
		var trFiltro = $(tr).next();
		if ($(trFiltro).attr('class') == 'trFiltro') {
			$(trFiltro).remove();
		}

	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo/fl_possui_tabs/"+ $("#tabelaConsulta > tbody > tr").size();
	},

	// Salva o registro
	salvar : function() {

        var habInvalida = true;
		var posInvalida = false;
		var ordInvalida = false;
        var conInvalida = false;
        var tamInvalida = false;

        if($("#fk_tabela").val() == "_null_" && $("#alias_tabela_nova").val() != "T1") {
            conInvalida = true;
        }
        
        // Percorre todos os campos que estão marcados
        var somaTamanhoLargura = 0;
        $('.chkHabilitar:checked').each( function() {
            
            habInvalida = false;
            
            obj1    = $(this);
            indice1 = $(this).attr("indice");
            
            objVisivel1     = $('#fl_visivel_'  + indice1);
            tamanhoLargura1 = $('#tam_largura_' + indice1).val();

            if((tamanhoLargura1 == 0 || tamanhoLargura1 == "") && $(objVisivel1).is(":checked")) {
                tamInvalida = true;
                return false;
                
            } else {
                
                if($(objVisivel1).is(":checked")) {
                    somaTamanhoLargura += Number(tamanhoLargura1);
                }
                
                $('.chkHabilitar:checked').each( function() {

                    obj2    = $(this);
                    indice2 = $(this).attr("indice");

                    if(indice1 != indice2) {

                        objVisivel2 = $('#fl_visivel_' + indice2);

                        if($(objVisivel1).is(":checked") && $(objVisivel2).is(":checked")) {

                            valorPosicao1 = $('#nr_posicao_' + indice1).val();
                            valorPosicao2 = $('#nr_posicao_' + indice2).val();

                            if(valorPosicao1 == valorPosicao2) {
                                posInvalida = true;
                                return false;
                            }
                            
                            tamanhoLargura2 = $('#tam_largura_' + indice2).val();

                            if((tamanhoLargura2 == 0 || tamanhoLargura2 == "") && $(objVisivel2).is(":checked")) {
                                tamInvalida = true;
                                return false;

                            }

                        }

                    }

                });

            }
            
        });     
       
        var percColunas      = $("#perc_colunas").val();
        var percColunasTela  = $("#perc_colunas_tela").val();
        var totalPercColunas = 0;
       
        if(Number(percColunas) > Number(percColunasTela)) {
            totalPercColunas = Number(percColunas) - Number(percColunasTela);
        }
       
        bExcedeuPerc = false;
        if((Number(somaTamanhoLargura) + Number(totalPercColunas)) > 100) {
            bExcedeuPerc = true;
        }

/*
		var posInvalida = false;
		$(".nrPosicao").each(function(i, o){
			var obj = $(this);
			var val = $(this).val();
			$(".nrPosicao").each(function(i1, o1){
				if ( i != i1) {
					if (val == $(o1).val()){
						alert ('tem igual');
						posInvalida = true;
						return false;
					}
				}
			});
			if (posInvalida == true){
				return false;
			}
		});

		if (posInvalida == true){
			alert ('saiu pq tem igual');
		}
*/
        
        // Valida os dados
        if (bExcedeuPerc == false && habInvalida == false && posInvalida == false && ordInvalida == false && conInvalida == false && tamInvalida == false && bPosCadastrada == false) {
            
            // Faz a validação do formulário
            valida = Valida.formulario('form_base', true);
			
			if (valida[0]) {
                
                $('.chkHabilitar').each( function() {
                    if (! $(this).is(":checked") ){
                        $(this).parent().append('<input type="hidden" name="fl_mostra[]" value="0">');
                    } else {
                        $(this).val("1");
                    }
                });
                
                $('.chkSoma').each( function() {
                    if (! $(this).is(":checked") ){
                        $(this).parent().append('<input type="hidden" name="fl_soma[]" value="0">');
                    } else {
                        $(this).val("1");
                    }	
                });

                $('.chkFiltro').each( function() {
                    if (! $(this).is(":checked") ){
                        $(this).parent().append('<input type="hidden" name="fl_filtro[]" value="0">');
                    } else {
                        $(this).val("1");
                    }	
                });

                $('.chkVisivel').each( function() {
                    if (! $(this).is(":checked") ){
                        $(this).parent().append('<input type="hidden" name="fl_visivel[]" value="0">');
                    } else {
                        $(this).val("1");
                    }
                });
                
                // Submete o formulário
//				$('#form_base').attr({
//					onsubmit: 'return true',
//					action: baseUrlController + '/salvar',
//					target: ''
//				}).submit();
                
			}
			
		} else {
			if (bExcedeuPerc == true){
				Base.montaMensagemSistema(Array('Tamanho total do relatório excedeu em '+valExcedeu+'%. Verifique as informações de "Largura %", inclusive de tabelas já gravadas anteriormente (se for o caso). A soma não deve exceder 100%!'), 'Atenção', 4, "");
			} else if (habInvalida == true) {
				Base.montaMensagemSistema(Array('Selecione no mínimo uma coluna para o relatório.'), 'Atenção', 4, "");
			} else if (posInvalida == true || bPosCadastrada == true) {
				Base.montaMensagemSistema(Array('Não é permitida informações duplicadas, zeradas ou já informadas em outras tabelas no campo "Posição". Verifique!'), 'Atenção', 4, "");
			} else if (ordInvalida == true) {
				Base.montaMensagemSistema(Array('Não é permitida informações duplicadas no campo "Ordenação". Verifique!'), 'Atenção', 4, "");
			} else if (conInvalida == true) {
				Base.montaMensagemSistema(Array('Selecione a conexão entre as tabelas.'), 'Atenção', 4, "");
			} else if (tamInvalida == true) {
				Base.montaMensagemSistema(Array('Há valores zerados no campo Largura(%). Verifique!'), 'Atenção', 4, "");
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
		// Chama a pesquisa da classe form devido 
		// ao erro de sessão com POST
		Form.pesquisar();
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
	},
	
	// Verifica se a ordem de exibição da ação selecionada já existe
	verificaOrdemExibicaoX : function(cd_transacao, ord_acao) {
		
		// Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/verifica-ordem-exibicao-x/"
		, { 'cd_transacao' : cd_transacao, 'ord_acao' : ord_acao } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor e se caso sim, mostra a mensagem na tela
			if(retorno.CD_TRANSACAO != undefined && retorno.CD_TRANSACAO != "") {
				
				var no_acao   = $("#no_acao").val(); 
				var mensagem  = "A transação selecionada, já possui a ação " + no_acao + " vinculada ";
				mensagem     += "ao valor informado como ordem de exibição, informe outro valor para ordenação.";
				
				Base.montaMensagemSistema(Array(mensagem), "Valor já existente", 4);
				
				// Passa para  validação os campos inconsistentes
				Form.adicionaCampoInconsistente($("#ord_acao"));
				
			} else {
				
				// Se estiver setado algum campo inconsistente, remove-os da validação
				Form.removeCampoInconsistente($("#ord_acao"));
			}
			
		} );
		
	},
    
    // Retorna as conexões entre as tabelas
	retornaConexoesTabelas : function() {
		
        var cd_consulta       = $('#cd_consulta').val();
        var cd_tabela         = $('#cd_tabela').val();
        var alias_tabela_nova = $('#alias_tabela_nova').val();
        
        // Se não for a primeira tabela selecionada, mostra as conexões
        if(alias_tabela_nova != "T1") {
        
            // Executa a requisição AJAX com retorno JSON
            $.getJSON(baseUrlController + "/retorna-conexoes-tabelas-x/"
            , { 'cd_consulta' : cd_consulta, 'cd_tabela_nova' : cd_tabela, 'alias_tabela_nova' : alias_tabela_nova } // Seta o parametro a ser passado por ajax	
            , function (retorno){ // Pega o retorno 
                
                var total = $(retorno).size();
                
                // Se retornou conexão
                if(total > 0) {
                    
                    // Desbloqueia o campo
                    $("#fk_tabela_ac").removeAttr("disabled");

                    var options  = "<option label=' -- Selecione -- ' value='_null_'> -- Selecione -- </option>";
                    var selCampo = "_null_";
                    $(retorno).each(function(i, v) {
                        selecionado = "";
                        if(v.EXISTE == 1 || total == 1) {
                            selecionado = "selected='selected'";
                            selCampo    = v.CHAVE;
                            
                            // Seta no campo os valores
                             $("#alias_tabela_nova").val(v.ALIAS_TABELA_NOVA);
                             $("#alias_tabela_ref").val(v.ALIAS_TABELA_REF);
                        }

                        options += "<option label='" + v.VALOR + "' value='" + v.CHAVE + "' alias-tabela-nova='" + v.ALIAS_TABELA_NOVA + "' alias-tabela-ref='" + v.ALIAS_TABELA_REF + "' " + selecionado + ">" + v.VALOR + "</option>";
                    });

                    // Monta o combo
                    $("#fk_tabela").html(options);

                    // Atualiza o combo
                    Form.atualizaCombo($("#fk_tabela"), selCampo);
                    
                }
                
            } );

        } else {
            
            // Limpa o combo
            $("#fk_tabela").html("<option label=' -- Selecione -- ' value='_null_'> -- Selecione -- </option>");
            $("#fk_tabela").val("_null_");
            
            // Bloqueia o campo
            $("#fk_tabela_ac").attr("disabled", "disabled").val(" -- Selecione -- ");
        }
		
	}
    
};