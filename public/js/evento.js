var Eventos = {
	
	// Seta a vari�vel que ir� controlar se algum campo de um
	// formul�rio foi alterado
	idFormularioAlterado  : Array(),
    // Seta o Local onde est� o foco da tela
    idFocoTela : 'Master',
	
	// M�todo principal da classe
	init : function() {
		
		// Fica escutando os campos do formul�rio para 
		// poder setar se foi alterado algo.
		$("input, select, textarea").change(function() {
			// Se for poss�vel salvar grava as altera��es
			// caso contr�rio n�o faz nenhum teste
			if($("#botao-salvar").size()) {
				// Passa para o array o campo alterado
				Eventos.adicionaFormId($(this));
			}			
		});
		
		// Cria novos campos hidden para enviar na requisi��o os campos desativados
		$("form").bind("submit", function() {
			Form.submeteCamposDesabilitados();
		});
	
		// Verifica se foi criada a DIV com a estrutura do MENU
		if ($("#menu-sistemas").size() > 0) {
			// Plugin para montar o MENU dos SISTEMAS
			ddsmoothmenu.init({
		        mainmenuid: "smoothmenu1", //menu DIV id
		        orientation: 'h', //Horizontal or vertical menu: Set to "h" or "v"
		        classname: 'ddsmoothmenu', //class added to menu's outer DIV
		        //customtheme: ["#1c5a80", "#18374a"],
		        contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
			});
			
		}
		
		// Eventos expecificos da botao administrativa
		$("#botao-importar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			// Chama o evento novo
			var tempo = setTimeout("Interna.importar()", 200);
		});
		
		
		// Eventos expecificos da botao administrativa
		$("#botao-novo").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Se o form_base foi alterado verifica se quer que salve o formul�rio
			// antes de executar a opera��o solicitada
			if($(Eventos.idFormularioAlterado).size() > 0) {
				Base.confirm("Deseja Salvar as altera��es?", "ATEN��O", "Interna.salvar();", "Base.mostraTelaCarregando(); Interna.novo();");
			} else {
				// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
				Base.mostraTelaCarregando();
				
				// Chama o evento novo
				var tempo = setTimeout("Interna.novo()", 200);
			}
			
		});
		
		// Bot�o salvar
		$("#botao-salvar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			//eval("try {" + eventoTrue + "} catch(e){ alert('Erro: ' + e); }");
			// Chama o evento salvar
			// O setTimeout � necess�rio para poder possibilitar que 
			// o javascript consiga fazer valida��es em tempo de execu��o 
			// antes de executar a requisi��o do evento.
			var tempo = setTimeout("Interna.salvar()", 300);
							
		});
        
        // Bot�o calcular
		$("#botao-calcular").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			//eval("try {" + eventoTrue + "} catch(e){ alert('Erro: ' + e); }");
			// Chama o evento salvar
			// O setTimeout � necess�rio para poder possibilitar que 
			// o javascript consiga fazer valida��es em tempo de execu��o 
			// antes de executar a requisi��o do evento.
			var tempo = setTimeout("Interna.calcular()", 300);
							
		});
		
		// Bot�o excluir
		$("#botao-excluir").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Pede confirma��o da exclus�o
			Base.confirm("Deseja excluir o registro selecionado?", "ATEN��O", "Base.mostraTelaCarregando(); Interna.excluir();");
		});
		
		$("#botao-pesquisar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			// Mata os campos de ordena��o de colunas caso eles existam
			$("#column").remove();
			$("#orderby").remove();
			
			// Chama o evento pesquisar
			var tempo = setTimeout("Interna.pesquisar();", 200);
		});
		
		$("#botao-tela-pesquisa").bind("click", function() {
		
			// Seta o foco no documento
			$(document).focus();
							
			// Se o form_base foi alterado verifica se quer que salve o formul�rio
			// antes de executar a opera��o solicitada
			if($(Eventos.idFormularioAlterado).size() > 0) {
				
				Base.confirm("Deseja Salvar as altera��es?", "ATEN��O", "Interna.salvar();", "Base.mostraTelaCarregando(); Interna.telaPesquisa();");
			} else {
				// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
				Base.mostraTelaCarregando();
				
				// Chama o evento telaPesquisa
				var tempo = setTimeout("Interna.telaPesquisa()", 200);
			}
			
		});
		
		$("#botao-enviar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			// Chama o evento enviar
			var tempo = setTimeout("Interna.enviar();", 200);
		});
		
		$("#botao-receber").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			// Chama o evento receber
			var tempo = setTimeout("Interna.receber();", 200);
		});
		
		$("#botao-solicitar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			// Chama o evento enviar
			var tempo = setTimeout("Interna.solicitar();", 200);
		});
		
		$("#botao-notificar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			// Chama o evento enviar
			var tempo = setTimeout("Interna.notificar();", 200);
		});
		
		$("#botao-relatorio").bind("click", function() {
			
			
			// Se o form_base foi alterado verifica se quer que salve o formul�rio
			// antes de executar a opera��o solicitada
			if($(Eventos.idFormularioAlterado).size() > 0) {
				Base.confirm("Deseja Salvar as altera��es?", "ATEN��O", "Interna.salvar();", "Eventos.tipoRelatorio();");
				
			} else {
				
				// Chama a tela com os tipos de relat�rio dispon�veis
				Eventos.tipoRelatorio();
				
			}
						
		});
		
		$("#botao-voltar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Se o form_base foi alterado verifica se quer que salve o formul�rio
			// antes de executar a opera��o solicitada
			if($(Eventos.idFormularioAlterado).size() > 0) {
				Base.confirm("Deseja Salvar as altera��es?", "ATEN��O", "Interna.salvar();", "Base.mostraTelaCarregando(); Interna.voltar();");
			} else {
				
				// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
				Base.mostraTelaCarregando();
				
				// Chama o evento telaPesquisa
				var tempo = setTimeout("Interna.voltar()", 200);
			}
		});
		
		$("#botao-limpar").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Chama o evento limpar
			Base.limpar();
		});		
		
		$("#botao-ajuda").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Chama o evento ajuda
			Base.ajuda();
		});
		
		$("#botao-sair").bind("click", function() {
			
			// Seta o foco no documento
			$(document).focus();
			
			// Verifica se tem mais alguma opera��o na fila
			if($(Eventos.idFormularioAlterado).size() > 0) {
				Base.confirm("Deseja Salvar as altera��es?", "ATEN��O", "Interna.salvar();", "Base.mostraTelaCarregando(); Base.sair();");
			} else {
				// Confirma se o usu�rio quer sair do sistema
				Base.confirm("Deseja realmente sair?", "ATEN��O", "Base.mostraTelaCarregando(); Base.sair();");
			}
		});
		
		$("#botao-configuracao").bind("click", function() {
			
			// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
			Base.mostraTelaCarregando();
			
			// Seta o foco no documento
			$(document).focus();
			
			// Chama o evento configura��o
			Base.configuracao();
		});
		
		// teclas de atalho
		shortcut.add("f1", function(e) {
			
			if ($("#botao-ajuda").size() > 0) {
				Eventos.preventDefault(e);
				Interna.ajuda();
			}
			
		}); 
		
		// Chama a tela de novo
		shortcut.add("f2", function(e) {
			
			if ($("#botaoNovo").size() > 0) {
				Eventos.preventDefault(e);
				Interna.novo();
			}
			
		});
		
		// Chama a tela de pesquisa
		shortcut.add("f3", function(e) {
			
			if ($("#botao-pesquisa").size() > 0) {
				Eventos.preventDefault(e);
				Interna.telapesquisa();
			}			
		});
		
		// Quando pressionado F5 N�O Atualiza a p�gina
		shortcut.add("f5", function(e) {
			Eventos.preventDefault(e);				
		});
        
        // Controla o foco da tela quando houver aba
        $("body").on('click', function(event) {
            // N�o seleciona o documento se apertar em algum item da palheta de bot�es
            if($(event.target).closest($("#palhetaControle")).size()    == 0 && 
               $(event.target).closest($("#palhetaControleAba")).size() == 0 && 
               $(event.target).closest($("div.ui-dialog")).size()       == 0 && 
               $(event.target).closest($("#janela-flutuante")).size()   == 0) {
                if (window.frameElement != null) {
                    // Se o iframe selecionado n�o for uma janela flutuante
                    if(! $("#pai").hasClass("janela-flutuante")) {
                        window.parent.Eventos.controlaFocoTela('Detail');
                    }
                } else {
                    Eventos.controlaFocoTela('Master');
                }
            }
        });
        
        // Seta o foco no documento pai ao carregar uma tela que possui aba
        if($("#tabs").size() > 0) {
            Eventos.controlaFocoTela('Master');
            if($("#tabs").size() > 0) {
                // Percorre as abas e seta o foco ao selecionar a tab
                var active = 0;
                var indice = 0;
                $("#tabs_ul li").each(function() {
                    
                    // Se a aba n�o estiver desabilitada
                    if(! $(this).hasClass("ui-state-disabled")) {
                        
                        $(this).find("a").on("click", function() {
                            active = $(this).attr("id");
                            indice = active.replace("tab_", "");
                            var ifrm   = 'ifrm_' + indice;
                            if(Eventos.idFocoTela != ifrm) {
                                Eventos.idFocoTela = ifrm;
                                Abas.adicionaBotoesAba(indice);
                                $("fieldset").attr("style", "box-shadow: 0px 0px 0px 0px rgb(40, 40, 50) !important; opacity: 0.5; filter: alpha(opacity=50);");
                                $("#tabs").attr("style", "box-shadow: 5px 5px 7px -1px rgb(40, 40, 50) !important; opacity: 100; filter: alpha(opacity=100);");
                            }
                        });
                        
                    }
                    
                });
            }
        } else {
            
            // Ao carregar a tela, se estiver na aba chama os botoes da mesma
            if (window.frameElement != null) {
                var idFocoTela = window.parent.Eventos.idFocoTela;
                if(idFocoTela != "Master") {
                    var indice = idFocoTela.replace("ifrm_", "");
                    window.parent.Abas.adicionaBotoesAba(indice);
                }
            }
            
        }
        
	},
    
    // Controla o foco da tela quando houver aba
    controlaFocoTela : function(origem) {
        
        // Se a palheta direita estiver bloqueada, ou estiver aberto uma tela de confirma��o, n�o seleciona a tela
        if($("#palhetaDireita").attr("disabled") != "disabled") {
            
           if(origem == "Master") {
                Eventos.idFocoTela = 'Master';
                Abas.removeBotoesAba();
                
                if($("#tabs").size() > 0) {
                    $("fieldset").attr("style", "box-shadow: 5px 5px 7px -1px rgb(40, 40, 50) !important; opacity: 100; filter: alpha(opacity=100);");
                    $("#tabs").attr("style", "box-shadow: 0px 0px 0px 0px rgb(40, 40, 50) !important; opacity: 0.5; filter: alpha(opacity=50);");
                }
            } else {
                
                if($("#tabs").size() > 0) {
                    var active = $("#tabs_ul li.ui-tabs-selected").find("a").attr("id");
                    var indice = active.replace("tab_", "");
                    var ifrm   = 'ifrm_' + indice;
                    if(Eventos.idFocoTela != ifrm) {
                        Eventos.idFocoTela = ifrm;
                        Abas.adicionaBotoesAba(indice);
                        
                        $("fieldset").attr("style", "box-shadow: 0px 0px 0px 0px rgb(40, 40, 50) !important; opacity: 0.5; filter: alpha(opacity=50);");
                        $("#tabs").attr("style", "box-shadow: 5px 5px 7px -1px rgb(40, 40, 50) !important; opacity: 100; filter: alpha(opacity=100);");
                    }
                }
            }
            
        }
                
    },
    	
	// Chama a tela de escolha do relat�rio
	tipoRelatorio : function() {
		
		// Seta o foco no documento
		$(document).focus();
		
		// Remove a div do dialogo
		$("#dialog").dialog("destroy");
		$("#dialog-confirm").dialog("destroy");
		$("#dialog-confirm").remove();
		
		dialogo  = "";
		dialogo += "<div id='dialog-confirm' title='Relat�rio'>";
		dialogo += "<p><span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 20px 0;'></span>Qual formato de relat�rio voc� deseja gerar?</p>";
		dialogo += "</div>";
		
		// Inclui no corpo da pagina a div mensagem
        $("body").append(dialogo);
		
        // Gera a caixa de mensagem com os tipos de relat�rios
		$( "#dialog-confirm" ).dialog({
			resizable  : false,
			height     : 150,
			modal      : true,
			buttons    : {
				"PDF"  : function() {
					
					// Chama o evento relatorio
					var tempo = setTimeout("Interna.relatorio('pdf')", 200);
					$(this).dialog("close");
				},
				"Excel"  : function() {
					
					// Chama o evento relatorio
					var tempo = setTimeout("Interna.relatorio('xls')", 200);
					$(this).dialog("close");
				},
				Fechar : function() {
					$(this).dialog("close");
				}
			}
		});
		
	},
		
	// Desabilita os eventos de um campo
	desabilitaEventosById : function (id_campo) {
		
		try {			
			// Verifica se o bot�o foi passado
			if(id_campo != undefined) {
				
				if(id_campo != "") {
					
					$("#" + id_campo).removeAttr('onfocus').unbind('focus');
					
					// Eventos via Teclado
					$("#" + id_campo).removeAttr('onblur').unbind('blur');
					$("#" + id_campo).removeAttr('onchange').unbind('change');
					$("#" + id_campo).removeAttr('onkeydown').unbind('keydown');
					$("#" + id_campo).removeAttr('onkeypress').unbind('keypress');
					$("#" + id_campo).removeAttr('onkeyup').unbind('keyup');
										
					// Eventos via MOUSE
					$("#" + id_campo).removeAttr('onclick').unbind('click');
					$("#" + id_campo).removeAttr('ondblclick').unbind('dblclick');
					$("#" + id_campo).removeAttr('onmouseover').unbind('mouseover');
					$("#" + id_campo).removeAttr('onmouseup').unbind('mouseup');
					$("#" + id_campo).removeAttr('onmousedow').unbind('mousedow');
					$("#" + id_campo).removeAttr('onmouseout').unbind('mouseout');
					$("#" + id_campo).removeAttr('onmousemove').unbind('mousemove');
					
					// N�o deixa executar o click do campo
					$("#" + id_campo).bind("click", function(e) {
						Eventos.preventDefault(e);
					});
					
					return $("#" + id_campo);
				}
			}
			
		} catch (e) {
			alert("Erro em Eventos desabilitaEventosById " + e);
		}
		
	},
	
	
	// Adiciona no array o ID do formul�rio passado
	// para controlar se foi algum campo alterado 
	// neste formul�rio
	adicionaFormId : function(campo) {
		// Captura o elemento ancestral informado 
		form = $(campo).closest("form");
		id   = $(form).attr('id');
		
		// Verifica se o formulario n�o esta ainda no array,
		// caso contr�rio, seta ele no array porem nao pode ser um formulario de pesquisa. Isto ir� fazer 
		// com que possa-se saber quais formul�rios foram alterados
		if (id != 'form_pesquisar' && (! $(form).hasClass("nao-controla-alteracao"))) {
			if(jQuery.inArray(id, Eventos.idFormularioAlterado) < 0) {
				Eventos.idFormularioAlterado.push(id);
			}
		}
		
	},
	
	// Remove do array o ID do formul�rio passado
	removeFormId   : function(formId) {
		Eventos.idFormularioAlterado = jQuery.grep(Eventos.idFormularioAlterado, function(valor) {
		    return valor != formId;
		});
	}, 
	
	// Limpar situa��o
	limparForms   : function() {
		
		Eventos.idFormularioAlterado = new Array();		
		
	}, 
	
	preventDefault : function (e) {
		try {
			e.preventDefault();
		} catch(ex){//IE Problemas! 
			e.returnValue=false; 
			e.keyCode=0;
		} 
		return false;
	},
	
	// Chama a url passada
	menuSistemas : function(url, novaJanela) {
		
		// For�a a exist�ncia deste atributo
		if(novaJanela == undefined) {
			novaJanela = 0;
		}
		
		// Verifica se o formul�rio de cadastro est� no array
		if(jQuery.inArray("form_base", Eventos.idFormularioAlterado) >= 0) {
			// Confirma se o usu�rio quer sair da transacao
			if(novaJanela == 0) {
				Base.confirm("Deseja Salvar as altera��es?", "ATEN��O", "Interna.salvar();", "Base.mostraTelaCarregando(); document.location.href = '" + url + "';");
			} else {
				Base.confirm("Deseja Salvar as altera��es?", "ATEN��O", "Interna.salvar();", "window.open('" + url + "', 'nova_janela');");
			}
			
		} else {
			
			// Chama a nova transa��o e verifica se tem que abrir na janela
			// corrente ou em uma nova janela
			if(novaJanela == 0) {
				// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
				Base.mostraTelaCarregando();
				
				// Carrega o documento
				document.location.href = url;
			} else {
				
				// Chama a nova janela
				window.open(url, 'nova_janela');
			}
			
		}
	},
	
	/**
     * Verifica se houve alguma altera��o nos formul�rios dentro dos iframes
     */
    verificaAlteracaoJanelasFilhas : function() {
    	
    	var alterou = false;
    	if($("iframe").size() > 0) {
    		$("iframe").each(function() {
    			id = $(this).attr("id");
    			try {
	    			if(window.document.getElementById(id).contentWindow != undefined) {
	    				if(window.document.getElementById(id).contentWindow.Eventos.idFormularioAlterado.length > 0) {
	    					alterou = true;
	    				}
	    			} else if(window.document.getElementById(id).contentDocument != undefined) {
	    				if(window.document.getElementById(id).contentDocument.Eventos.idFormularioAlterado.length > 0) {
	    					alterou = true;
	    				}
	    			}
    			} catch(e) {
    				//alert("Erro: " + e);
    			}
    		});
    	}
    	
    	return alterou;
    	
    },
    
    /**
     * Retorna o nome das janelas que foram alteradas e n�o foram salvas
     */
    retornaNomeJanelasFilhasAlteradas : function() {
    	
    	var janelas = Array();
    	if($("iframe").size() > 0) {
    		$("iframe").each(function() {
    			id = $(this).attr("id");
    			try {
	    			if(window.document.getElementById(id).contentWindow != undefined) {
	    				if(window.document.getElementById(id).contentWindow.Eventos.idFormularioAlterado.length > 0) {
	    					divIndice = id.split("_");
	        	    		janelas.push($("#tab_" + divIndice[1]).html());
	    				}
	    			} else if(window.document.getElementById(id).contentDocument != undefined) {
	    				if(window.document.getElementById(id).contentDocument.Eventos.idFormularioAlterado.length > 0) {
	    					divIndice = id.split("_");
	    					janelas.push($("#tab_" + divIndice[1]).html());
	    				}
	    			}
    			} catch(e) {
    				//alert("Erro: " + e);
    			}
	    	});
    	}
    	
    	return janelas;
    }
		
};