//Classe interna, carregada no OnLoad
var Interna = {

	// Método principal da classe
	init : function() {
        
        var cdConsulta = $("#pai_cd_consulta").val();
        
        // Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-condicoes-colunas-consulta-x/"
		, { cd_consulta : cdConsulta } // Seta o parametro a ser passado por ajax 
		, function (retorno){ // Pega o retorno 

            var optionsColTabelas = $("#coluna_tabelas_origem").html();
            var filtrosColunas    = $("#filtros_origem").html();
            
            for(var i=0; i < retorno.length; i++) {
                
                var linha = "";
                
                linha += "<tr indice='" + i + "'>";
                linha +=    "<td class='abre_par'><input class='imp_abre_par' type='text' name='abre_par[]' id='abre_par_" + i + "' value='" + retorno[i].abre_par + "' maxlength='5' />";
                linha +=    "<input class='imp_cd_cond_col' type='hidden' name='cd_cond_col[]' id='cd_cond_col_" + i + "' value='" + retorno[i].cd_cond_col + "' /></td>";
                linha +=    "<td class='coluna'><select class='sel_sg_cd_coluna required' id='sg_cd_coluna_" + i + "' name='sg_cd_coluna[]'>" + optionsColTabelas + "</select>";
                linha +=    "<input class='imp_sg_tabela' type='hidden' name='sg_tabela[]' id='sg_tabela_" + i + "' value='" + retorno[i].sg_tabela + "' />";
                linha +=    "<input class='imp_cd_coluna' type='hidden' name='cd_coluna[]' id='cd_coluna_" + i + "' value='" + retorno[i].cd_coluna + "' /></td>";
                linha +=    "<td class='tp_ligacao'><select class='sel_ligacao required' id='tp_ligacao_" + i + "' name='tp_ligacao[]'>" + filtrosColunas + "</select></td>";
                linha +=    "<td class='ds_complemento'><input class='imp_ds_complemento' type='text' name='ds_complemento[]' id='ds_complemento_" + i + "' value='" + retorno[i].ds_complemento + "' /></td>";
                linha +=    "<td class='lig_int_par'><select class='imp_lig_int_par' id='lig_int_par_" + i + "' name='lig_int_par[]'><option value='_null_' label='-- Selecione --' sigla=''>-- Selecione --</option><option label='E' value='AND'>E</option><option label='OU' value='OR'>OU</option></select></td>";
                linha +=    "<td class='fecha_par'><input class='imp_fecha_par' type='text' name='fecha_par[]' id='fecha_par_" + i + "' value='" + retorno[i].fecha_par + "' maxlength='5' /></td>";
                linha +=    "<td class='lig_ext_par'><select class='imp_lig_ext_par' id='lig_ext_par_" + i + "' name='lig_ext_par[]'><option value='_null_' label='-- Selecione --' sigla=''>-- Selecione --</option><option label='E' value='AND'>E</option><option label='OU' value='OR'>OU</option></select></td>";
                linha +=    "<td class='dell'><img class='del_cond_col' alt='Remover Condição' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
                linha += "</tr>";
                
                $("#tabelaConsulta tbody").append(linha);
                
                $("#sg_cd_coluna_" + i).val(retorno[i].sg_tabela +"."+ retorno[i].cd_coluna);
                $("#tp_ligacao_" + i).val(retorno[i].tp_ligacao);
                $("#lig_int_par_" + i).val(retorno[i].lig_int_par);
                $("#lig_ext_par_" + i).val(retorno[i].lig_ext_par);
                                
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
                
                $("#abre_par_" + j).bind("keyup", function() {
                    
                    var total = $(this).val().length;
                    $(this).val("");
                    var abrePar = "";
                    for(k=0; k < total; k++) {
                        abrePar = abrePar + "(";
                    }
                    $(this).val(abrePar);
                });
                
                $("#fecha_par_" + j).bind("keyup", function() {
                    
                    var total = $(this).val().length;
                    $(this).val("");
                    var abrePar = "";
                    for(k=0; k < total; k++) {
                        abrePar = abrePar + ")";
                    }
                    $(this).val(abrePar);
                });
                
            }
            Interna.excluir();
            
		});
        
        
        $(".add_cond_col").bind("click", function() {
            
            var optionsColTabelas = $("#coluna_tabelas_origem").html();
            var filtrosColunas    = $("#filtros_origem").html();
            
            if($("#tabelaConsulta tbody tr").size() > 0) {
                var indice = parseInt($("#tabelaConsulta tbody tr:last").attr("indice")) + 1;
            } else {
                var indice = 0;
            }
            
            var linha = "";

            linha += "<tr indice='" + indice + "'>";
            linha +=    "<td class='abre_par'><input class='imp_abre_par' type='text' name='abre_par[]' id='abre_par_" + indice + "' maxlength='5' />";
            linha +=    "<input class='imp_cd_cond_col' type='hidden' name='cd_cond_col[]' id='cd_cond_col_" + indice + "' value='' /></td>";
            linha +=    "<td class='coluna'><select class='sel_sg_cd_coluna required' id='sg_cd_coluna_" + indice + "' name='sg_cd_coluna[]'>" + optionsColTabelas + "</select>";
            linha +=    "<input class='imp_sg_tabela' type='hidden' name='sg_tabela[]' id='sg_tabela_" + indice + "' />";
            linha +=    "<input class='imp_cd_coluna' type='hidden' name='cd_coluna[]' id='cd_coluna_" + indice + "' /></td>";
            linha +=    "<td class='tp_ligacao'><select class='sel_ligacao required' id='tp_ligacao_" + indice + "' name='tp_ligacao[]'>" + filtrosColunas + "</select></td>";
            linha +=    "<td class='ds_complemento'><input class='imp_ds_complemento' type='text' name='ds_complemento[]' id='ds_complemento_" + indice + "' value='' /></td>";
            linha +=    "<td class='lig_int_par'><select class='imp_lig_int_par' id='lig_int_par_" + indice + "' name='lig_int_par[]'><option value='_null_' label='-- Selecione --' sigla=''>-- Selecione --</option><option label='E' value='AND'>E</option><option label='ou' value='OR'>OU</option></select></td>";
            linha +=    "<td class='fecha_par'><input class='imp_fecha_par' type='text' name='fecha_par[]' id='fecha_par_" + indice + "' maxlength='5' /></td>";
            linha +=    "<td class='lig_ext_par'><select class='imp_lig_ext_par' id='lig_ext_par_" + indice + "' name='lig_ext_par[]'><option value='_null_' label='-- Selecione --' sigla=''>-- Selecione --</option><option label='E' value='AND'>E</option><option label='ou' value='OR'>OU</option></select></td>";
            linha +=    "<td class='dell'><img class='del_cond_col' alt='Remover Condição' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
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
            
            $("#abre_par_" + indice).bind("keyup", function() {
                    
                var total = $(this).val().length;
                $(this).val("");
                var abrePar = "";
                for(k=0; k < total; k++) {
                    abrePar = abrePar + "(";
                }
                $(this).val(abrePar);
            });

            $("#fecha_par_" + indice).bind("keyup", function() {

                var total = $(this).val().length;
                $(this).val("");
                var abrePar = "";
                for(k=0; k < total; k++) {
                    abrePar = abrePar + ")";
                }
                $(this).val(abrePar);
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
        
        $(".del_cond_col").unbind("click");
        $(".del_cond_col").bind("click", function() {
            
            var linha  = $(this).closest("tr");
            var indice = $(linha).attr("indice");
            
            var cdCondColuna = $("#cd_cond_col_" + indice).val();
            
            if(cdCondColuna != "") {
            
                // Executa a requisição AJAX com retorno JSON
                $.getJSON(baseUrlController + "/excluir/"
                , { cd_consulta : cdConsulta, cd_cond_col : cdCondColuna } // Seta o parametro a ser passado por ajax 
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
        
        $(".sel_ligacao").each(function() {
            if($(this).val() == "") {
                mensagem.push("Ligação Obrigatória.");
                return false;
            }
        });
        
        $(".imp_ds_complemento").each(function() {
            if($(this).val() == "") {
                mensagem.push("Complemento Obrigatório.");
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