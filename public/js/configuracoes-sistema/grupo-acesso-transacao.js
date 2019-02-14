//Classe interna, carregada no OnLoad
var Interna = {

	// Método principal da classe
	init : function() {

		// Desassocia os eventos existentes no arquivo Evento.js
		$("#botao-relatorio").unbind("click");

		// Ao clicar no botão relatório, chama o método
		$("#botao-relatorio").click(function() {
			Interna.relatorio();
		});

		$("#botao-pesquisar").hide();
		$("#botao-limpar").hide();
		$("#botao-novo").hide();
		
		$("#no_sistema").blur(function() {
			if ($("#no_sistema").val() == "") {
				$("#cd_sistema").val("");

				//Atualiza o combo para a primeira posição apos cria-lo
				Form.atualizaCombo("#no_transacao",  "");
				Form.atualizaCombo("#no_menu",  "");
				
				$("#no_transacao_ac").attr("readonly", true);
				$("#no_menu_ac").attr("readonly", true);
			}

			if($("#no_sistema").val().indexOf("*") >= 0){
				$("#no_sistema").val("");
			}
		});

		// Aplica o autocomplete
		$("#no_sistema").autocomplete({
			source: baseUrlController + "/auto-complete-x?code=1&term="+$("#no_sistema").val(),
			minLength: 3,
			change: function(event, ui) {
				if (ui.item == undefined) {
					// Limpa os dados
					$("#cd_sistema").val("");
				}
			},
			select: function( event, ui ) {
				// Preenche os campos com o retorno do AJAX
				$("#cd_sistema").val(ui.item.id);
				$("#nome_sistema").val(ui.item.nome);

				Interna.montaComboMenu(ui.item.id);
			}
		});		

		// Aplica o autocomplete
		$("#no_grupo").autocomplete({
			source: baseUrlController + "/auto-complete-x?code=2&term="+$("#no_grupo").val(),
			minLength: 2,
			change: function(event, ui) {
				if (ui.item == undefined) {
					// Limpa os dados
					$("#cd_grupo").val("");
				}
			},
			select: function( event, ui ) {
				// Preenche os campos com o retorno do AJAX
				$("#cd_grupo").val(ui.item.id);
				$("#nome_grupo").val(ui.item.id);
			}
		});

		// Dispara o evento change do combo
		$("#no_menu_ac").bind("autocompleteselect", function(event, ui){
			Interna.montaComboTransacao($("#cd_sistema").val(), $(ui.item.option).val());
		});
	},

	// Gera um novo documento
	novo : function() {},

	// Salva o registro
	salvar : function() {},

	// Excluir o registro
	excluir : function() {},

	// Chama a tela de pesquisa
	telaPesquisa : function() {},

	// Executa a tela de pesquisa
	pesquisar : function() {},

	// Seleciona um registro, recebendo a chave já criptografada
	selecionar : function(chave) {},

	// Chama a tela de relatórios
	relatorio : function(metodo){

		var gerar = true;
		// Faz a validação do formulário
		valida = Valida.formulario('form_pesquisar', true);
		
		if(($("#no_sistema").val() == "") && ($("#no_grupo").val() == "")){

			Base.montaMensagemSistema(Array("Obrigatório informar pelo menos um campo!"), "Alerta", 4);
			gerar = false;
		}

		if (gerar) {
			if (valida[0]) {
				$('#form_pesquisar').attr({
					onsubmit: 'return true',
					action: baseUrlController + '/relatorio/_rel_metodo/PDF',
					target: '_blank'
				}).submit();
			}
		}
	},
	
	// Volta para a listagem
	voltar : function() {},

	// Mostra a ajuda
	ajuda : function() {
		Base.ajuda();
	},

	// Fecha o documento
	sair : function() {
		Base.sair();
	},

	montaComboMenu : function(cd_sistema){
		//Executa query para polpular as dropsdown turnos
		$.ajax({
			url: baseUrlController + "/retorna-dados-x?code=1&cd_sistema=" + cd_sistema,
			async : false,
			success: function(retorno) {

				// Monta o combo invisível
				Form.montaCombo("#no_menu", retorno, Array("CD_MENU", "NO_MENU"));
			}
		});

		Form.habilitaCamposById("no_menu");

		//Atualiza o combo para a primeira posição apos cria-lo
		// Form.atualizaCombo("#cd_cliente",  "");
	},

	montaComboTransacao : function(cd_sistema, cd_menu){
		//Executa query para polpular as dropsdown turnos
		$.ajax({
			url: baseUrlController + "/retorna-dados-x?code=2&cd_sistema=" + cd_sistema + "&cd_menu=" +cd_menu,
			async : false,
			success: function(retorno) {

				// Monta o combo invisível
				Form.montaCombo("#no_transacao", retorno, Array("CD_TRANSACAO", "NO_TRANSACAO"));
			}
		});

		Form.habilitaCamposById("no_transacao");

		//Atualiza o combo para a primeira posição apos cria-lo
		// Form.atualizaCombo("#cd_cliente",  "");
	}
};