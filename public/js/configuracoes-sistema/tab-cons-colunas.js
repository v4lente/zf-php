//Classe interna, carregada no OnLoad
var Interna = {

	// Método principal da classe
	init : function() {
        
        var cdConsulta = $("#pai_cd_consulta").val();
        
        // Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-colunas-tabelas-consulta-x/"
		, { cd_consulta : cdConsulta } // Seta o parametro a ser passado por ajax 
		, function (retorno){ // Pega o retorno 

            var optionsColTabelas    = $("#coluna_tabelas_origem").html();
            var totalizadoresColunas = $("#totais_origem").html();
            
            for(var i=0; i < retorno.length; i++) {
                
                var linha = "";
                
                var habilitaDistinto = "";
                if(i > 0) {
                    habilitaDistinto = "value='0' disabled='disabled'";
                } else {
                    var indice = 0;
                    habilitaDistinto = "value='1'";
                }
                
                linha += "<tr indice='" + i + "'>";
                linha +=    "<td class='distinto'><input " + habilitaDistinto + " class='imp_distinto' type='checkbox' name='distinto_x[]' id='distinto_" + i + "' /></td>";
                linha +=    "<td class='coluna'><select class='sel_sg_cd_coluna required' id='sg_cd_coluna_" + i + "' name='sg_cd_coluna[]'>" + optionsColTabelas + "</select>";
                linha +=    "<input class='imp_sg_tabela' type='hidden' name='sg_tabela[]' id='sg_tabela_" + i + "' value='" + retorno[i].sg_tabela + "' />";
                linha +=    "<input class='imp_cd_coluna' type='hidden' name='cd_coluna[]' id='cd_coluna_" + i + "' value='" + retorno[i].cd_coluna + "' /></td>";
                linha +=    "<td class='cabecalho'><input class='imp_ds_cabecalho' type='text' name='ds_cabecalho[]' id='ds_cabecalho_" + i + "' value='" + retorno[i].ds_cabecalho + "' /></td>";
                linha +=    "<td class='tamanho'><input class='imp_tam_coluna' type='text' name='tam_coluna[]' id='tam_coluna_" + i + "' value='" + retorno[i].tam_coluna + "' /></td>";
                linha +=    "<td class='total'><select class='sel_cd_tp_total' id='cd_tp_total_" + i + "' name='cd_tp_total[]'>" + totalizadoresColunas + "</select>";
                linha +=    "<input class='imp_cd_col_tab' type='hidden' name='cd_col_tab[]' id='cd_col_tab_" + i + "' value='" + retorno[i].cd_col_tab + "' /></td>";
                linha +=    "<td class='sentido_coluna'><select class='imp_sentido_coluna' id='sentido_coluna_" + i + "' name='sentido_coluna[]'><option value='_null_' label='-- Selecione --' sigla=''>-- Selecione --</option><option label='Ascendente' value='ASC'>Ascendente</option><option label='Descendente' value='DESC'>Descendente</option></select></td>";
                linha +=    "<td class='dell'><img class='del_col_tabela' alt='Remover Coluna' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
                linha += "</tr>";
                
                $("#tabelaConsulta tbody").append(linha);
                
                $("#sg_cd_coluna_" + i).val(retorno[i].sg_tabela +"."+ retorno[i].cd_coluna);
                $("#cd_tp_total_" + i).val(retorno[i].cd_tp_total);
                $("#sentido_coluna_" + i).val(retorno[i].sentido_coluna);
                if(retorno[i].distinto == 1 && i == 0) {
                    $("#distinto_" + i).val(retorno[i].distinto).attr("checked", true);
                } else {
                    $("#distinto_" + i).val("0").attr("checked", false);
                }
                
            };
            
            $("select").combobox();
            
            // Dispara o evento change do combo
            for(var j=0; j < retorno.length; j++) {
                
                $("#sg_cd_coluna_" + j + "_ac").bind( "autocompleteselect", function(event, ui) {

                    var valorCombo = $(ui.item.option).val();

                    if(valorCombo != "_null_") {
                        var valorSigla  = $(ui.item.option).attr("sigla");
                        var valorColuna = $(ui.item.option).attr("coluna");
                        var nomeColuna  = $(ui.item.option).attr("nome");
                        $("#sg_tabela_" + indice).val(valorSigla);
                        $("#cd_coluna_" + indice).val(valorColuna);
                        $("#ds_cabecalho_" + indice).val(nomeColuna);
                    }

                });
                
            }
            Interna.excluir();
            
		});
        
        
        $(".add_col_tabela").bind("click", function() {
            
            var optionsColTabelas    = $("#coluna_tabelas_origem").html();
            var totalizadoresColunas = $("#totais_origem").html();
            
            var habilitaDistinto = "";
            if($("#tabelaConsulta tbody tr").size() > 0) {
                var indice = parseInt($("#tabelaConsulta tbody tr:last").attr("indice")) + 1;
                habilitaDistinto = "value='0' disabled='disabled'";
            } else {
                var indice = 0;
                habilitaDistinto = "value='1'";
            }
            
            var linha = "";
            
            linha += "<tr indice='" + indice + "'>";
            linha +=    "<td class='distinto'><input " + habilitaDistinto + " class='imp_distinto' type='checkbox' name='distinto_x[]' id='distinto_" + indice + "' /></td>";
            linha +=    "<td class='coluna'><select class='sel_sg_cd_coluna required' id='sg_cd_coluna_" + indice + "' name='sg_cd_coluna[]'>" + optionsColTabelas + "</select>";
            linha +=    "<input class='imp_sg_tabela' type='hidden' name='sg_tabela[]' id='sg_tabela_" + indice + "' />";
            linha +=    "<input class='imp_cd_coluna' type='hidden' name='cd_coluna[]' id='cd_coluna_" + indice + "' /></td>";
            linha +=    "<td class='cabecalho'><input class='imp_ds_cabecalho' type='text' name='ds_cabecalho[]' id='ds_cabecalho_" + indice + "' value='' /></td>";
            linha +=    "<td class='tamanho'><input class='imp_tam_coluna' type='text' name='tam_coluna[]' id='tam_coluna_" + indice + "' value='' /></td>";
            linha +=    "<td class='total'><select class='sel_cd_tp_total' id='cd_tp_total_" + indice + "' name='cd_tp_total[]'>" + totalizadoresColunas + "</select>";
            linha +=    "<input class='imp_cd_col_tab' type='hidden' name='cd_col_tab[]' id='cd_col_tab_" + indice + "' value='' /></td>";
            linha +=    "<td class='sentido_coluna'><select class='imp_sentido_coluna' id='sentido_coluna_" + indice + "' name='sentido_coluna[]'><option value='_null_' label='-- Selecione --' sigla=''>-- Selecione --</option><option label='Ascendente' value='ASC'>Ascendente</option><option label='Descendente' value='DESC'>Descendente</option></select></td>";
            linha +=    "<td class='dell'><img class='del_col_tabela' alt='Remover Coluna' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
            linha += "</tr>";

            $("#tabelaConsulta tbody").append(linha);
            
            $("select").combobox();
            
            // Dispara o evento change do combo
            $("#sg_cd_coluna_" + indice + "_ac").bind( "autocompleteselect", function(event, ui) {
                
                var valorCombo = $(ui.item.option).val();
                
                if(valorCombo != "_null_") {
                    var valorSigla  = $(ui.item.option).attr("sigla");
                    var valorColuna = $(ui.item.option).attr("coluna");
                    var nomeColuna  = $(ui.item.option).attr("nome");
                    $("#sg_tabela_" + indice).val(valorSigla);
                    $("#cd_coluna_" + indice).val(valorColuna);
                    $("#ds_cabecalho_" + indice).val(nomeColuna);
                }
            
            });
            
            Interna.excluir();
            
        });
        
	},

	// Gera um novo documento
	novo : function() {
		
	},

	// Salva o registro
	salvar : function() {
        
        // Faz a validação do formulário
		var valida = Interna.validaFormulario();		
		
		// Se validar manda salvar
		if (valida[0]) {
			
            Base.mostraTelaCarregando();
            
            // Seta os checkbox
            $(".imp_distinto").each(function() {
                if($(this).is(":checked")) {
                    $(this).after("<input type='hidden' name='distinto[]' value='1' />");
                } else {
                    $(this).after("<input type='hidden' name='distinto[]' value='0' />");
                }
            });
            				
            $('#form_base').attr({
                onsubmit: 'return true',
                action: baseUrlController + '/salvar',
                target: ''
            }).submit();
			
		}
        
	},

	// Excluir o registro
	excluir : function() {
        
        var cdConsulta = $("#pai_cd_consulta").val();
        
        $(".del_col_tabela").unbind("click");
        $(".del_col_tabela").bind("click", function() {
            
            var linha  = $(this).closest("tr");
            var indice = $(linha).attr("indice");
            
            var cdColunaTabela = $("#cd_col_tab_" + indice).val();
            
            if(cdColunaTabela != "") {
            
                // Executa a requisição AJAX com retorno JSON
                $.getJSON(baseUrlController + "/excluir/"
                , { cd_consulta : cdConsulta, cd_col_tab : cdColunaTabela } // Seta o parametro a ser passado por ajax 
                , function (retorno){ // Pega o retorno 

                    if(retorno == '1') {
                        Base.montaMensagemSistema(Array("Registro excluido com sucesso."), "SUCESSO", 2);

                        $(linha).remove();

                    } else {
                        Base.montaMensagemSistema(Array("Erro ao excluir registro."), "ERRO", 3);
                    }

                });

            } else {
                $(linha).remove();
            }
            
        });
        
    },

	// Chama a tela de pesquisa
	telaPesquisa : function() {
		
	},

	// Executa a tela de pesquisa
	pesquisar : function() {
		
	},

	// Seleciona um registro, recebendo a chave já criptografada
	selecionar : function(chave) {
		
	},

	// Chama a tela de relatórios
	relatorio : function(metodo){
		
	},
	
	// Volta para a listagem
	voltar : function() {
		
	},

	// Mostra a ajuda
	ajuda : function() {
		
	},

	// Fecha o documento
	sair : function() {
		
	},
    
    // Valida alguns dados para cadastrar os dados
	validaFormulario : function() {
		
		// Instancia as variáveis de controle
		var mensagem   = Array();
        
        $(".sel_sg_cd_coluna").each(function() {
            if($(this).val() == "_null_") {
                mensagem.push("Coluna Obrigatória.");
                return false;
            }
        });
        
        $(".imp_tam_coluna").each(function() {
            if($(this).val() == "") {
                mensagem.push("Tamanho Obrigatório.");
                return false;
            }
        });
        				
        // Chama a validação do sistema
        valida = Valida.formulario('form_base', false, mensagem);
        
        if (! valida[0]) {
            Base.montaMensagemSistema(mensagem, "ATENÇÃO", 4);
        }
        
        return valida;
	}
    
};