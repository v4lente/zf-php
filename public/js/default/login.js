//Classe interna, carregada no OnLoad
var Interna = {
	
	// M�todo principal da classe
	init : function() {
		
		// Seta o foco no campo do usu�rio
		$("#cd_usuario").focus();

		// Controla o evento CLICK do bot�o conectar
		$("#conectar").click(function(){
			 Interna.validaForm();				
		});
		
		// Caso esteja com a conta expirada
		if ($("#contaExpirada").size()> 0 && $("#contaExpirada").val() == 1) {
			this.telaContaExpirada();
		}	
        //Envia para a tela que o usuario solicitar� a altera��o de senha para depois trocar a mesma
        $("#solicita").click(function() {
            var cdUsuario = $(this).attr('cd_usuario');
			$("#form_base").attr({
				onsubmit: "return true",
				action: baseUrl + "/default/login/novo/cd_usuario/" + cdUsuario,
				target: "_self"
			}).submit();
		});
		
        //verifica se o usuario que esta solicitando tem e-mail cadastrado
        //se tiver altera a senha e envia e-mail para futura altera��o
		$("#enviar").click(function(){
           
			if ($("#cd_usuario").val() == "" ){
				Base.montaMensagemSistema(Array("O Campo Usu�rio � Obrigat�rio!"), "Aten��o", 4);
			} else{ 
				$.ajax({
					url: baseUrlController + "/verifica-email-usuario-x?cd_usuario=" + $("#cd_usuario").val() + "&cpf=" + $("#cpf").val(),
					async : false,
					success: function(retorno) { 
						if (retorno.EMAIL == ""){
							Base.montaMensagemSistema(Array("O usu�rio n�o tem E-mail Cadastrado. Favor entrar em contato com o CPD!"), "Aten��o", 4);
						} else{
                            
                            var url = $.base64.encode("cd_usuario/" + retorno.CD_USUARIO + "/email/" + retorno.EMAIL);
                            
							$("#form_base").attr({
								onsubmit: "return true;",
								action: baseUrlController + "/esquecisenha/crypt/" + url,
								target: "_self"
							}).submit();
						}
					}
				});
			}
		});
        //acao do bot�o voltar
        $("#voltar").click(function(){
            $("#form_base").attr({
				onsubmit: "return true",
				target: "_self"
			}).submit();
        });
	},
	
	// Faz a valida��o do formul�rio
	validaForm : function () {
		// Chama a valida��o do sistema
        var valida = Valida.formulario('form_base', true);
        
        // Se passar nas valida��es
		if (valida[0]) {
			$('#form_base').attr({
				onsubmit: 'return true',
				action: baseUrlController + '/logar',
				target: ''
			}).submit();
		}	
	},
	
	// Chama a tela para refazer o login
    telaContaExpirada : function() {
    	
    	// Pega o usu�rio logado
    	usuarioLogado = $("#cd_usuario").val();
    	
    	// Monta o html da tela
    	html = "<div style='text-align:right;'>" +
    			"<div style='text-align:center;'><span>Sua senha expirou ou foi esquecida, voc� deve alter�-la!</span></div>" +
    			"<br />" +
    			"<div style='text-align:justify;'><span>A Senha Nova deve conter no minimo 8 caracteres formada obrigatoriamente por letras e numeros. N�o podendo conter caracteres especiais (.;\�;#$@&_/|)</span></div><br />" +
    			"<form name='' action=''>" +
    			"<div style='margin-botton:7px;'><label for='_cd_usuario'>Usu�rio: </label>" +
    			"<input style='width:150px;' type='text' name='_cd_usuario' id='_cd_usuario' value='"+usuarioLogado+"' maxlength='20' class='required' disabled='disabled'/></div>" +
    			"<div class='clear'></div>" +
    			"<div><label for='senha' style='margin-left:9px;'>Senha atual: </label><input style='width:150px;' type='password' maxlength='20' name='_senha' id='_senha' class='required' value=''/></div>" +
    			"<div><label for='nova_senha' style='margin-left:9px;'>Nova senha: </label><input style='width:150px;' type='password' maxlength='20' name='nova_senha' id='nova_senha' class='required' value=''/></div>" +
    			"<div><label for='nova_senha2' style='margin-left:9px;'>Repita nova senha: </label><input style='width:150px;' type='password' maxlength='20' name='nova_senha2' id='nova_senha2' class='required' value='' onkeypress='if(event.keyCode == 13) Interna.alterarSenha();' /></div>" +
    			"</form>" +
    			"</div>";
    	
    	// Chama a tela
    	Base.confirm(html, "Altera��o de senha", "Interna.alterarSenhaX()", "Base.sair()");
    	
    	$("#_senha").focus();
    	
    },
    
    // Alterar senha via ajax
    alterarSenhaX : function () {
    	
    	var mensagem = Array();    	
    	var retorno  = null;    	
    	var valida   = false;    	
       
    	// Retorna os chassis para o agendamento em quest�o
	    $.ajax({
            type    : "POST",
            async   : false,
            url     : baseUrlController + "/altera-senha-x/_cd_usuario/" + $("#_cd_usuario").val()+
            											      "/_senha/" + $("#_senha").val()     + 
            											  "/nova_senha/" + $("#nova_senha").val() + 
            											  "/nova_senha2/" + $("#nova_senha2").val(),
            success : function(data) {
            	retorno = data;         	
            }});
		    
		    // Se a opera��o foi realizada com sucesso
		    if (retorno[0] == 1) {
		    	Base.montaMensagemSistema(Array(retorno[1]), "Sucesso", 2, "Base.sair();");
		    	valida = true;
		    } else {
		    	Base.montaMensagemSistema(Array(retorno[1]), "Aten��o", 3, "Base.sair();");
		    	valida = false;
		    }
    }
};