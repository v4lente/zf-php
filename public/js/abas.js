// Classe que controla as abas nas views
var Abas = {
		
	// M�todo principal da classe
	init : function() {
		
		// Habilita as abas
		$(".tabs").tabs({ 
			selected: 0
		});
		
		// Trata algumas classes para as abas
		if($("#palhetaControleAba").size() > 0) {
			$("#palhetaControleAba").removeClass("ui-widget-header");
			$("table").removeClass("tabela");
			$("table").addClass("tabelaAbas");
			$("table thead").removeClass("ui-widget-header");
		}
		
		// Contrala os dados iniciais dos grupos
		$(".grupo").each(function() {
			grupo = $(this).attr("id");
			
			// Controla para ver se tem algum campo na tabela, caso contr�rio mostra
			// a linha com nehum registro cadastrado
			if($("#" + grupo + "tabela tbody tr").size() == 0) {
				$("#" + grupo + "sem_registro").css("display", "block");
				$("#" + grupo + "tabela").css("display", "none");
			} else {
				$("#" + grupo + "sem_registro").css("display", "none");
				$("#" + grupo + "tabela").css("display", "table");
			}
		});
		
        // Seta o click da aba para chamar o iframe
        $("#tabs_ul > li > a").bind("click", function() {
            var indice = $(this).attr("id").replace("tab_", "");
            Abas.ativaIframe('ifrm_' + indice);
        });
        
	},
	
	// Ativa os iFrames das abas
	ativaIframe : function(id_iframe) {
		
        var indice = id_iframe.replace("ifrm_", "");
        
        if(! $("#tab_" + indice).closest("li").hasClass("ui-state-disabled")) {
                        
            // Pega os par�metros para passar pro iframe
            var params = $("#" + id_iframe).attr("params");
            
            // Carrega o iframe com os dados
            if(params != "" && $("#tab_" + indice).attr("ativo") == "0") {
                
                // Mostra a tela de prote��o
                Base.mostraTelaCarregando();
                
                // Carrega o controllador interno
                $("#" + id_iframe).attr("src", params);
                $("#" + id_iframe).attr("params", "");
                
                $("#tab_" + indice).attr("ativo", "1");

            }
            
        }
		
	},
    
    // Atualiza os iFrames das abas
	atualizaIframe : function(id_iframe) {
		
         // Pega os par�metros para passar pro iframe
        var params = $("#" + id_iframe).attr("params");
        
        // Mostra a tela de prote��o
        Base.mostraTelaCarregando();
        
        // Carrega o controllador interno
        $("#" + id_iframe).attr("src", params);
        $("#" + id_iframe).attr("params", "");
        		
	},
    
    // Remove os bot�es da ABA (na master) e mostra os bot�es da Master
    removeBotoesAba : function() {
            
        // Remove a div dos bot�es do iframe
        if($("#botoesAba").size() > 0) {
            $("#botoesAba").remove();
            
            $("#palhetaDireita").attr("disabled", "disabled");
            
            // Esconde os bot�es da master
            $("#botoes").css("display", "block");
            $("#palhetaDireita").removeAttr("disabled");
            
        }
        
    },
    
    // Adiciona os botoes da ABA na MASTER
    adicionaBotoesAba : function(indiceAba) {
                
        // Bloqueia a palheta direita e 
        $("#palhetaDireita").attr("disabled", "disabled");
        
        // Esconde os bot�es da master
        $("#botoes").css("display", "none");
        
        // Remove a div dos bot�es do iframe
        if($("#botoesAba").size() > 0) {
            $("#botoesAba").remove();
        }
        
        // Cria a div dos bot�es do iframe
        //$("#palhetaDireita").append("<div id='botoesAba'></div>");
        $("<div id='botoesAba'></div>").insertBefore($("#botoes"));
        
        // Espera 200 milisegundos para incluir os botoes devido a altera��o de
        // algumas telas que editam as permiss�es, modificando a visualiza��o
        window.setTimeout(function() {
            
            // Percorre os botoes do iframe e cria na master
            $("#ifrm_" + indiceAba).contents().find(".palhetaBotoesAba").each(function() {
                
                // Verifica se o bot�o est� desabilitado
                if($(this).css("display") != "none") {
                    
                    var btAba   = $(this);
                    var idBotao = $(this).attr("id");
                    var tpBotao = idBotao.replace("botao-", "");
                    var tpBotao = tpBotao.replace("-", "_");
                    var tpBotao = tpBotao.toLowerCase();
                    
                    var nmBotao = "botao-"+tpBotao+"-"+indiceAba;
                    
                    $("#botoesAba").append("<img id='"+nmBotao+"' class='palhetaBotoesAba' src='/portoweb_teste/zf/public/images/botoes/"+tpBotao+".png' style='float: right; display: block; cursor: pointer;'>");
                    $("#"+nmBotao).bind("click", function() {
                        $(btAba).click();
                    });
                    
                }
                
            });
            
        }, 200);
              
        $("#botoesAba").css("display", "block");
        $("#palhetaDireita").removeAttr("disabled");
        
    }
	
};
