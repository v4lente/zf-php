//Classe interna, carregada no OnLoad
var Interna = {

	// Método principal da classe
	init : function() {
        
        var cdConsulta = $("#pai_cd_consulta").val();
        
        // Executa a requisição AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-tabelas-consulta-x/"
		, { cd_consulta : cdConsulta } // Seta o parametro a ser passado por ajax 
		, function (retorno){ // Pega o retorno 

            var optionsTabelas = $("#tabelas_origem").html();
            
            for(var i=0; i < retorno.length; i++) {
                
                var linha = "";
                
                linha += "<tr indice='" + i + "'>";
                linha +=    "<td class='no_tabela'><select class='sel_no_tabela required' id='tabelas_" + i + "' name='tabelas[]' disabled='disabled'>" + optionsTabelas + "</select></td>";
                linha +=    "<td class='sg_tabela'><input class='imp_sg_tabela required' type='text' name='sg_tabelas[]' disabled='disabled' id='sg_tabela_" + i + "' /></td>";
                linha +=    "<td><img class='del_tabela' alt='Remove Tabela' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
                linha += "</tr>";
                
                $("#tabelaConsulta tbody").append(linha);
                
                $("#tabelas_" + i).val(retorno[i].cd_tabela);
                $("#sg_tabela_" + i).val(retorno[i].sg_tabela);
                $("#no_tabela_" + i).val(retorno[i].no_tabela);
                
            };
            
            $("select").combobox();
            
            for(var j=0; j < retorno.length; j++) {
                
                // Dispara o evento change do combo
                $("#tabelas_" + j + "_ac").bind( "autocompleteselect", function(event, ui) {
                    
                    if($(this).closest("tr").size() > 0) {
                        var indice = $(this).closest("tr").attr("indice");
                    } else {
                        var indice = 0;
                    }
                    
                    var valorCombo = $(ui.item.option).val();

                    if(valorCombo != "_null_") {
                        var valorSigla = $(ui.item.option).attr("sigla");
                        $("#sg_tabela_" + indice).val(valorSigla);
                    }

                });
            }
            
            Interna.excluir();
            
		});
        
        $(".add_tabela").bind("click", function() {
            
            var optionsTabelas = $("#tabelas_origem").html();
            
            if($("#tabelaConsulta tbody tr").size() > 0) {
                var indice = parseInt($("#tabelaConsulta tbody tr:last").attr("indice")) + 1;
            } else {
                var indice = 0;
            }
            
            var linha = "";

            linha += "<tr indice='" + indice + "'>";
            linha +=    "<td class='no_tabela'><select class='sel_no_tabela required' id='tabelas_" + indice + "' name='tabelas[]'>" + optionsTabelas + "</select></td>";
            linha +=    "<td class='sg_tabela'><input class='imp_sg_tabela required' type='text' name='sg_tabelas[]' id='sg_tabela_" + indice + "' /></td>";
            linha +=    "<td><img class='del_tabela' alt='Remove Tabela' src='" + baseUrl + "/public/images/botoes/16x16_deletar.png' class='del_linha' /></td>";
            linha += "</tr>";

            $("#tabelaConsulta tbody").append(linha);
            
            $("select").combobox();
            
            // Dispara o evento change do combo
            $("#tabelas_" + indice + "_ac").bind( "autocompleteselect", function(event, ui) {
                
                var valorCombo = $(ui.item.option).val();
                
                if(valorCombo != "_null_") {
                    var valorSigla = $(ui.item.option).attr("sigla");
                    $("#sg_tabela_" + indice).val(valorSigla);
                }

            });
            
            Interna.excluir();
            
        });
        
        var controleAtualiza = $("#controleAtualiza").val();
        if(controleAtualiza == 1) {
            window.parent.Interna.salvarY();
        }
        
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
        
        $(".del_tabela").unbind("click");
        $(".del_tabela").bind("click", function() {
            
            var linha  = $(this).closest("tr");
            var indice = $(linha).attr("indice");
            
            var cdTabela = $("#tabelas_" + indice).val();
            var sgTabela = $("#sg_tabela_" + indice).val();
            
            if(cdTabela != "" && sgTabela != "") {
                
                cdTabela = $.trim(cdTabela);
                sgTabela = $.trim(sgTabela);
                
                // Executa a requisição AJAX com retorno JSON
                $.getJSON(baseUrlController + "/excluir/"
                , { cd_consulta : cdConsulta, cd_tabela : cdTabela, sg_tabela : sgTabela } // Seta o parametro a ser passado por ajax 
                , function (retorno){ // Pega o retorno 

                    if(retorno == '1') {
                        Base.montaMensagemSistema(Array("Registro excluido com sucesso."), "SUCESSO", 2);

                        $(linha).remove();
                        
                        window.parent.Interna.salvarY();

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
        
        $(".sel_no_tabela").each(function() {
            if($(this).val() == "_null_") {
                mensagem.push("Nome da Tabela Obrigatório.");
                return false;
            }
        });
        
        var ret = true;
        $(".imp_sg_tabela").each(function() {
            var indice1 = $(this).closest("tr").attr("indice");
            var valor1  = $(this).val().toUpperCase();
            if(valor1 == "") {
                mensagem.push("Sigla da Tabela Obrigatório.");
                ret = false;
                return false;
            } else {
                $(".imp_sg_tabela").each(function() {
                    var indice2 = $(this).closest("tr").attr("indice");
                    var valor2  = $(this).val().toUpperCase();
                    if(indice1 != indice2) {
                        if(valor1 == valor2) {
                            mensagem.push("Não é permitido mais de uma sigla igual.");
                            ret = false;
                            return false;
                        }
                    }
                });
            }
            
            if(ret === false) {
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