// Classe principal que será carregada em todas as páginas e inicializa 
// cada classe interna, facilitando a chamada das outras classes e 
// carregando as funções que serão utilizados em todas as páginas automáticamente

// Verifica se o dispositivo que está acessando o PORTO WEB é móvel ou não. Se for, redireciona para o mobile.

//var userAgent = navigator.userAgent.toLowerCase();
//if( userAgent.search(/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|up\.browser|up\.link|webos|wos)/i)!= -1 ){
//	location.href='http://www.portoriogrande.com.br/portoweb/zf-mobile';
//}

var Base = {
	
	// Controla a altura e tamanho da window e do document
	wH : 0,
	wW : 0,
	dH : 0,
	dW : 0,
	
	// Guarda a referência da janela pai caso exista
	janelaPai : null,
	
	// Guardas as janelas filhas da transação principal
	janelasFilhas : Array(),
	
	// Mostra a proteção da tela por padrão
	mostraProtecaoTela  : true,	
	escondeProtecaoTela : true,
	
	// Guarda a referencia do iframe que chamou o evento	
	idIframeOrigemEvento : "",
	
	// Define se o foco será ou não setado automaticamente
	setaFocoAutomatico : false,
	
	// Método principal da classe
	init : function() {

		try {

			/*Criado para "simular" o focus do css no IE7*/
			if ((jQuery.browser.msie === true) && (jQuery.browser.version == '7.0')) {
				$(":input:not([readonly])")
				 	.bind('focus', function() {
						$(this).addClass('ieFocusHack');
					}).bind('blur', function() {
						$(this).removeClass('ieFocusHack');
				});
			}	
			// Chama o autoloader do ajax
			Base.ajaxLoader();
			
			Base.escondeTelaCarregando();
									
			// Controla os eventos do teclando quando a tela de carregando estiver aberta
			$(":input").bind("keypress", function(e){				
				if ($("#carregando").css("display") == "block") {
					e.preventDefault();
				}
			});
			
		    //Carrega tooltipo em elementos que contém o atributo 'title'.
			try {
				$("img").each(function() {
					if($(this).attr("alt") != undefined) {
						$(this).attr("title", $(this).attr("alt"));
					}
				});
				
				$("[title]").tooltip({ showURL: false, positionLeft: true });
			} catch(e) {
				// alert(e)
			}
						
			// Altera algumas classes do tema do UI
		    $( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
		        .find( ".portlet-header" )
		            .addClass( "ui-widget-header ui-corner-all" )
		            .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
		            .end()
		        .find( ".portlet-content" );

		    $( ".portlet-header .ui-icon" ).click(function() {
		        $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
		        $( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
		    });

		    // Se a action for selecionar 
		    // e contiver abas na tela, mostra 
		    // o botão de recolher a tela para 
		    // liberar espaço ao trabalhar nas abas
		    if(no_action == "selecionar" && $("div").hasClass("tabs")) {
		    	var divSeta = "";
		    	var indiceFieldset = 1;
		    	$("fieldset[class!='fieldset-filho']").each(function() {
		    		
		    		divSeta  = "<div style='width:99%; position:relative; border: 0px solid #44f; margin-top: 15px; margin-bottom: 20px; text-align: center;'>";
			    	divSeta += "	<div style='display: table-cell; position: absolute; left: 49.6%; border: 0px solid #f44;'>";
			    	divSeta += "		<img id='seta-" + indiceFieldset + "' class='seta_cima' onclick='Base.controleSeta(" + indiceFieldset + ");' style='cursor: pointer;' src='" + baseUrl + "/public/images/botoes/seta_cima.png' />";
			    	divSeta += "	</div>";
			    	divSeta += "</div>";
		    		
		    		// Seta o id do fieldset
		    		$(this).attr("id", "fs-" + indiceFieldset);
		    		
		    		// Adiciona o botão no fieldset
		    		$(divSeta).appendTo($(this));
		    		
		    		// Incrementa o indice
		    		indiceFieldset += 1;
		    	});
		    	
		    	//$(divSeta).appendTo("fieldset[class!='fieldset-filho']");
		    }
			
		    // Seta o foco no primeiro elemento não disabled e não readonly que estejam visible
		    var _contador = 0;
		    if(this.setaFocoAutomatico == true) {
			    $('form:first input, textarea, select').each(function() {
					if( $(this).is(':visible') && $(this).attr('disabled') == false && $(this).attr('readonly') == false) {
				      _contador++;
	
				      if(_contador == 1) {
				        $(this).focus();
				      }
				    }
				});
		    }
		    
		    // Adiciona a div clear:both em cada linha automaticamente
		    $("div.linha").each(function(i, obj) {
		    	// Verifica se a linha possui a classe no-clear, 
		    	// não coloca a div caso possua.
		    	if(! $(this).hasClass("no-clear")) {
		    		$(obj).append("<div class='clear'></div>");
		    	}
		    });
		   
		    // Corrige o tamanho dos elementos na tela
			Base.controlaTamanhoTela();
			
			// DRAG AJUDA
			try {
				if($("#drag_ajuda_editor").size()) {
					$('#drag_ajuda_editor').dragResize({grid:20});
					$('.drag_ajuda_block').hide();
					$('#drag_ajuda_editor').height(Base.wH);
					$('#drag_ajuda_editor').width(Base.wW);
					var margem_esq = ( $('#drag_ajuda_editor').width() - 750) /2;
					$('.drag_ajuda_block').css({'left':margem_esq,'top':'0'});
				}
			} catch(e) {
				// alert(e)
			}
			
			// Se for UMA ABA
			if (window.frameElement != null) {
				
				// Cria um alias para a janela pai
				this.janelaPai = window.parent;
				
				// Controla o tamanho da ABA 
				window.parent.Base.controlaTamanhoAba();
                
                // Se for chamado uma tela mestre dentro de uma detail.
                //if(typeof(fl_tp_tran) != "undefined" && fl_tp_tran == "M") {
                //    document.location.href = site + 'default/menu/index';
                //}
                
			} else {
                // Se for chamado diretamente uma janela filha na Tela Master, deve sair para o menu.
                if(typeof(fl_tp_tran) != "undefined" && fl_tp_tran == "D") {
                    document.location.href = site + 'default/menu/index';
                }
            }
            
            // Se não existir nenhum botão de manutenção desabilita o formulário
            if($("#palhetaDireita").size() > 0 || $("#palhetaDireitaAba").size() > 0) {
                if(fl_perm == 'C') {
                    $("#form_base").find(":input").attr("disabled", "disabled");
                }
            }
            
		} catch(e) {
			// trata o erro aqui
			alert("[Base.init()] " + e);
		}
	},
    
    // Reajusta o tamanho da tela para posicionar as divs corretamente
    controlaTamanhoTela : function() {
    	try {    		
    		
    		if (window.frameElement != null) {
        		// Chama o evento da janela pai
    			window.parent.Base.controlaTamanhoTela();
    			
        	} else {
    	    	
               // Corrige o tamanho dos elementos na tela
                Base.wH = $(window).height();
                Base.wW = $(window).width();
                Base.dH = $(document).height();
                Base.dW = $(document).width();
                var  h2 = 0;

                // Define o tamanho da div carregando
                $("#carregando").css("height", Base.dH+"px");
                $("#carregando").css("width",  Base.dW+"px");

                // Define o tamanho da div carregando
                $("#protecao_tela").css("height", Base.dH+"px");
                $("#protecao_tela").css("width",  (Base.dW+20)+"px");

                // Posiciona a imagem de carregando
                $("#carregando-img").css("margin-top",  (Base.wH/2)+"px");
                $("#carregando-img").css("margin-left", (Base.wW/2)+"px");

                // Se não for UMA ABA
                if (window.frameElement == null) {
                    if (Base.wH >= Base.dH && no_controller != "login") {

                        var mheight = "min-height";
                        if($.browser.msie) {
                            mheight = "height";
                        }

                        $("body").css(mheight, Base.dH + "px");
                        if($("#palhetaControle").css("display") != "none") {
                            h2 = parseInt(Base.dH, 10) - parseInt(49, 10);
                            $("#container").css(mheight, h2 + "px");

                            h2 = parseInt(Base.dH, 10) - parseInt(140, 10);
                            $("#content").css(mheight, h2 + "px");
                            $("#iMenu").css(mheight, h2 + "px");

                        } else {
                            $("#container").css(mheight, Base.dH + "px");

                            h2 = parseInt(Base.dH, 10) - parseInt(65, 10);
                            $("#content").css(mheight, h2 + "px");
                            $("#iMenu").css(mheight, h2 + "px");
                        }
                    }
                    
                    if($("#iMenu").size() > 0) {
                        $("#iMenu").css("display", "block");
                    }
                }
        		
        	}
    	} catch(e){
    		//alert("Erro: " + e);
    	}    	
    },

	// Mostra a tela de configuracao
	configuracao : function() {
		// Chama a tela de configuração
		document.location.href = baseUrl + "/configuracoes-sistema/configuracao/index/"+$.base64.encode("_cd_sistema/10/_cd_menu/41/_cd_transacao/207");
	},

	// Mostra a ajuda
	ajuda : function() {

		// Executa a requisição AJAX que retorna os botões do legado
		$.getJSON(baseUrl + "/default/menu/retorna-noarqajuda-x/"
		, { "cd_transacao": cd_transacao } // Seta o parametro a ser passado por ajax
		, function (retorno){ // Pega o retorno

			if ($.trim(retorno) == "") {
				//retorno = "sem.pdf";
				Base.montaMensagemSistema(Array("Documento sem ajuda!"), "ATENÇÃO", 4);
			}else{

				var url = baseUrl + "/public/ajuda/" + retorno;				
				window.open(url);	
				
			}
		});			
	},
	
	// Voltar a tela
    voltar: function() {
        history.go(-1);
    },

    // Limpa os form
    limpar: function() {
    	
    	// Percorre todos os elementos do formulario e limpa
    	$("form").find(':input').each(function(){
    		if($(this).attr("readonly") != true && $(this).attr("readonly") != "readonly" && $(this).attr("disabled") != true && $(this).attr("disabled") != "disabled") {
	    		switch(this.type) {
		    		case 'password':
		    		case 'select-multiple':
		    		case 'select-one':
		    		case 'text':
		    		case 'file':	
		    		case 'textarea':
		    			$(this).val('');
		    			break;
		    		case 'checkbox':
		    		case 'radio':
		    			this.checked = false;
	    		}
    		}
    	});
    	
    },

    // Fecha o documento
	sair : function(msg) {
		
		if (msg == undefined) {
			msg = "";
		}
		// Fecha a janela corrente
	    document.location.href = baseUrl + "/login/sair/msg-sessao/"+ msg;

			
	},

    // Seta o foco e preenche com o valor do retorno no elemento de referencia da picklist e executa o evento onblur
    retornoPickList: function(references) {

		// Divide as referências e seus valores de retorno
		divRef = references.split("&&");
		
		// Instancia as variáveis
		var campoSql		= "";
		var campoForm		= "";
		var nomeCampo	    = "";
		var valorCampoAtual = "";
    	var valorCampoNovo  = "";
		
		// Percorre cada referencia passada
		$(divRef).each(function(i, v) {
			
			// Separa a referencia (campo da consulta = campo do formulário) do valor
			divVal = v.split(";;");
			
			// Verifica se foi passado a "seta (->)" no campo, definindo 
			// qual input do formulário receberá o valor retornado. Geralmente 
			// o nome do campo do formulário é o mesmo do campo da consulta, 
			// retornando assim o valor automáticamente para o campo com 
			// o mesmo nome. Se o valor de retorno contiver a "seta", indica 
			// que o campo do formulário que receberá o valor será outro 
			// diferente da consulta.
			if(divVal[0].search(/->/i) != -1) {
				divCampo  = divVal[0].split("->");
				campoSql  = $.trim(divCampo[0]);
				campoForm = $.trim(divCampo[1]);
				
			} else {
				campoSql  = $.trim(divVal[0]);
				campoForm = campoSql;
			}
						
			// A primeira referência é setada o foco e lançado o blur sobre ela
			if(i==0) {
				
				// Captura o nome do primeiro campo
				nomeCampo = campoForm;
				
				// Captura o valor do campo
				valorCampoAtual = $("#" + nomeCampo).val();
				
				// Guarda o valor novo do campo
		    	valorCampoNovo = $.base64.decode(divVal[1]);
		    	
		    	// Seta o foco no primeiro campo
				$("#" + nomeCampo).focus();
			}
			
			// Decodifica o valor na base 64
			valor = $.base64.decode(divVal[1]);
			
			// Joga cada valor em seu devido campo
	    	$("#" + campoForm).val(valor);
	    	
		});
		
		// Se foi alterado o valor do campo
		if(valorCampoAtual != valorCampoNovo) {
			
			// Espera preencher o campo com valor e após 1/10 de segundo 
	    	// executa o evento BLUR
	    	window.setTimeout(function() {
	    		
	    		$("#" + nomeCampo).blur(function() {
	    			
    				// Passa para o array o campo alterado 
    				Eventos.adicionaFormId($("#" + nomeCampo));
    				
	    		}).blur();
	    	}, 500);
		}
		
    },
    
	/**
	 * Metodo que gera uma mesagem condicional
	 * 
	 * @param string mensagem
	 * 
	 * @returns string (pop up)
	 */
	confirm : function (mensagem, titulo, eventoTrue, eventoFalse) {
		
		if (window.frameElement != null) {
			
			// Guarda a referencia da origem do iframe que quer executar o evento
			window.parent.Base.idIframeOrigemEvento = window.frameElement.id;
			
			// Chama a confirmação da janela pai
			window.parent.Base.confirm(mensagem, titulo, eventoTrue, eventoFalse);
			
		} else {
			
			// Remove a div do corpo da pagina
			$("#dialog-confirm").remove();
			
			// Monta a estrutura da mensagem
			mensagem = "<div id='dialog-confirm' title='"+titulo+"'> " +
					   "	<p class='dialog-confirm-p' style='margin: 0px; padding: 2px; text-align: left;'> " +
					   " 		<span class='ui-icon ui-icon-alert' style='float:left; margin:0px; margin-right: 10px;'></span> " +
					   				mensagem + 	
				       "	</p> " +
				       "</div>";
			
			// Insera a mensagem na pagina
			$("body").append(mensagem);
			
			// Chama o plugin dialog
			$("#dialog-confirm").dialog({
				modal: true,
				close: function(event, ui) {
	        		$('.ui-dialog-titlebar').css({background: 'none'});
	        		$('.ui-dialog-titlebar #ui-dialog-title-dialog-message').css({color: '#444'});        		
	        	},
				buttons: {
					'Confirmar': function() {
						$(this).dialog('close');
						
						try {
							
							Base.escondeTelaCarregando();
							
							// Executa o evento passado
							if(eventoTrue != undefined && eventoTrue != "") {
								
								// Caso a chamada seja de um Iframe
								if (Base.idIframeOrigemEvento != "") {
									
									// Caso encontre uma chamada da classe interna, 
									// concatena a chamada	da origem do evento
									if (eventoTrue.indexOf("Interna.") >= 0) {
										if(window.document.getElementById(Base.idIframeOrigemEvento).contentWindow != undefined) {
											eventoTrue = eventoTrue.replace(/Interna\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentWindow.Interna.");
										} else {
											eventoTrue = eventoTrue.replace(/Interna\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentDocument.Interna.");
										}
									}
									
									if (eventoTrue.indexOf("Form.") >= 0) {
										if(window.document.getElementById(Base.idIframeOrigemEvento).contentWindow != undefined) {
											eventoTrue = eventoTrue.replace(/Form\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentWindow.Form.");
										} else {
											eventoTrue = eventoTrue.replace(/Form\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentDocument.Form.");
										}
									}
								}
								
								// Executa o evento
								eval("try {" + eventoTrue + "} catch(e){ alert('Erro: ' + e); }");
								
							}
						} catch(e) {
							//alert(e);
						}
					},
					'Cancelar': function() {					
						
						$(this).dialog('close');
						
						try {
							
							Base.escondeTelaCarregando();
							
							// Executa o evento passado
							if(eventoFalse != undefined && eventoFalse != "") {
								
								// Caso a chamada seja de um Iframe
								if (Base.idIframeOrigemEvento != "") {
									
									// Caso encontre uma chamada da classe interna, 
									// concatena a chamada	da origem do evento
									if (eventoFalse.indexOf("Interna.") >= 0) {
										if(window.document.getElementById(Base.idIframeOrigemEvento).contentWindow != undefined) {
											eventoFalse = eventoFalse.replace(/Interna\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentWindow.Interna.");
										} else {
											eventoFalse = eventoFalse.replace(/Interna\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentDocument.Interna.");
										}
										
									}
									
									if (eventoFalse.indexOf("Form.") >= 0) {
										if(window.document.getElementById(Base.idIframeOrigemEvento).contentWindow != undefined) {
											eventoFalse = eventoFalse.replace(/Form\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentWindow.Form.");
										} else {
											eventoFalse = eventoFalse.replace(/Form\./g, "window.document.getElementById('" + (Base.idIframeOrigemEvento) + "').contentDocument.Form.");
										}
										
									}
									
								}
								
								// Executa o evento
								eval("try {" + eventoFalse + "} catch(e){ alert('Erro: ' + e); }");
								
								// Limpa a referencia da origem do iframe 
								Base.idIframeOrigemEvento = "";
							}
							
						} catch(e) {				
							//alert(e);
						}
					}
				}
			});
			
			// Troca a cor do titulo em função do "TIPO"
		    url = 'url(' + baseUrl + '/public/images/ui-bg_highlight-soft_15_1x100_amarelo.png)';        
		    $(".ui-dialog-titlebar").css({backgroundImage: url, backgroundRepeat: 'repeat-x', backgroundColor: '#444'});
			
		}
	},

	/**
	 * Função para mostrar o iframe com o arquivo do sistema antigo
	 * 
	 * @param string arquivo
	 */
	mostraLegado : function(arquivo) {
		
		$("div#content").css("display","none").html("");
		$("div#palhetaDireita").css("display","none").html("");
		
		//tituloForm
		
		$("#iMenu").attr("src", arquivo);
		$("#iMenu").css("display", "block");
		
		
	},
	
	/**
	 * Faz o controle da seta, que mostrará ou escoderá o 
	 * conteúdo do formulário base, caso possua abas na tela
	 * 
	 * @returns void
	 */
	controleSeta : function(indiceId) {
		
		if(no_action == "selecionar") {
						
			if($("#seta-" + indiceId).hasClass("seta_cima")) {
				$("#seta-" + indiceId).attr("src",   baseUrl + "/public/images/botoes/seta_baixo.png");
				$("#seta-" + indiceId).attr("class", "seta_baixo");
				
				$("#fs-"+indiceId+" div.linha").each(function() {
					$(this).hide("blind", function() {
						// Controla o tamanho da ABA 
						Base.controlaTamanhoAba();
					}, 500);
				});
				
				$("#fs-"+indiceId+" table").each(function() {
					$(this).hide("blind", function() {
						// Controla o tamanho da ABA 
						Base.controlaTamanhoAba();
					}, 500);
				});
				
				$("#fs-"+indiceId+" fieldset.fieldset-filho").css("cssText", "display: none !important;");
				
			} else {
				$("#seta-" + indiceId).attr("src",   baseUrl + "/public/images/botoes/seta_cima.png");
				$("#seta-" + indiceId).attr("class", "seta_cima");
				
				$("#fs-"+indiceId+" fieldset.fieldset-filho").css("cssText", "display: block !important;");
				
				$("#fs-"+indiceId+" div.linha").each(function() {
					$(this).show("blind", function() {
						// Controla o tamanho da ABA 
						Base.controlaTamanhoAba();
					}, 500);
				});
				
				$("#fs-"+indiceId+" table").each(function() {
					$(this).show("blind", function() {
						// Controla o tamanho da ABA 
						Base.controlaTamanhoAba();
					}, 500);
				});
				
			}
						
		}
		
	},
	
	/**
	 * Monta as mensagens do sistema
	 * 
	 * @param array 	mensagens
	 * @param string	titulo 
	 * @param integer	tipo (estilo da mensagem [padrão = 1/ sucesso = 2/ erro = 3/ alerta = 4])
	 * @param string	Chama um evento ao fechar a tela
	 * 
	 * @returns string
	 */
	montaMensagemSistema : function (mensagens, titulo, tipo, eventoFechar) {
		
		try{
			
			if (window.frameElement != null) {
				
				// Guarda a referencia da origem do iframe que quer executar o evento
				window.parent.Base.idIframeOrigemEvento = window.frameElement.id;
				
				// Chama o evento da janela pai
				window.parent.Base.montaMensagemSistema(mensagens, titulo, tipo, eventoFechar);
			} else {
				
				Base.escondeTelaCarregando();
				
				// Remove a div do dialogo
				$("#dialog-message").dialog("destroy");
				$("#dialog-message").remove();
					
				mensagem = ""; 
				
				switch(String(tipo)) {
				
					case "1"      :  // Padrão
                    case "PADRAO" :
							  class_ico = "ui-icon-bullet";
							  titleBar  = "";
							  break;
							  
					case "2"       :  // Sucesso
                    case "SUCESSO" :
							  class_ico = "ui-icon-circle-check";
					 		  titleBar  = "verde";
							  break;
							  
					case "3"    :  // Erro
                    case "ERRO" :
							  class_ico = "ui-icon-closethick";
							  titleBar  = "vermelho";
							  break;
							  
					case "4"      :  // Alerta
                    case "ALERTA" :
							  class_ico = "ui-icon-alert";
							  titleBar  = "amarelo";
							  break;
							  
					default : // Padrão
							  class_ico = "ui-icon-bullet";
							  titleBar  = "";
                              break;
				}
		        
		        mensagem += "<div id='dialog-message' title='"+titulo+"'>";
		        
		        for(var i=0; i < mensagens.length; i++) {
		        	mensagem += "<p class='dialog-message-p' style='margin: 0px; padding: 2px; text-align: left;'>";
		            mensagem += "<span class='ui-icon "+class_ico+"' style='float:left; margin:0px; margin-right: 10px;'></span>";
		            mensagem += mensagens[i];
		            mensagem += "</p>";
		        }
		        
		        mensagem += "</div>";
		        
		        // Inclui no corpo da pagina a div mensagem
		        $("body").append(mensagem);
		        
		        // Monta a caixa de dialogo
		        $("#dialog-message").dialog({
					modal: true,
					close: function(event, ui) {
		        		$('.ui-dialog-titlebar').css({background: 'none'});
		        		$('.ui-dialog-titlebar #ui-dialog-title-dialog-message').css({color: '#444'});

		        		if(typeof(eventoFechar) != "undefined" && eventoFechar != "") {
		        			
                            // Controla se o evento é do pai ou do filho
                            Base.executaEventoFechar(eventoFechar);
                            
		        		}
		        	},
					buttons: {
						Fechar : function() {
							
							$(this).dialog('close');
							
							// Se sucesso
							if(tipo < 3) {
								
								// Verifica se tem mais alguma operação na fila
								if($(Eventos.idFormularioAlterado).size() > 0) {
                                    
                                    // Caso a chamada seja de um Iframe
                                    if (Base.idIframeOrigemEvento != "") {
                                        // Retorna o id da aba ativa
                                        abaId = Abas.retornaAbaAtiva();

                                        // Remove o "aba" do grupo
                                        divId = abaId.split("_");

                                        // Captura o id do formulario que está na aba ativa
                                        var formId = divId[0] + "_form";

                                        // Remove do array o id do formulario
                                        Eventos.idFormularioAlterado = jQuery.grep(Eventos.idFormularioAlterado, function(valor) {
                                            return valor != formId;
                                        });
                                    }
								}
								
								// Limpa a referencia da origem do iframe 
								Base.idIframeOrigemEvento = "";
							} 							
						}
					}
				});
		        
			    // Troca a cor do titulo em função do "TIPO"
			    var url = 'url('+baseUrl+'/public/images/ui-bg_highlight-soft_15_1x100_'+titleBar+'.png)';        
			    $(".ui-dialog-titlebar").css({backgroundImage: url, backgroundRepeat: 'repeat-x', backgroundColor: '#444'});			 
			}			
			
		} catch (e) {			
			alert(e);
		}		
    },
    
    // Executa o evento ao clicar no botão fechar na mensagem
    executaEventoFechar : function(eventoFechar) {
        
        // Caso o evento seja de um Iframe
        if (Base.idIframeOrigemEvento != "") {
            
            // Retorna para o filho
            window.document.getElementById(Base.idIframeOrigemEvento).contentWindow.Base.executaEventoFechar(eventoFechar);
            
            // Limpa a referencia da origem do iframe 
            Base.idIframeOrigemEvento = "";
            
        } else {

            // Executa o evento
            window.setTimeout(function() {
                eval("try {" + eventoFechar + "} catch(e){ alert('Erro: ' + e); }");
            }, 100);

        }
        
    },

    // Trata as requisições e Mostra o gif animado nas em AJAX
    ajaxLoader : function () {
    	
    	// Quando o ajax inicia    	
		$("#ajax").ajaxStart(function() {
			
			// Se o campo que está processando for auto-complete
	    	// troca a cor de fundo
			try {
				if($("input:focus").hasClass("auto-complete-x")) {
					$("input:focus").addClass("auto-complete-x2");
				}
			} catch(e) {
				// alert(e);
			}
			
			try {
				// Reinicia o tempo de sessão
				if (typeof Sessao == "object") {
					
					// Se for UMA ABA
					if (window.frameElement != null) {
						
						// Reinicia o tempo de sessão
						window.parent.Sessao.reiniciaTempoSessao();
						
					} else {
						Sessao.reiniciaTempoSessao();
					}
					
				}
				
			} catch (e) {
				// alert(e)
			}
			
			// Mostra a tela de carregamento
			Base.mostraTelaCarregando();
		});
		
		// Quando o ajax termina
		$("#ajax").ajaxStop(function(){  
			
			// Se o campo que está processando for auto-complete
	    	// troca a cor de fundo
			try {
                // Remove o x2 de qualquer campo que o possua
				$("input").removeClass("auto-complete-x2");
                
			} catch(e){
				// alert(e);
			}
			
			// Mostra a palheta
			Base.escondeTelaCarregando();
		});
		
		// Qundo o ajax tem algum erro
		$("#ajax").ajaxError(function(event, request, settings, exception){
			// Mostra a palheta
			Base.escondeTelaCarregando();

			if(request.status == 0) {
				alert("Erro requisição cancelada " + settings.url);
			} else if(request.status == 401) {
				Base.sair("saida-forcada");
			} else {
				alert("Erro: " + settings.url + " - " + request.status + "\n\n\n ATENÇÃO: " + request.responseText);
			}
		});	
				
    },
    
    // Mosta a tela de carregando
    mostraTelaCarregando : function(msg) {
    	
    	try {
    		
    		if (window.frameElement != null) {
    			
    			// Guarda a referencia da origem do iframe que quer executar o evento
				window.parent.Base.idIframeOrigemEvento = window.frameElement.id;
				
        		// Chama o evento da janela pai
    			window.parent.Base.mostraTelaCarregando();
    			
        	} else {
        		
                if(msg == undefined || msg == "") {
                    msg = '<h1>Um momento...</h1>';
                }
                
        		if(Base.mostraProtecaoTela == true && $("#carregando").css('display') == "none") {
        			
                    // Ativa o carregando para controle de páginas antigas (Substituido pelo blockUI)
        			$("#carregando").show();
                    
                    $.blockUI.defaults.baseZ = 1002;
                    
                    // Bloqueia a tela
					if ((jQuery.browser.msie === true)) {
						$.blockUI({message    : msg,
								   css        : {border: 'none', padding: '15px', backgroundColor: 'none', color: '#777777'},
								   overlayCSS : {backgroundColor: '#f1f3f5'},
								   css        : {border: '3px solid #cccccc'}
								  });
					} else {
						$.blockUI({message    : msg,
								   css        : {border: 'none', padding: '15px', backgroundColor: 'none', color: '#777777'},
								   overlayCSS : {backgroundColor: '#f1f3f5'}
								  });
					}
            	}
        	}
    	} catch (e) {
    		alert("Erro: " + e);
    	}
    	
    }, 
    
    // Esconde a tela de carregando
    escondeTelaCarregando : function() {
    	try {    		
    		
    		if (window.frameElement != null) {
        		// Chama o evento da janela pai
    			window.parent.Base.escondeTelaCarregando();
    			
        	} else {
    	    	
        		if(Base.escondeProtecaoTela == true) {
        			
                    // Esconde o carregando para controle de páginas antigas (Substituido pelo blockUI)
        			$("#carregando").hide();
                    
                    // Desbloqueia a tela
    		    	$.unblockUI();
    	    	}
        	}
    	} catch(e){
    		//alert("Erro: " + e);
    	}    	
    },
    
	// Mosta a tela de proteção
    mostraTelaProtecao : function(msg) {
    	
    	try {
    		
    		if (window.frameElement != null) {
    			
    			// Guarda a referencia da origem do iframe que quer executar o evento
				window.parent.Base.idIframeOrigemEvento = window.frameElement.id;
				
        		// Chama o evento da janela pai
    			window.parent.Base.mostraTelaProtecao();
    			
        	} else {
        		
                if(msg == undefined || msg == "") {
                    msg = '<h1>...</h1>';
                }
                
        		if(Base.mostraProtecaoTela == true && $("#protecao_tela").css('display') == "none") {
        			
                    // Ativa o carregando para controle de páginas antigas (Substituido pelo blockUI)
        			$("#protecao_tela").show();
                    
            	}
        	}
            
    	} catch (e) {
    		alert("Erro: " + e);
    	}
    	
    }, 
    
    // Esconde a tela de proteção
    escondeTelaProtecao : function() {
    	try {    		
    		
    		if (window.frameElement != null) {
        		// Chama o evento da janela pai
    			window.parent.Base.escondeTelaProtecao();
    			
        	} else {
    	    	
        		if(Base.escondeProtecaoTela == true) {
        			
                    // Esconde o carregando para controle de páginas antigas (Substituido pelo blockUI)
        			$("#protecao_tela").hide();
                    
    	    	}
        	}
    	} catch(e){
    		//alert("Erro: " + e);
    	}    	
    },
	
    // Controla o tamanho da aba
    controlaTamanhoAba : function() {
    	
    	// Controla a posição inicial da aba
		if($("#tabs").size()) {
			// Captura a posição do campo referente a janela
			var position        = $("#altura_tab").position();
			
			// Calcula a diferença do cabeçalho para o início da aba
			var alturaAba  = Base.wH - 30 - parseInt(position.top, 10);
			
			// Verifica se a aba está transpondo o total da janela, e
			// caso esteja, seta o tamanho máximo da aba em 330
			if((parseInt(position.top, 10) + 430) > Base.wH) {
				alturaAba = 430;
			}
			
			// Seta a altura da tab e do iframe
			$("#tabs").css("height", alturaAba+"px");
			$("#tabs iframe").css("height", alturaAba+"px");
			
		}
    	
    },
    
    // Trabalha na janela flutuante
    JanelaFlutuante : {
    	
    	// Carrega os dados da janela
    	load : function(url) {
    		
    		if (window.frameElement != null) {
				
        		// Chama o evento da janela pai
    			window.parent.Base.JanelaFlutuante.load(url);
    			
        	} else {
    		

	    		$("#janela-flutuante").css("display", "block");
	    		$("#janela-flutuante").draggable({
	    			handle : $("#jfHeader"),
	    			start  : function(event, ui) {
	    				if($("#jfIframeBody").size() > 0) {
	    					$("#jfIframeBody").css("display", "none");
	    				}
	    			},
	    			stop   : function(event, ui) {
	    				if($("#jfIframeBody").size() > 0) {
	    					$("#jfIframeBody").css("display", "block");
	    				}
	    			}
	    		});
	    		
	    		$("#jfBody").html("");
	    		if(url.indexOf("http") == 0) {
	            $("#janela-flutuante").css("overflow", "hidden");
	    			$("#jfBody").html("<iframe id='jfIframeBody' src='' style='width: 100%; height: 540px;'></iframe>");
	    			$("#jfIframeBody").attr("src", url + "/janela-flutuante/1");
	    			
	    		} else {
	    			
	    			// Mostra a tela de carregamento que 
	        		// protege de alterações no conteúdo da página
	        		Base.mostraTelaCarregando();

	    			
	    			$("#jfBody").html(url);
	    		}
	    		
        	}
    	},
    	
    	// Fecha a janela
    	close : function() {
    		
    		 if (window.frameElement != null) {
				
        		// Chama o evento da janela pai
    			window.parent.Base.JanelaFlutuante.close();
    			
        	} else {
			
				// Esconde a tela de carregamento
				Base.escondeTelaProtecao();
				
				$("#jfBody").html("");
				$("#janela-flutuante").css("display", "none");
			}
    	}
    },
    
    // Funcao responsavel por controlar o tamanho maximo de caracteres de um campo do tipo TextArea
    //exemplo de chamada Base.setaLimiteTextarea(this, 950);
    //parametro, Objeto e tamanho maximo de caracteres
    setaLimiteTextarea : function(obj, limite) {
    	
    	$(obj).keypress(function(){
            var tamanho = $(this).val().length;
            if(tamanho > limite)
               tamanho -= 1;
            if(tamanho >= limite){
               var txt = $(this).val().substring(0, limite)
               $(this).val(txt)
            }
         });
    	
    }
    
};

//Aliases...
var janelaPai = null;

// Carrega o documento e todas as funções de inicialização das páginas internas
$(document).ready(function() {

	// Instancia a classe	
	Base.init();	
	
	// Alias para a janela pai
	janelaPai = Base.janelaPai;
	
	try {
		// Verifica se existe o arquivo de controle dos formulários
		if (typeof Form == "object") {
			Form.init();
		}
		
		// Verifica se existe o arquivo com os controles das tabelas
		if (typeof Tabela == "object") {
			Tabela.init();
		}
		
		// Verifica se existe o arquivo com as validações
		if (typeof Data == "object") {
			Data.init();
		}
		
		// Verifica se existe o arquivo com as validações
		if (typeof Valida == "object") {
			Valida.init();
		}
		
		// Verifica se existe o arquivo com os cálculos
		if (typeof Calcula == "object") {
			Calcula.init();
		}
		
		// Verifica se existe o arquivo com as mascaras
		if (typeof Mascara == "object") {
			Mascara.init();
		}
		
		// Verifica se existe o arquivo com os controles das abas
		if (typeof Abas == "object") {
			Abas.init();
		}
		
		// Verifica se existe o arquivo com de controle das sessões
		if (typeof Sessao == "object") {
			Sessao.init();
		}
        
        // Verifica se existe o arquivo com as ações de botões da toolbar/palheta
		if (typeof Eventos == "object") {
			Eventos.init();
		}
		
		// Verifica se existe o arquivo das páginas internas
		if (typeof Interna == "object") {
			
			Interna.init();
			
			// Chama o evento da classe Interna referente a action em execução
			if (no_action == "index") {				
				if (typeof Interna.initIndexAction == "function") {
					Interna.initIndexAction();
				}
			}
			
			if (no_action == "novo") {				
				if (typeof Interna.initNovoAction == "function") {
					Interna.initNovoAction();
				}
			}
			
			if (no_action == "salvar") {				
				if (typeof Interna.initSalvarAction == "function") {
					Interna.initSalvarAction();					
				}
			}
			
			if (no_action == "excluir") {				
				if (typeof Interna.initExcluirAction == "function") {
					Interna.initExcluirAction();
				}
			}
			
			if (no_action == "pesquisar") {				
				if (typeof Interna.initPesquisarAction == "function") {
					Interna.initPesquisarAction();
				}
			}
			
			if (no_action == "selecionar") {				
				if (typeof Interna.initSelecionarAction == "function") {
					Interna.initSelecionarAction();
				}
			}
						
			if (no_action == "relatorio") {				
				if (typeof Interna.initRelatorioAction == "function") {
					Interna.initRelatorioAction();
				}
			}
			
		}
        
        // Verifica se existe o arquivo com as ações de botões da toolbar/palheta
		if (typeof Ultimo == "object") {
			Ultimo.init();
		}
		
	} catch (e) {
		// Mostra o erro
		alert("Base.js - Erro: " + e);
	}
});