// Classe que controla as tabelas do sistema
var Tabela = {
		
	// M�todo principal da classe
	init : function() {
		
		// Controla se alguma tabela est� sendo ordenada
		// e seta a ordena��o correta
		if(column != "") {
			var seta = "";
			if(orderby != "") {
				orderby = orderby.toUpperCase();
			}
			
			if(orderby == "ASC" || orderby == "") {
				seta = "seta-cima";
			} else {
				seta = "seta-baixo";
			}
	
			// Define a classe na td
			$("table.tabela > thead > tr > th[column='"+column+"']").addClass(seta);
			$("table.tabela > thead > tr > th[column='"+column+"']").attr("orderby", orderby);
		}	
		
		// Seta a classe do cabe�alho da tabela para formatar conforme o tema
		$("table.tabela > thead").addClass("ui-widget-header");
		$("table.tabelaAbas > thead").addClass("ui-widget-header");
	
		// Faz todo o controle de ordena��o das colunas, mostrando uma seta para cima ou para baixo.
		if(! $('table.tabela').hasClass("nao-ordenavel")) {
            
            // Muda o tipo de cursor para "link", para que possa o usu�rio ordenar.
            $("table.tabela > thead > tr > th").css("cursor", "pointer");
            
			if ($.trim($("table.tabela > thead > tr > th").attr("column")).length > 0) {
				
				// Insere as ordena��es nas colunas
				$("table.tabela > thead > tr > th").each(function() {
					
					// Seta a div que conter� a ordena��o
					img_seta = "<div class='img_seta'></div>";
					
					// Verifica se a coluna j� n�o possui ordena��o
					if(! $(this).find(".img_seta").size()) {
						// Inclui a ordena��o no final da coluna
						$(this).append(img_seta);
					}
					
				});
				
				
				$("table.tabela > thead > tr > th").click(function () {
					
                    // Se n�o possuir o atributo column n�o executa a ordena��o
					if (typeof($(this).attr("column")) == "undefined") {
						return false;
					}
                    
                    if(orderby != "") {
						orderby = orderby.toUpperCase();
					}
					
					// Se for a mesma coluna troca a ordena��o
					if ($(this).attr("column") != column) {
						$(this).attr("orderby", "ASC");
						orderby = "";
					}
									
					if(orderby == "DESC" || orderby == "") {
						//asc
						$("table.tabela > thead > tr > th").removeClass("seta-cima");
						$("table.tabela > thead > tr > th").removeClass("seta-baixo");
						$(this).addClass("seta-cima");
						$(this).attr("orderby", "ASC");
					} else {
						//desc
						$("table.tabela > thead > tr > th").removeClass("seta-cima");
						$("table.tabela > thead > tr > th").removeClass("seta-baixo");
						$(this).addClass("seta-baixo");
						$(this).attr("orderby", "DESC");
					}
					
					// Seta a ordem no campo hidden
					$("#orderby").val($(this).attr("orderby"));
					
					// Chama o pesquisar, para fazer a reordena��o	
					var filtroParams = "";
					$(":input, select, textarea").each(function(i, obj) {	
						// Desconsidera os atributos de ordena��o
						if ($(obj).attr("name") != "column" && $(obj).attr("name") != "orderby" ) {
						
							if( (! $(obj).attr("readonly")) && (! $(obj).attr("disabled"))) {
								if($(obj).attr("type") == "radio" || $(obj).attr("type") == "checkbox") {
									if($(obj).is(':checked')) {
										filtroParams += "/" + $(obj).attr("name") + "/" + encodeURIComponent($(obj).val());
									}
								} else {
									if ($.trim($(obj).val()) != "") {
										filtroParams += "/" + $(obj).attr("name") + "/" + encodeURIComponent($(obj).val());
									}
								}
							}
						}					
					});
					
					// Chama a prote��o da tela
					Base.mostraTelaCarregando();
					
					// Se encontrar o formul�rio, faz a pesquisa por ele, sen�o busca todos os campos e envia os dados por GET
					if($("#form_pesquisar").size() > 0) {
						
						// Atualza os campos da pesquisa com o valor de origem
						Form.atualizaCamposPesquisarValorOrigem();
						
						// Cria os campos hidden
						var inputColumn  = "<input type='hidden' name='column'  value='" + $(this).attr("column") + "' />";
						var inputOrderby = "<input type='hidden' name='orderby' value='" + $(this).attr("orderby") + "' />";
						
						// Dispara a pesquisa
						/*$("#form_pesquisar").append(inputOrderby)
											.append(inputColumn)
											.submit();*/
	
						$("#form_pesquisar").attr({
							onsubmit: 'return true',
							action: baseUrlController + '/pesquisar',
							target: ''
						}).append(inputOrderby)
						  .append(inputColumn)
						  .submit();
						
					} else {
						
						// Monta a URL
						url = "column/" + $(this).attr("column") + "/orderby/" + $(this).attr("orderby") + filtroParams;
						
						// Executa a chamada da URL
						document.location.href = baseUrlController + "/" + no_action + "/crypt/" + $.base64.encode(url);
					}
									
				});
				
			}
			
		}
		
		// Seta a m�scara nas colunas das tabelas
		this.setaMascaraColuna();
	
		// Zebra a tabela
		this.zebrar();
	    
	    // Ativa o plugin para as mensagens do menu
		$( ".column" ).sortable({
	        connectWith: ".column"
	    });
		
		// Desativa a sele��o
		$( ".column" ).disableSelection();
	
	},
	
	// Seta as m�scaras nas colunas das tabelas
	setaMascaraColuna : function() {
		
		// Faz o controle das m�scaras
		if ($("table.tabela > tbody > tr").size() > 0) {
			
			// Cria o campo para controle
			var maskInputHidden = $('<input type="hidden" name="mask-input-hidden" id="mask-input-hidden" value="" />').appendTo('body');
			
			// Passa por cada coluna no corpo da tabela e aplica a m�scara
			$("table.tabela > tbody > tr > td").each(function() {
				
				// Remove todas as classes da m�scara
				Mascara.unsetAll(maskInputHidden);
				
				// Se a m�scara for moeda
				if($(this).is(".qtde, .moeda, .peso, .placa, .data, .dthr, .decimal, .numero, .cnpj, .tonelada, .datahoraminuto")) {
					
					// Seta o valor da coluna no campo
					$(maskInputHidden).val($.trim($(this).html()));
					
					// Se for moeda
					if($(this).hasClass("qtde")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setQtde($(maskInputHidden));
						
					} else if($(this).hasClass("moeda")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setMoeda($(maskInputHidden));
						
					} else if($(this).hasClass("peso")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setPeso($(maskInputHidden));
						
					} else if($(this).hasClass("placa")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setPlaca($(maskInputHidden));
						
					} else if($(this).hasClass("data")) {
						// Adiciona a classe readonly para n�o gerar o datepicker
						$(maskInputHidden).attr("readonly", true);
						
						// Aplica a m�scara no campo
						var obj = Mascara.setData($(maskInputHidden));
						
					} else if($(this).hasClass("dthr")) {
						// Adiciona a classe readonly para n�o gerar o datepicker
						$(maskInputHidden).attr("readonly", true);
						
						// Aplica a m�scara no campo
						var obj = Mascara.setDataHora($(maskInputHidden));
						
					} else if($(this).hasClass("decimal")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setDecimal($(maskInputHidden));
						
					} else if($(this).hasClass("numero")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setNumero($(maskInputHidden));
						
					} else if($(this).hasClass("cnpj")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setCnpj($(maskInputHidden));
						
					} else if($(this).hasClass("tonelada")) {
						// Aplica a m�scara no campo
						var obj = Mascara.setTonelada($(maskInputHidden));
						
					} else if($(this).hasClass("datahoraminuto")) {
						// Adiciona a classe readonly para n�o gerar o datepicker
						$(maskInputHidden).attr("readonly", true);
						
						// Aplica a m�scara no campo
						var obj = Mascara.setDataHoraMinuto($(maskInputHidden));
						
					}					
					
					// Seta o valor da m�scara na coluna
					$(this).html($(obj).val());
				}
				
			});
			
			// Destroi o elemento no final
			$(maskInputHidden).remove();
			
		}
		
	},
	
	// Remove a funcionalidade de selecionar
	desabilitaSelecao : function() {
		
		if ($('#tabelaConsulta').size() > 0) {
			$('#tabelaConsulta').addClass("nao-selecionavel");
			$('#tabelaConsulta tbody tr').unbind("click");
			$('#tabelaConsulta tbody tr').css("cursor", "default");
		}
		
	},
	
	// Zebra a tabela e adiciona o m�todo para chamar a view selecionar
	zebrar : function(tabela) {
		
		 // Responsavel por zebrar a tabela
        if(tabela != undefined) {
            
            // Remove as classes antes de come�ar a zebrar
            $(tabela).find("tbody").find("tr").removeClass("par");
            
            // Adiciona a classe par nos campso pares
	        $(tabela).find("tbody").find("tr:odd").addClass('par');
	        $(tabela).find("tbody").find("tr").mouseover(function(){
	            $(this).addClass('hover');
	        }).mouseout(function(){
	            $(this).removeClass('hover');
	        });
            
        } else if($('table.tabela').size()) {
            
            // Remove as classes antes de come�ar a zebrar
            $('table.tabela tbody tr').removeClass("par");
            
            // Adiciona a classe par nos campso pares
	        $('table.tabela tbody tr:odd').addClass('par');
	        $('table.tabela tbody tr').mouseover(function(){
	            $(this).addClass('hover');
	        }).mouseout(function(){
	            $(this).removeClass('hover');
	        });
	        
	        // Chama a action selecionar do controller quando ocorrer um duplo click na row
	        if(! $('table.tabela').hasClass("nao-selecionavel")) {
	        	$('table.tabela tbody tr').click(function() {
		        	try {
                        
		        		if($(this).attr("key")) {
			        		var key = $(this).attr("key");
			        		key = "crypt/" + $.base64.encode(key);
			        		
			        		// Mostra a tela de carregando enquanto n�o for subetido a requisi��o
							Base.mostraTelaCarregando();
			        		Interna.selecionar(key);
			        		
		        		} else {
		        			alert("Chave n�o definida!");
		        		}
		        	} catch(e){
		        		// n�o tem a function selecionar na tabela
		        	}
		        });
	        }
	        	        
	    }
	    
	    // Responsavel por zebrar as tabelas dentro das ABAS
	    if($('table.tabelaAbas').size()){    	
	        $('table.tabelaAbas tbody tr:odd').addClass('par');
	        $('table.tabelaAbas tbody tr').mouseover(function(){
	            $(this).addClass('hover');
	        }).mouseout(function(){
	            $(this).removeClass('hover');
	        });
	    }
		
	}
};