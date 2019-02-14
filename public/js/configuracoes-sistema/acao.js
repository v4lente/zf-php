//Classe interna, carregada no OnLoad
var Interna = {
		
	// Método principal da classe
	init : function() {
		
		if(no_action == "novo" || no_action == "selecionar") {
			
			// Controla o campo que mostra o menu
			$("[name=fl_menu]").click(function() {
				
				// Limpa os campos
				$("#lnk_img_acao").val("");
				$("#ord_acao").val("");
				
				// Se marcar o campo "Sim" habilita a imagem e a ordenação, 
				// caso contrário coloca-os como readonly
				var valor = parseInt($(this).val());
				if(valor == 1) {
					$("#lnk_img_acao").removeAttr("readonly");
					$("#lnk_img_acao").removeAttr("tabindex");
					$("#lnk_img_acao").addClass("required");
					
					$("#ord_acao").removeAttr("readonly");
					$("#ord_acao").removeAttr("tabindex");
					$("#ord_acao").addClass("required");
					
					$("#lnk_img_acao").val($("#_lnk_img_acao").val());
					$("#ord_acao").val($("#_ord_acao").val());
					
				} else if(valor == 0) {
					$("#lnk_img_acao").attr("readonly", "readonly");
					$("#lnk_img_acao").attr("tabindex", "-1");
					$("#lnk_img_acao").removeClass("required");
					
					$("#ord_acao").attr("readonly", "readonly");
					$("#ord_acao").attr("tabindex", "-1");
					$("#ord_acao").removeClass("required");
				}
				
			});
			
			// Se vier com o valor zero, aplica o readonly nos campos
			if($("#fl_menu-0").is(':checked')) {
				
				// Limpa os campos
				$("#lnk_img_acao").val("");
				$("#ord_acao").val("");
				
				// Seta readonly nos campos
				$("#lnk_img_acao").attr("readonly", "readonly");
				$("#lnk_img_acao").attr("tabindex", "-1");
				$("#lnk_img_acao").removeClass("required");
				
				$("#ord_acao").attr("readonly", "readonly");
				$("#ord_acao").attr("tabindex", "-1");
				$("#ord_acao").removeClass("required");
				
			} else {
				$("#lnk_img_acao").addClass("required");
				$("#ord_acao").addClass("required");
			}
			
		}
	
	},

	// Gera um novo documento
	novo : function() {
		document.location.href = baseUrlController + "/novo";
	},

	// Salva o registro
	salvar : function() {
		
        // Faz a validação do formulário
		valida = Valida.formulario('form_base', true);

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

	// Seleciona um registro, recebendo a chave já criptografada
	selecionar : function(chave) {
		document.location.href = baseUrlController + "/selecionar/" + chave;
	},

	// Chama a tela de relatórios
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
	}
	
};