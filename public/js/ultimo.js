var Ultimo = {
	
	// Método principal da classe
	init : function() {
		
        // Correção para os campos select multiplos que utilizam o plugin
        // para controle em tela
        $("select").removeAttr("multiple");
        
        // Controla o foco da tela quando houver aba
        $("body").on('click', function(event) {
            // Não seleciona o documento se apertar em algum item da palheta de botões
            if($(event.target).closest($("#palhetaControle")).size()    == 0 && 
               $(event.target).closest($("#palhetaControleAba")).size() == 0 && 
               $(event.target).closest($("div.ui-dialog")).size()       == 0 && 
               $(event.target).closest($("#janela-flutuante")).size()   == 0) {
                if (window.frameElement != null) {
                    // Se o iframe selecionado não for uma janela flutuante
                    if(! $("#pai").hasClass("janela-flutuante")) {
                        window.parent.Ultimo.controlaFocoTela('Detail');
                    }
                } else {
                    Ultimo.controlaFocoTela('Master');
                }
            }
        });
        
        // Seta o foco no documento pai ao carregar uma tela que possui aba
        if($("#tabs").size() > 0) {
            Ultimo.controlaFocoTela('Master');
            if($("#tabs").size() > 0) {
                // Percorre as abas e seta o foco ao selecionar a tab
                var active = 0;
                var indice = 0;
                $("#tabs_ul li").each(function() {
                    
                    // Se a aba não estiver desabilitada
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
        
        // Se a palheta direita estiver bloqueada, ou estiver aberto uma tela de confirmação, não seleciona a tela
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
                
    }
    	
};