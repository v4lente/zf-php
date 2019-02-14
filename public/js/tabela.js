// Classe que controla as tabelas do sistema
var Tabela = {
		
	// Método principal da classe
	init : function() {
		
		// Controla se alguma tabela está sendo ordenada
		// e seta a ordenação correta
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
		
		// Seta a classe do cabeçalho da tabela para formatar conforme o tema
		$("table.tabela > thead").addClass("ui-widget-header");
		$("table.tabelaAbas > thead").addClass("ui-widget-header");
	
		// Faz todo o controle de ordenação das colunas, mostrando uma seta para cima ou para baixo.
		if(! $('table.tabela').hasClass("nao-ordenavel")) {
            
            // Muda o tipo de cursor para "link", para que possa o usuário ordenar.
            $("table.tabela > thead > tr > th").css("cursor", "pointer");
            
			if ($.trim($("table.tabela > thead > tr > th").attr("column")).length > 0) {
				
				// Insere as ordenações nas colunas
				$("table.tabela > thead > tr > th").each(function() {
					
					// Seta a div que conterá a ordenação
					img_seta = "<div class='img_seta'></div>";
					
					// Verifica se a coluna já não possui ordenação
					if(! $(this).find(".img_seta").size()) {
						// Inclui a ordenação no final da coluna
						$(this).append(img_seta);
					}
					
				});
				
				
				$("table.tabela > thead > tr > th").click(function () {
					
                    // Se não possuir o atributo column não executa a ordenação
					if (typeof($(this).attr("column")) == "undefined") {
						return false;
					}
                    
                    if(orderby != "") {
						orderby = orderby.toUpperCase();
					}
					
					// Se for a mesma coluna troca a ordenação
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
					
					// Chama o pesquisar, para fazer a reordenação	
					var filtroParams = "";
					$(":input, select, textarea").each(function(i, obj) {	
						// Desconsidera os atributos de ordenação
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
					
					// Chama a proteção da tela
					Base.mostraTelaCarregando();
					
					// Se encontrar o formulário, faz a pesquisa por ele, senão busca todos os campos e envia os dados por GET
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
		
		// Seta a máscara nas colunas das tabelas
		this.setaMascaraColuna();
	
		// Zebra a tabela
		this.zebrar();
	    
	    // Ativa o plugin para as mensagens do menu
		$( ".column" ).sortable({
	        connectWith: ".column"
	    });
		
		// Desativa a seleção
		$( ".column" ).disableSelection();
	
	},
	
	// Seta as máscaras nas colunas das tabelas
	setaMascaraColuna : function() {
		
		// Faz o controle das máscaras
		if ($("table.tabela > tbody > tr").size() > 0) {
			
			// Cria o campo para controle
			var maskInputHidden = $('<input type="hidden" name="mask-input-hidden" id="mask-input-hidden" value="" />').appendTo('body');
			
			// Passa por cada coluna no corpo da tabela e aplica a máscara
			$("table.tabela > tbody > tr > td").each(function() {
				
				// Remove todas as classes da máscara
				Mascara.unsetAll(maskInputHidden);
				
				// Se a máscara for moeda
				if($(this).is(".qtde, .moeda, .peso, .placa, .data, .dthr, .decimal, .numero, .cnpj, .tonelada, .datahoraminuto")) {
					
					// Seta o valor da coluna no campo
					$(maskInputHidden).val($.trim($(this).html()));
					
					// Se for moeda
					if($(this).hasClass("qtde")) {
						// Aplica a máscara no campo
						var obj = Mascara.setQtde($(maskInputHidden));
						
					} else if($(this).hasClass("moeda")) {
						// Aplica a máscara no campo
						var obj = Mascara.setMoeda($(maskInputHidden));
						
					} else if($(this).hasClass("peso")) {
						// Aplica a máscara no campo
						var obj = Mascara.setPeso($(maskInputHidden));
						
					} else if($(this).hasClass("placa")) {
						// Aplica a máscara no campo
						var obj = Mascara.setPlaca($(maskInputHidden));
						
					} else if($(this).hasClass("data")) {
						// Adiciona a classe readonly para não gerar o datepicker
						$(maskInputHidden).attr("readonly", true);
						
						// Aplica a máscara no campo
						var obj = Mascara.setData($(maskInputHidden));
						
					} else if($(this).hasClass("dthr")) {
						// Adiciona a classe readonly para não gerar o datepicker
						$(maskInputHidden).attr("readonly", true);
						
						// Aplica a máscara no campo
						var obj = Mascara.setDataHora($(maskInputHidden));
						
					} else if($(this).hasClass("decimal")) {
						// Aplica a máscara no campo
						var obj = Mascara.setDecimal($(maskInputHidden));
						
					} else if($(this).hasClass("numero")) {
						// Aplica a máscara no campo
						var obj = Mascara.setNumero($(maskInputHidden));
						
					} else if($(this).hasClass("cnpj")) {
						// Aplica a máscara no campo
						var obj = Mascara.setCnpj($(maskInputHidden));
						
					} else if($(this).hasClass("tonelada")) {
						// Aplica a máscara no campo
						var obj = Mascara.setTonelada($(maskInputHidden));
						
					} else if($(this).hasClass("datahoraminuto")) {
						// Adiciona a classe readonly para não gerar o datepicker
						$(maskInputHidden).attr("readonly", true);
						
						// Aplica a máscara no campo
						var obj = Mascara.setDataHoraMinuto($(maskInputHidden));
						
					}					
					
					// Seta o valor da máscara na coluna
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
	
	// Zebra a tabela e adiciona o método para chamar a view selecionar
	zebrar : function(tabela) {
		
		 // Responsavel por zebrar a tabela
        if(tabela != undefined) {
            
            // Remove as classes antes de começar a zebrar
            $(tabela).find("tbody").find("tr").removeClass("par");
            
            // Adiciona a classe par nos campso pares
	        $(tabela).find("tbody").find("tr:odd").addClass('par');
	        $(tabela).find("tbody").find("tr").mouseover(function(){
	            $(this).addClass('hover');
	        }).mouseout(function(){
	            $(this).removeClass('hover');
	        });
            
        } else if($('table.tabela').size()) {
            
            // Remove as classes antes de começar a zebrar
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
			        		
			        		// Mostra a tela de carregando enquanto não for subetido a requisição
							Base.mostraTelaCarregando();
			        		Interna.selecionar(key);
			        		
		        		} else {
		        			alert("Chave não definida!");
		        		}
		        	} catch(e){
		        		// não tem a function selecionar na tabela
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