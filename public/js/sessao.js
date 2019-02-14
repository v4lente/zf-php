// Classe que faz o controle da sess�o do usu�rio 
var Sessao = {
	
	// Tempo da sess�o do usu�rio	
	timer   	       : 0,
	idTimer 	       : null,
	idTempoDecorrido   : null,
	// Tempo m�ximo do servidor em milisegundos
	maxTimeLife        : 3600000,
	// Tempo de sess�o m�ximo para desconectar permantente o usu�rio
	idMaxTimeLife      : null,
	// Tempo de inatividade
	tempo_sem_atividade : 15,
	// Tempo decorrido
	tempo_decorrido     : 0,
		
	// M�todo principal da classe
	init : function() {
		
		try {	
			
			// Se for UMA ABA
			if (window.frameElement != null) {
				
				// Reinicia o tempo de sess�o
				window.parent.Sessao.reiniciaTempoSessao();
				
			} else {
				
				// Pega o tempo de inatividade do usu�rio definido no perfil
				this.tempo_sem_atividade = $("#_tempo_sem_atividade").val();
				
				// Se o tempo sem atividade n�o for setado, define 15 minutos
				if(this.tempo_sem_atividade == "") {
					this.tempo_sem_atividade = 15;
				}
				
				// Transforma o tempo de sess�o de minutos para segundos para milisegundos
				this.timer = parseInt(this.tempo_sem_atividade, 10) * 60 * 1000;
				
				// Verifica o tempo de sess�o
				this.tempoSessao();
				
				// Verifica o tempo decorrido da sess�o
				this.tempoDecorrido();
				
			}
			
		} catch(e) {
			// trata o erro aqui
			alert(e);
		}
		
	},
	
	// Reinicializa o tempo de sess�o do usu�rio
	reiniciaTempoSessao: function() {
		
		clearTimeout(this.idTimer);
		clearTimeout(this.idTempoDecorrido);
		
		// Chama a fun��o sair do sistema ap�s o tempo de inatividade
		this.tempoSessao();
		
		// Chama a fun��o do tempo decorrido
		this.tempoDecorrido();
		
	},
	
    // Pega o tempo da sess�o configurada para o usu�rio
    tempoSessao : function() {
    	
    	// Se for a janela pai
    	if (window.opener == null) {
    		
    		// Se nao estiver no login
    		if (no_controller != "login") {
        		
            	// Chama a fun��o sair do sistema ap�s o tempo de inatividade
            	this.idTimer = setTimeout("window.parent.Sessao.sairX()", this.timer);
        	}
    	}
    	
    },
	
	// Seta o tempo decorrido da sess�o
	tempoDecorrido : function() {
		
		// Zera o tempo decorrido
		this.tempo_decorrido = 0;
		
        // Limpa o intervalo
        clearInterval(this.idTempoDecorrido);
        
		// Seto o tempo decorrido a cada minuto
		this.idTempoDecorrido = setInterval(function() {
			Sessao.tempo_decorrido = parseInt(Sessao.tempo_decorrido, 10) + 1;
			$("#_tempo_decorrido").val(Sessao.tempo_decorrido);
		}, 1000);
		
	},
    
    // Desloga do sistema
    sairX : function () {    	
    	
    	// Verifica se tem janelas filhas
    	if(Base.janelasFilhas.length > 0) {
    		// Percorre o array de janelas filhas
    		$(Base.janelasFilhas).each(function() {
    			// Fecha a janela filha
    			this.close();
    		});
    		
    		// Zera o atributo da classe base com um array vazil
    		Base.janelasFilhas = Array();
    	}
    	
    	this.deslogar();
    	
    },
    
    // Chama a tela para refazer o login
    telaLogar : function() {
    	// Pega o usu�rio logado
    	usuarioLogado = $("#_cd_usuario_logado").val();
    	
    	// Monta o html da tela
    	html = "<div>" +
    			"<span>Sua sess�o expirou, deseja reconectar?</span>" +
    			"<br />" +
    			"<br />" +
    			"<form name='' action=''>" +
    			"<div style='margin-botton:7px;'><label for='_cd_usuario'>Usu�rio </label>" +
    			"<input style='width:150px;' type='text' name='_cd_usuario' id='_cd_usuario' value='"+usuarioLogado+"' maxlength='20' class='required' readonly='readonly'/></div>" +
    			"<div class='clear'></div>" +
    			"<div><label for='senha' style='margin-left:9px;'>Senha </label><input style='width:150px;' type='password' maxlength='20' name='_senha' id='_senha' class='required' value='' onkeypress='if(event.keyCode == 13) Sessao.confirmar();' /></div>" +
    			"</form>" +
    			"</div>";
    	
    	// Chama a tela
    	Base.confirm(html, "Autentica��o", "Sessao.logarX()", "Base.sair()");
    	
    	$("#_senha").focus();
    	
    	// Verifica a diferen�a do tempo do banco para o do servidor
    	// Hoje est� em 3600 segundos (1 hora), mas poder� ser alterado.
    	var diferencaTempo = this.maxTimeLife - this.timer;
		
		// Chama a fun��o deslogar do sistema ap�s estourar o tempo m�ximo do servidor
    	this.idMaxTimeLife = setTimeout("Sessao.deslogar()", diferencaTempo);
    	
    },
    
    // Chama o bot�o de confirmar
    confirmar : function() {
    	
    	// Captura os bot�es do dialogo
    	var botoes  = $(".ui-dialog-buttonset").find("button");
    	
    	// Clica no bot�o
    	$(botoes[0]).click();
    	
    },
    
    // Verifica se a se��o do usu�rio esta ativa
    logarX : function() {
    	
    	// Dispara o ajax para o controller login action logar-x
    	$.getJSON(baseUrl + "/login/logar-x",
    			{cd_usuario: $("#_cd_usuario").val(), senha: $("#_senha").val()}, 
    			function(retorno) {
		    		// Caso nao autentique
		    		if (retorno !== true) {
		    			// Chama a tela de logar
		    			Sessao.telaLogar();
		    		} else {
		    			Sessao.reiniciaTempoSessao();
		    		}    		
    	});
    },
    
    // Desloga a for�a o sistema se estourar o tempo limite de sess�o do servidor
    deslogar : function() {
    	
    	clearTimeout(this.idMaxTimeLife);
    	
    	document.location.href = baseUrl + "/login/sair/msg-sessao/expirada";
    	
    }
    
};