//Classe interna, carregada no OnLoad
var Interna = {
		
	// M�todo principal da classe
	init : function() {
     
     
		// Gerencia o nr_matric
		$("#nr_matric").blur(function(event) {
			var nr_matric = $(this).val();
			if(nr_matric != "") {
				Interna.retornaFuncionarioX(nr_matric);
			}
		});
        		
	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	// Salva o registro
	salvar : function() {
        // Faz a valida��o do formul�rio
		valida = Interna.validaFormulario();
		
		if (valida[0]) {
			$('#form_base').attr({
				onsubmit: 'return true',
				action: baseUrlController + '/salvar',
				target: ''
			}).submit();
		}
		
	},

	// Excluir o registro
	excluir : function() {
		$('#form_base').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/excluir',
			target: ''
		}).submit();
	},

	// Chama a tela de pesquisa
	telaPesquisa : function() {
		document.location.href = baseUrlController + "/index";
	},

	// Executa a tela de pesquisa
	pesquisar : function() {
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/pesquisar',
			target: ''
		}).submit();
	},

	// Seleciona um registro, recebendo a chave j� criptografada
	selecionar : function(chave) {
		document.location.href = baseUrlController + "/selecionar/" + chave;
	},

	// Chama a tela de relat�rios
	relatorio : function(metodo){
		$('#form_pesquisar').attr({
			onsubmit: 'return true',
			action: baseUrlController + '/relatorio/_rel_metodo/' + metodo,
			target: '_blank'
		}).submit();
	},
	
	// Volta para a listagem
	voltar : function() {
		
		document.location.href = paginaAnterior;
		
	},

	// Mostra a ajuda
	ajuda : function() {
		Base.ajuda();
	},

	// Fecha o documento
	sair : function() {
		Base.sair();
	},
    
    //Valida inconsist�ncias e exce��es na aplica��o.
    validaFormulario : function(){
    	var mensagem = Array();
    	var validamsg = true;
        var no_usuario = $("#no_usuario").val();
    	// Express�o para validar email
    	var exp = /[A-Za-z0-9._-]+@[A-Za-z]+\.[A-Za-z]+(\.[A-Za-z]{2})?/;
    	
    	// Testa se o email � v�lido
        if (! exp.test($("#email").val())){
        	mensagem.push("E-mail inv�lido.!");
                validamsg = false;
                
        }
         if (($.trim(no_usuario) != "") && ((no_usuario.length) < '5') ) 
         {
                mensagem.push("O Campo Nome Completo deve ter mais de 4 caracteres.!");
                validamsg = false;
			
			
        }
        if(validamsg)
        {
            mensagem.push("");    
        }
        
        
        // Chama a valida��o do sistema
        valida = Valida.formulario('form_base', true, mensagem);
        
        return valida;
    },
    
    // Retorna o funcion�rio pela matr�cula
	retornaFuncionarioX : function(nr_matric) {
		
		// Executa a requisi��o AJAX com retorno JSON
		$.getJSON(baseUrlController + "/retorna-funcionario-x/"
		, { 'nr_matric' : nr_matric } // Seta o parametro a ser passado por ajax	
		, function (retorno){ // Pega o retorno 
			
			// Verifica se foi retornado valor e se caso sim, mostra a mensagem na tela
			if(retorno.NR_MATRIC != undefined && retorno.NR_MATRIC != "") {
				
				// Joga o valor no campo
				$("#no_func").val(retorno.NO_FUNC);
				
			}
			
		} );
		
	},
    
    atualizaAba : function(ifrm) {
        Base.montaMensagemSistema(Array("Importa��o realizada com sucesso."), "Sucesso", 2);
        var caminho = $('#ifrm_0').attr("params");
        $("#ifrm_0").attr("src",caminho);
    },
       
    atualizaAbaNula : function(ifrm) {
        Base.montaMensagemSistema(Array("Grupo de usu�rio j� importado ou j� existente, tente outro usu�rio."), "Aten��o",4);
        var caminho = $('#ifrm_0').attr("params");
        $("#ifrm_0").attr("src",caminho);
    }
    
	
};