//Classe interna, carregada no OnLoad
var Interna = {

	// Método principal da classe
	init : function() {
        
        var cdConsulta = $("#pai_cd_consulta").val();
        
        // Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-ligacoes-tabelas-consulta-x/"
		, { cd_consulta : cdConsulta } // Seta o parametro a ser passado por ajax 
		, function (retorno){ // Pega o retorno 

            var optionsLigTabelas = $("#ligacao_tabelas_origem").html();
            var filtrosColunas    = $("#filtros_origem").html();
            
            for(var i=0; i < retorno.length; i++) {
                
                var linha = "";
                
                linha += "<tr indice='" + i + "'>";
                linha +=    "<td class='coluna_1'><select class='sel_lig_tabela required' id='lig_tabelas1_" + i + "' name='lig_tabelas1[]'>" + optionsLigTabelas + "</select>";
                linha +=    "<input class='imp_sg_tabela_1' type='hidden' name='sg_tabela_1[]' id='sg_tabela_1_" + i + "' value='" + retorno[i].sg_tabela_1 + "' />";
                linha +=    "<input class='imp_cd_coluna_1' type='hidden' name='cd_coluna_1[]' id='cd_coluna_1_" + i + "' value='" + retorno[i].cd_coluna_1 + "' /></td>";
                linha +=    "<td class='ligacao'><select class='sel_ligacao required' id='tp_ligacao_" + i + "' name='tp_ligacao[]'>" + filtrosColunas + "</select></td>";
                linha +=    "<td class='coluna_2'><select class='sel_lig_tabela required' id='lig_tabelas2_" + i + "' name='lig_tabelas2[]'>" + optionsLigTabelas + "</select>";
                linha +=    "<input class='imp_sg_tabela_2' type='hidden' name='sg_tabela_2[]' id='sg_tabela_2_" + i + "' value='" + retorno[i].sg_tabela_2 + "' />";
                linha +=    "<input class='imp_cd_coluna_2' type='hidden' name='cd_coluna_2[]' id='cd_coluna_2_" + i + "' value='" + retorno[i].cd_coluna_2 + "' /></td>";
                linha +=    "<td class='out'><input class='imp_outer_join' type='checkbox' name='outer_join_x[]' id='outer_join_" + i + "' />";
                linha +=    "<input class='imp_ligacao' type='hidden' name='cd_ligacao[]' id='cd_ligacao_" + i + "' value='" + retorno[i].cd_ligacao + "' /></td>";
                linha +=    "<td class='dell'><img class='del_lig_tabela' alt='Remover Ligação' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
                linha += "</tr>";
                
                $("#tabelaConsulta tbody").append(linha);
                
                $("#lig_tabelas1_" + i).val(retorno[i].sg_tabela_1 +"."+ retorno[i].cd_coluna_1);
                $("#tp_ligacao_" + i).val(retorno[i].tp_ligacao);
                $("#lig_tabelas2_" + i).val(retorno[i].sg_tabela_2 +"."+ retorno[i].cd_coluna_2);
                if(retorno[i].outer_join == 1) {
                    $("#outer_join_" + i).val(retorno[i].outer_join).attr("checked", true);
                } else {
                    $("#outer_join_" + i).val(retorno[i].outer_join).attr("checked", false);
                }
                
            };
            
            $("select").combobox();
            
            Interna.excluir();
            
		});
        
        
        $(".add_lig_tabela").bind("click", function() {
            
            var optionsLigTabelas = $("#ligacao_tabelas_origem").html();
            var filtrosColunas    = $("#filtros_origem").html();
            
            if($("#tabelaConsulta tbody tr").size() > 0) {
                var indice = parseInt($("#tabelaConsulta tbody tr:last").attr("indice")) + 1;
            } else {
                var indice = 0;
            }
            
            var linha = "";

            linha += "<tr indice='" + indice + "'>";
            linha +=    "<td class='coluna_1'><select class='sel_lig_tabela required' id='lig_tabelas1_" + indice + "' name='lig_tabelas1[]'>" + optionsLigTabelas + "</select>";
            linha +=    "<input class='imp_sg_tabela_1' type='hidden' name='sg_tabela_1[]' id='sg_tabela_1_" + indice + "' />";
            linha +=    "<input class='imp_cd_coluna_1' type='hidden' name='cd_coluna_1[]' id='cd_coluna_1_" + indice + "' /></td>";
            linha +=    "<td class='ligacao'><select class='sel_ligacao required' id='tp_ligacao_" + indice + "' name='tp_ligacao[]'>" + filtrosColunas + "</select></td>";
            linha +=    "<td class='coluna_2'><select class='sel_lig_tabela required' id='lig_tabelas2_" + indice + "' name='lig_tabelas2[]'>" + optionsLigTabelas + "</select>";
            linha +=    "<input class='imp_sg_tabela_2' type='hidden' name='sg_tabela_2[]' id='sg_tabela_2_" + indice + "' />";
            linha +=    "<input class='imp_cd_coluna_2' type='hidden' name='cd_coluna_2[]' id='cd_coluna_2_" + indice + "' /></td>";
            linha +=    "<td class='out'><input class='imp_outer_join' type='checkbox' name='outer_join_x[]' id='outer_join_" + indice + "' />";
            linha +=    "<input class='imp_ligacao' type='hidden' name='cd_ligacao[]' id='cd_ligacao_" + indice + "' /></td>";
            linha +=    "<td class='dell'><img class='del_lig_tabela' alt='Remover Ligação' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
            linha += "</tr>";

            $("#tabelaConsulta tbody").append(linha);
            
            $("select").combobox();
            
            // Dispara o evento change do combo
            $("#lig_tabelas1_" + indice + "_ac").bind( "autocompleteselect", function(event, ui) {
                
                var valorCombo = $(ui.item.option).val();
                
                if(valorCombo != "_null_") {
                    var valorSigla  = $(ui.item.option).attr("sigla");
                    var valorColuna = $(ui.item.option).attr("coluna");
                    $("#sg_tabela_1_" + indice).val(valorSigla);
                    $("#cd_coluna_1_" + indice).val(valorColuna);
                }

            });
            
            // Dispara o evento change do combo
            $("#lig_tabelas2_" + indice + "_ac").bind( "autocompleteselect", function(event, ui) {
                
                var valorCombo = $(ui.item.option).val();
                
                if(valorCombo != "_null_") {
                    var valorSigla  = $(ui.item.option).attr("sigla");
                    var valorColuna = $(ui.item.option).attr("coluna");
                    $("#sg_tabela_2_" + indice).val(valorSigla);
                    $("#cd_coluna_2_" + indice).val(valorColuna);
                }

            });
            
            Interna.excluir();
            
        });
        
        // Busca as ligações automáticas entre tabelas
        $("#lig_aut").bind("click", function() {
            
            var totalLinhas = parseInt($("#tabelaConsulta tbody tr").size());
            
            if(totalLinhas > 0) {
                
                Base.montaMensagemSistema(Array("Nenhuma ligação poderá ter sido feita para utilizar a automática."), "ALERTA", 4);
                
            } else {

                // Executa a requisição AJAX com retorno JSON
                $.getJSON(baseUrlController + "/busca-ligacoes-automaticas-x/"
                , { cd_consulta : cdConsulta } // Seta o parametro a ser passado por ajax 
                , function (retorno){ // Pega o retorno 

                    var optionsLigTabelas = $("#ligacao_tabelas_origem").html();
                    var filtrosColunas    = $("#filtros_origem").html();

                    for(var i=0; i < retorno.length; i++) {

                        var linha = "";

                        linha += "<tr indice='" + i + "'>";
                        linha +=    "<td class='coluna_1'><select class='sel_lig_tabela required' id='lig_tabelas1_" + i + "' name='lig_tabelas1[]'>" + optionsLigTabelas + "</select>";
                        linha +=    "<input class='imp_sg_tabela_1' type='hidden' name='sg_tabela_1[]' id='sg_tabela_1_" + i + "' value='" + retorno[i].sg_tabela_1 + "' />";
                        linha +=    "<input class='imp_cd_coluna_1' type='hidden' name='cd_coluna_1[]' id='cd_coluna_1_" + i + "' value='" + retorno[i].cd_coluna_1 + "' /></td>";
                        linha +=    "<td class='ligacao'><select class='sel_ligacao required' id='tp_ligacao_" + i + "' name='tp_ligacao[]'>" + filtrosColunas + "</select></td>";
                        linha +=    "<td class='coluna_2'><select class='sel_lig_tabela required' id='lig_tabelas2_" + i + "' name='lig_tabelas2[]'>" + optionsLigTabelas + "</select>";
                        linha +=    "<input class='imp_sg_tabela_2' type='hidden' name='sg_tabela_2[]' id='sg_tabela_2_" + i + "' value='" + retorno[i].sg_tabela_2 + "' />";
                        linha +=    "<input class='imp_cd_coluna_2' type='hidden' name='cd_coluna_2[]' id='cd_coluna_2_" + i + "' value='" + retorno[i].cd_coluna_2 + "' /></td>";
                        linha +=    "<td class='out'><input class='imp_outer_join' type='checkbox' name='outer_join_x[]' id='outer_join_" + i + "' />";
                        linha +=    "<input class='imp_ligacao' type='hidden' name='cd_ligacao[]' id='cd_ligacao_" + i + "' value='' /></td>";
                        linha +=    "<td class='dell'><img class='del_lig_tabela' alt='Remover Ligação' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
                        linha += "</tr>";

                        $("#tabelaConsulta tbody").append(linha);

                        $("#lig_tabelas1_" + i).val(retorno[i].sg_tabela_1 +"."+ retorno[i].cd_coluna_1);
                        $("#tp_ligacao_" + i).val("=");
                        $("#lig_tabelas2_" + i).val(retorno[i].sg_tabela_2 +"."+ retorno[i].cd_coluna_2);

                    };

                    $("select").combobox();

                });

            }
            
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
            $(".imp_outer_join").each(function() {
                if($(this).is(":checked")) {
                    $(this).after("<input type='hidden' name='outer_join[]' value='1' />");
                } else {
                    $(this).after("<input type='hidden' name='outer_join[]' value='0' />");
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
        
        $(".del_lig_tabela").unbind("click");
        $(".del_lig_tabela").bind("click", function() {
            
            var linha  = $(this).closest("tr");
            var indice = $(linha).attr("indice");
            
            var cdLigacao = $("#cd_ligacao_" + indice).val();
            
            if(cdLigacao != "") {
            
                // Executa a requisição AJAX com retorno JSON
                $.getJSON(baseUrlController + "/excluir/"
                , { cd_consulta : cdConsulta, cd_ligacao : cdLigacao } // Seta o parametro a ser passado por ajax 
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
        
        $(".sel_lig_tabela").each(function() {
            if($(this).val() == "_null_") {
                mensagem.push("Coluna Obrigatória.");
                return false;
            }
        });
        
        $(".sel_ligacao").each(function() {
            if($(this).val() == "") {
                mensagem.push("Ligação Obrigatória.");
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