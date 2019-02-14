// Classe que controla os formularios dos sitema
var Form = {
		
	// Método principal da classe
	init : function() {
			
		// Verifica se o formulário tem o parâmetro submit 
		// para controle do enter sobre os campos. Se possuir 
		// e for falso, não ativa o envio do formulário ao 
		// usuário apertar o enter.
		var acceptSubmit = true;
		if($("#form_pesquisar").attr("accept-submit") != undefined) {
			
			// Recupera o atributo
			acceptSubmit = $("#form_pesquisar").attr("accept-submit");
			
			// Verifica o tipo e retorna um booleano
			switch(acceptSubmit.toLowerCase()) {
	        	case "true" :
	        	case "yes"  :
	        	case "1"    :
	        		acceptSubmit = true; 
	        		break;
	        	
	        	case "false":
	        	case "no"   :
	        	case "0"    :
	        	case null   :
	        		acceptSubmit = false;
	        		break;
	        	
	        	default     :
	        		acceptSubmit = Boolean(acceptSubmit);
			}
			
		}
		
		// Verifica se existe a variavel "operação" vinda do layout
		// e cria um elemento hidden nos formularios
		if($.trim(operacao) != "") {
			$("form").each(function(i, e) {
				$(this).append("<input type='hidden' name='operacao' id='operacao' value='"+operacao+"' />");
			});
		}
		
		// Verifica se existe a variavel "column" vinda do layout
		// e cria um elemento hidden nos formularios
		if($.trim(column) != "") {
			$("form").each(function(i, e) {
				$(this).append("<input type='hidden' name='column'  id='column'  value='"+column+"' />");
				$(this).append("<input type='hidden' name='orderby' id='orderby' value='"+orderby+"' />");
			});
		}
		
		// Controla o evento do ENTER nos formulario de BUSCA
		if(acceptSubmit) {
			
			$("#form_pesquisar input[type='text']").keydown(function(event) {
				
				if (event.which == 13) {
										
					// Verifica se não tem algum campo de auto-completar 
					// com as descrições de retorno aberto
					var autCompAberto = false;
					
					if($("ul.ui-autocomplete").size() > 0) {
						$("ul.ui-autocomplete").each(function() {
							if($(this).css("display") == "block") {
								autCompAberto = true;
							}
							
						});
					}
					
					
					// Se nenhum campo auto-complete estiver aberto dispara o enter
					if(! autCompAberto) {
						
						// Sai do campo antes de disparar a pesquisa
						$(this).blur();
						
						// Mostra a tela de carregando enquanto não for subetido a requisição
						Base.mostraTelaCarregando();
						
						 // Mata os campos de ordenação de colunas caso eles existam
						$("#column").remove();
						$("#orderby").remove();
						
						// Chama o evento pesquisar
						Interna.pesquisar();	
					}
				}
			});
			
		}
	
	    // Pega todos os botões de picklist (com "?") e adiciona o NAME como parametro
	    // da URL da POPUP do botão PICKLIST
	    if ($.trim($('button').html()) == '?') {
	    	
	    	$('button').click(function(){
	    		janelaFilha = window.open(baseUrl + "/default/pick-list/index/picklist_id/"+($(this).attr('name'))+"/reference/"+($(this).attr("reference").replace(/ /g, '')), "PickList", "fullscreen=yes,toolbar=no,location=yes,directories=no,status=no,menubar=no,scrollbars=yes,copyhistory=no,resizable=no");
	    		
	    		if($.inArray(janelaFilha, Base.janelasFilhas) == -1) {
	    			Base.janelasFilhas.push(janelaFilha);		    			
	    		}
				
	    	});
	    }
	
	    //Impede que campos readonly recebam o foco, quando o usuário percorre com o TAB
		$("input, textarea").each(function() {
			if($(this).attr("readonly")) {
				$(this).attr("tabindex", "-1");				
			};
		});
		
        // Chama e cria o combobox
        Form.combobox();
        				
		// Guarda na classe interna os valores iniciais de cada campo do formulário
		// Percorre todos os elementos do formulário
		$(':input').each(function(i, v) {
			try {
				var id = $(this).attr("id");
				if(id != undefined && id != "") {
					var value = $(this).val();
					eval("Interna." + id + " = '" + value + "';");
				}
				
				// Se não existir o atributo type, cria um devido 
				// a atualização de versão do jquery
				if($(this).attr("type") == undefined) {
					$(this).attr("type", $(this).prop("type"));
				}
			} catch(e) {
				//
			}
		});
        
        // Controla todo o campo inserido para adicionar o atributo type
        $(document).bind('DOMNodeInserted', function(e) {
            var element = e.target;
            setTimeout(function() {
                if($(element).attr("type") == undefined) {
					$(element).attr("type", $(element).prop("type"));
				}
            }, 500);
        });
        
	},
    
    combobox : function() {
        
        // Se não foi carregado ainda o combobox, carrega na memória
		if(typeof $.ui.combobox == 'undefined') {
        
            // Altera os campos select
            $.widget( "ui.combobox", {

                _create: function() {

                    var self = this,
                            select    = this.element,
                            selected  = select.children( ":selected" ),
                            value     = selected.val() ? selected.text() : "",
                            newId     = $(select).attr("id")        != "" ? ($(select).attr("id") + "_ac") : "";

                    display = $(select).css("display") == "none" ? "none" : "block";
                    
                    if(display == "block") {

                            var newClass = "";
                            if($(select).hasClass("required")) {
                                var newClass = "required";
                            }
                            //newClass = $(select).attr("class");

                            readonly = ($(select).attr("readonly") == "readonly" || $(select).attr("readonly") == true) ? "readonly" : "";
                            disabled = ($(select).attr("disabled") == "disabled" || $(select).attr("disabled") == true) ? "disabled" : "";

                            // Remove a classe do select principal
                            //$(select).removeAttr("class");

                        // Altera o for do label para apontar pro campo auto-completar
                        $('label[for*="' + ($(select).attr("id")) + '"]').attr("for", newId);

                        // Verifica a posição
                        var position = $(select).position();
                        var diff     = parseInt($(window).height()) - parseInt(position.top);

                        // Position AutoComplete
                        var pac = {my : "left top", at: "left bottom"}; 
                        //if(diff <= 100) {
                        //    pac = {my : "left bottom", at: "left top"};
                        //}

                        // Esconde o campo select
                        $(select).hide();

                        // Cria o elemento que irá substituir os campos "select" e que 
                        // receberão o evento auto-completar
                        var input = $( "<input type='text'>" )
                            .insertAfter(select)
                            .val( value )
                            .attr("name", newId)
                            .attr("id", newId)
                            .keydown(function(e) {
                                if (e.which == 13) {

                                    // Captura o formulario do botão
                                    form = $(this).closest('form');

                                    // Verifica se o formulário é de pesquisa
                                    if($(form).attr("id") == "form_pesquisar") {

                                        // Verifica se a seleção de campo está visivel, se não tiver, 
                                        // faz a busca pelo campo selecionado
                                        if (! $('ul[aria-activedescendant*="ui-active-menuitem"]').is(":visible") && acceptSubmit) {

                                            // Mostra a tela de carregando enquanto não for subetido a requisição
                                            Base.mostraTelaCarregando();

                                            // Chama o evento pesquisar
                                            Interna.pesquisar();																											
                                        }
                                    }							
                                }
                            })
                            .autocomplete({
                                position: pac,
                                delay: 0,
                                minLength: 0,
                                source: function( request, response ) {
                                    var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
                                    response( select.children( "option" ).map(function() {
                                        var text = $( this ).text();
                                        if ( this.value && ( !request.term || matcher.test(text) ) )
                                            return {
                                                label: text.replace(
                                                    new RegExp(
                                                        "(?![^&;]+;)(?!<[^<>]*)(" +
                                                        $.ui.autocomplete.escapeRegex(request.term) +
                                                        ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                                    ), "<strong>$1</strong>" ),
                                                value: text,
                                                option: this
                                            };
                                    }) );
                                },
                                select: function( event, ui ) {

                                    // Verifica se foi alterado ou selecionado algum item
                                    if($(ui.item.option).val() != "") {
                                        if($(ui.item.option).val() != select.val()) {
                                            // Passa para o array o campo alterado 
                                            Eventos.adicionaFormId($(this));
                                        }
                                    }

                                    ui.item.option.selected = true;
                                    self._trigger( "selected", event, {
                                        item: ui.item.option
                                    });
                                    
                                    // Corrige o problema de select multiplo
                                    $(select).val(ui.item.option.value);
                                    
                                    // Chama funções do campo setados na trigger
                                    if($(select).attr("trigger") != undefined) {

                                        $.globalEval(' ' + $(select).attr("trigger") + '; ');
                                        //$(select).change();

                                    }

                                },
                                change: function( event, ui ) {

                                    if ( !ui.item ) {
                                        var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
                                            valid = false;
                                        select.children( "option" ).each(function() {
                                            if ( this.value.match( matcher ) ) {
                                                this.selected = valid = true;
                                                return false;
                                            }
                                        });
                                        
                                        if(! $(select).hasClass("permite-novo-valor")) {
                                            if ( !valid ) {
                                                // remove invalid value, as it didn't match anything
                                                $( this ).val("-- Selecione --");
                                                select.val("_null_");
                                                return false;
                                            }
                                        }
                                        
                                    }
                                }
                            })
                            .addClass("ui-widget");
                            //.addClass($(select).attr("class"));
                        
                        // Monta a lista com os parâmetros buscados
                        input.data( "autocomplete" )._renderItem = function( ul, item ) {
                            return $( "<li></li>" )
                                .data( "item.autocomplete", item )
                                .append( "<a>" + item.label + "</a>" )
                                .appendTo( ul );
                        };

                        // Seleciona todo o conteúdo quando estiver selecionado o _null_
                        $(input).bind("focus", function(e) {
                            if ($(select).val() == "_null_") {
                                $(this).select();
                                $(this).val("");
                            }
                        });

                        // Define o valor padrão caso saia do campo com o mesmo vazio
                        $(input).bind("blur", function(e) {
                            // Concatena o valor para corrigir o trim com objeto vazio
                            if ($.trim($(this).val() + "") == "" || $.trim($(select).val() + "") == "_null_") {
                                if(! $(select).hasClass("permite-novo-valor")) {
                                    Form.atualizaCombo($(select), '');
                                }
                            }
                        });				
                        
                        $(select).addClass("combobox");
                        
                        // Verifica se o campo select foi setado como readonly
                        // caso sim adiciona no input que foi criado readonly = readonly
                        if (readonly == "readonly") {
                            input.attr("readonly", "readonly");
                        };

                        if (disabled == "disabled") {
                            input.attr("disabled", "disabled");
                        };

                        // Insere o botão para o novo campo select
                        var button = $( "<button type='button'>&nbsp;</button>" );
                        button.attr( "tabIndex", -1 )
                            .attr( "title", "Mostrar todos os itens" )
                            .insertAfter( $(input) )
                            .button({
                                icons: {
                                    primary: "ui-icon-triangle-1-s"
                                },
                                text: false
                            })
                            .removeClass( "ui-corner-all" )
                            .addClass( "ui-corner-right ui-button-icon" )
                            .click(function() {

                                // Verifica se o campo não é readonly
                                if(! $(input).attr("readonly") && ! $(input).attr("disabled")){

                                    // close if already visible
                                    if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
                                        input.autocomplete( "close" );
                                        return;
                                    }

                                    // pass empty string as value to search for, displaying all results
                                    input.autocomplete( "search", "" );
                                    input.focus();
                                }

                            });
                    }

                }

            });
		
        }
        
		// Aplica as alterações nos campos selects
		$("select:not(.combobox):not(.original)").combobox();
                
        
    },
	
	// Cria campos hidden de campos desabilitados para poder enviar 
	// no formulário com os demais campos
	submeteCamposDesabilitados : function() {
		
		// Percorre todos os campos desabilitados
		$(":input:disabled").each(function() {
			
			if($(this).attr('no-hidden') == undefined) {
			
				// Captura o nome do campo
				var name  = $(this).attr("name");
				var value = $(this).val();
				
				// Cria o campo escondido de controle para submeter o formulário normalmente quando possuir
				// campos desabilitados na tela
				var novoCampo = "<input type='hidden' name='" + name + "' value='" + value + "'>";
				
				// Insere o campo escondido logo após o campo desabilitado
				$(this).after(novoCampo);
			}
			
		});
		
	},
	
	// Desabilita(disabled) o campo passado
	desabilitaCamposById : function(id_campo, formato) {
		
		// Verifica o formato para setar o atributo
		var attributo = "disabled";
		if (formato != undefined && formato.toLowerCase() == "readonly") {
			attributo = "readonly";
		}
		
		// Verifica se o botão foi passado
		if(id_campo != undefined && id_campo != "") {
			
			// Se for um campo select desabilita o plugin
			if($("#" + id_campo).attr("type") == "select-one") {
				$("#" + id_campo).attr(attributo, true);
				$("#" + id_campo + "_ac").attr(attributo, true);
			} else {
				if($("#" + id_campo).hasClass("data") || $("#" + id_campo).hasClass("datahora")) {
					$("#" + id_campo).attr(attributo, true).datepicker("disable");
				} else {
					$("#" + id_campo).attr(attributo, true);
				}
				
			}
			
			return $("#" + id_campo);
			
		} else {
			
			// Desabilita todos os campos
			$(":input").each(function() {
				
				// Captura o id do campo
				var id_campo = $(this).attr("id");
				var role     = $(this).attr("role");
				
				// Se não possuir ID seta os valores no próprio campo
				// caso contrário valida os selects
				if(id_campo != "" && role != "button") {
					// Se for um campo select desabilita o plugin
					if($(this).attr("type") == "select-one") {
						$(this).attr(attributo, true);
						$("#" + id_campo + "_ac").attr(attributo, true);
					} else {
						if($(this).hasClass("data") || $(this).hasClass("datahora")) {
							$(this).attr(attributo, true).datepicker("disable");
						} else {
							$(this).attr(attributo, true);
						}
					}
										
				}
				
			});
			
			return $(":input");
		}
	},
	
	// Habilita(remove disabled) o campo passado
	habilitaCamposById : function(id_campo) {
		
		// Verifica se o botão foi passado
		if(id_campo != undefined && id_campo != "") {
			
			// Se for um campo select habilita o plugin
			if($("#" + id_campo).attr("type") == "select-one") {
				$("#" + id_campo).attr("readonly", false).attr("disabled", false);
				$("#" + id_campo + "_ac").attr("readonly", false).attr("disabled", false);
			} else {
				if($("#" + id_campo).hasClass("data") || $("#" + id_campo).hasClass("datahora")) {
					$("#" + id_campo).attr("readonly", false).attr("disabled", false).datepicker("enable");
				} else {
					$("#" + id_campo).attr("readonly", false).attr("disabled", false);
				}
			}
			
			return $("#" + id_campo);
			
		} else {
			
			// Desabilita todos os campos
			$(":input").each(function() {
				
				// Captura o id do campo
				var id_campo = $(this).attr("id");
				
				// Se não possuir ID seta os valores no próprio campo
				// caso contrário valida os selects
				if(id_campo != "") {
					// Se for um campo select habilita o plugin
					if($(this).attr("type") == "select-one") {
						$(this).attr("readonly", false).attr("disabled", false);
						$("#" + id_campo + "_ac").attr("readonly", false).attr("disabled", false);
					} else {
						if($(this).hasClass("data") || $(this).hasClass("datahora")) {
							$(this).attr("readonly", false).attr("disabled", false).datepicker("enable");
						} else {
							$(this).attr("readonly", false).attr("disabled", false);
						}
					}
					
				} else {
					$(this).attr("readonly", false).attr("disabled", false).datepicker("enable");
				}
				
			});
			
			return $(":input");
		}
	},
	
	// Desabilita (esconde) o botão passado
	desabilitaBotoesById : function(id_botao) {
		// Verifica se o botão foi passado
		if(id_botao != undefined) {
			
			if(id_botao != "") {
				// Desabilita o botão passado
				$("#" + id_botao).css("display", "none");				
			}
			
		} else {
			// Desabilita todos os botões
			$("div.palhetaBotoes, div.palhetaBotoesAba").each(function() {
				$(this).css("display", "none");
			});
		}
	},
    
	// Habilita (mostra) o botão passado
	habilitaBotoesById : function(id_botao) {
		// Verifica se o botão foi passado
		if(id_botao != undefined) {
			
			if(id_botao != "") {
				// Desabilita o botão passado
				$("#" + id_botao).css("display", "block");				
			}
			
		} else {
			// Desabilita todos os botões
			$("div.palhetaBotoes , div.palhetaBotoesAba").each(function() {
				$(this).css("display", "block");
			});
		}
	},
    
    // Fixa um botão da Master na tela para que não suma ao selecionar uma aba
    fixaBotoesTelaById : function(id_botao) {
        
        // Verifica se o botão foi passado
		if(id_botao != undefined) {
			
                    if(id_botao != "") {
                            // Habilita o botão passado
                    $("#" + id_botao).appendTo("#botoesFixos");
                    $("#" + id_botao).css("display", "block");
                    }
			
		} else {
            
                // Seta todos os botões como fixo na tela
                $(".palhetaBotoes").each(function() {
                    if($(this).css("display") != "none") {
                        // Habilita o botão passado
                        $("#" + $(this).attr("id")).appendTo("#botoesFixos");
                    }
                });
            
                }
        
    },
	
	// Seta o valor de um combo e atualiza o campo autocomplete
	// com o valor selecionado do combo
	atualizaCombo : function (objCombo, valor) {

		// Captura o id do campo e inicializa os atributos
		var id            = $(objCombo).attr("id");
		var valorLabel    = "";
		var valorSelected = "";
		var existeValor   = false;
		
		// Verifica se o valor passado não é vazio.
		// Caso seja vazio, seta o primeiro valor do combo 
		// no campo autocomplete		
		if (valor != undefined && valor != "") {
			
			// Percorre todos os valores do combo hidden
			$("#" + id + ">option").each(function(i, obj) {
				
				// Se o valor existir seta-o como selecionado
				if($.trim($(obj).val()) == $.trim(valor)) {
					$(objCombo).val(valor);
					valorLabel = $("#" + id + ">option")[i].label;
					existeValor = true;					
				}
			});
			
			// Busca o valor e se existir no combo, seta
			// o mesmo no campo autocomplete
			if(existeValor) {
				$("#" + id + "_ac").val(valorLabel);
				
			} else {
				// Verifica se o combo contém alguma opção
				if($("#" + id + ">option").size() > 0) {
					// Captura o primeiro valor do combo e define-o como selecionado
					valorSelected = $("#" + id + ">option")[0].value;
					$(objCombo).val(valorSelected);				
					
					// Joga o valor do combo no campo autocomplete
					valorLabel = $("#" + id + ">option").attr("label");
					$("#" + id + "_ac").val(valorLabel);
				}
			}
			
		} else {
			// Verifica se o combo contém alguma opção
			if($("#" + id + ">option").size() > 0) {
				// Captura o primeiro valor do combo e define-o como selecionado
				valorSelected = $("#" + id + ">option")[0].value;
				$(objCombo).val(valorSelected);
				
				// Joga o valor do combo no campo autocomplete
				valorLabel = $("#" + id + ">option").attr("label");
				$("#" + id + "_ac").val(valorLabel);
			}
		} 
		
		return $(objCombo);
	},
	
	// Pega o retorno do banco e monta os options do combo	 
	montaCombo : function (combo, valor, campos) {
		
		var options = "<option label=' -- Selecione -- ' value='_null_'> -- Selecione -- </option>";
	
		$(valor).each(function(i, v) {
            if(campos[2] != undefined) {
                eval("options += \"<option label='\" + v." + campos[1] + " + \"' value='\" + v." + campos[0] + " + \"' param-extra='\" + v." + campos[2] + " + \"'>\" + v." + campos[1] + " + \"</option>\";");
            } else {
			eval("options += \"<option label='\" + v." + campos[1] + " + \"' value='\" + v." + campos[0] + " + \"'>\" + v." + campos[1] + " + \"</option>\";");
            }
		});	
		
		$(combo).html(options);
        
        Form.atualizaCombo($(combo), "_null_");
        
	},
	
	// Adiciona no campo passado o atributo de inconsistência 
	adicionaCampoInconsistente : function(obj, title) {
		if(title == undefined) {
			title = "";
		}
		
		$(obj).attr("inconsistent", title);
	},
	
	// Remove do campo passado o atributo de inconsistência 
	removeCampoInconsistente : function(obj) {
		$(obj).removeAttr("inconsistent");
	},
	
	// Remove os acentos de um campo e retorna o valor se acento
	retirarAcento : function (obj) {  
		var varString       = new String($(obj).val());
		var stringAcentos   = new String('àâêôûãõáéíóúçüÀÂÊÔÛÃÕÁÉÍÓÚÇÜ');  
		var stringSemAcento = new String('aaeouaoaeioucuAAEOUAOAEIOUCU');  
		  
		var i = new Number();  
		var j = new Number();  
		var cString = new String();  
		var varRes = '';  
		  
		for (i = 0; i < varString.length; i++) {  
			cString = varString.substring(i, i + 1);  
			for (j = 0; j < stringAcentos.length; j++) {  
				if (stringAcentos.substring(j, j + 1) == cString){  
					cString = stringSemAcento.substring(j, j + 1);  
				}  
			}
			
			varRes += cString;  
		}  
		
		// Retorna o valor sem acento
		return varRes;  
	},
	
	// Copia os valores que estão na classe Interna para
	// os campos do formulário. Os valores da classe são 
	// gravados no momento em que a página é carregada.
	atualizaCamposPesquisarValorOrigem : function() {
		
		if($("#form_pesquisar").size() > 0) {
			
			// Percorre os campos e atualiza os valores
			$(":input, select, textarea").each(function(i, obj) {
				
				// Pega o nome do campo para atualizar dinamicamente
				var name = $(this).attr("name");
				
				// Se possuir name
				if(name != "") {
					try {
						// Atualiza o campo do formulário
						eval("jQuery('#" + name + "').val(Interna." + name + ");");
					} catch(e) {
						//e
					}
				}
				
			});
			
		}
	}
	
};