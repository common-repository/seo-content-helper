jQuery(document).ready(function($) {
	// Add
	$('#sch').on("click", '.add.tab', function(e){
		$('#sch .add-form').toggle();

		$('#sch .add-form select').val( 'keyword' + $(this).attr('data-position') );

		$('#sch .add.tab').toggleClass('active');
		$('#sch .edit-form').hide();
	});

	// Edit toggle click
	$('#sch').on("click", '.keyword.tab', function(e){
		$('#sch .edit-form').show();
		$('#sch .add-form').hide();
		$('#sch .add.tab').removeClass('active');
		$('#sch .edit-form input[type="text"]').val( $(this).prev().val() );
		$('#sch .edit-form input[type="text"]').attr( 'data-old-keyword', $(this).prev().val() );

		if( $(this).hasClass('keyword1') ) {
			$('#sch .edit-form select').val('keyword1');
		} else if( $(this).hasClass('keyword2') ) {
			$('#sch .edit-form select').val('keyword2');
		}
	});

	// Cancel
	$('#sch').on("click", '.cancel-edit-keyword', function(e){
		$('#sch .edit-form').hide();
	});

	// Save keyword
	$('#sch').on("keyup", '.edit-form input[type="text"]', function(e){
		$('#sch .keyword.tab').filter(function() {
			var keyword = $('#sch .edit-form .edit-keyword');
			var select_priority = $('#sch .edit-form select');
			if( $(this).prev().val() == keyword.attr('data-old-keyword') ) {
				$(this).html( keyword.val() );

				$(this).attr('data-value', keyword.val() );
				$(this).prev().attr('data-value', keyword.val() );

				$(this).prev().val( keyword.val() );
				keyword.attr( 'data-old-keyword', keyword.val() );
				$(this).removeClass('found not-found keyword1 keyword2');

				$(this).prev().attr( 'name', select_priority.val() + '[]' );
				$(this).addClass( select_priority.val() );
			}
		});
	});

	// Select
	$('#sch').on("change", '.edit-form select', function(e){
		var keyword = $(this).parent().find('.edit-keyword').val();
		var destination = $(this).val();

		if( destination == 'keyword1' ) {
			$("#sch .keywords2 .keyword[data-value='" + keyword + "']").prependTo("#sch .keywords1 .words").removeClass('keyword2').addClass('keyword1');
			$("#sch .keywords2 input[data-value='" + keyword + "']").prependTo("#sch .keywords1 .words").attr('name', 'keywords1[]');
		} else if( destination == 'keyword2' ) {
			$("#sch .keywords1 .keyword[data-value='" + keyword + "']").prependTo("#sch .keywords2 .words").removeClass('keyword1').addClass('keyword2');
			$("#sch .keywords1 input[data-value='" + keyword + "']").prependTo("#sch .keywords2 .words").attr('name', 'keywords2[]');
		}
	});

	// Delete keyword
	$('#sch').on("click", '.delete-keyword', function(e){
		$('#sch .keyword.tab').filter(function() {
			var edit_form_text = $('#sch .edit-form input[type="text"]');
			if( $(this).attr('data-value') == edit_form_text.val() ) {
				$(this).prev().remove();
				$(this).remove();
			}
		});
		$('#sch .edit-form').hide();
	});

	// Save add
	$("#sch").on("click", ".submit-keywords", function(e){
		e.preventDefault();
		var keyword_type = $(this).parent().find('select').val();

		var lines = $(this).prev().val().split(/\n/);
		var texts = []
		for (var i=0; i < lines.length; i++) {
			if (/\S/.test(lines[i])) {
				texts.push($.trim(lines[i]));
			}
		}

		var selected = $(this).parent().find('select').val();
		if( selected == 'keyword1') {
			position = '1';
		} else if ( selected == 'keyword2' ) {
			position = '2';
		}

		$.each(texts, function(index, value) {
			$('#sch .keywords' + position + ' .words').prepend('<span class="keyword ' + keyword_type + ' tab" data-value="' + value.toLowerCase() + '">' + value.toLowerCase() + '</span>');
			$('#sch .keywords' + position + ' .words').prepend('<input type="hidden" name="' + keyword_type + '[]" value="' + value.toLowerCase() + '" data-value="' + value.toLowerCase() + '">');
		});

		$(this).parent().find('textarea').val('');
		$('#sch .add-form').hide();
		$('#sch .add.tab').removeClass('active');
	});

	// Accordion
	$("#sch").on("click", ".accordion > li > .title", function(e){
		$('#sch .accordion .title').removeClass('active');
		if (!$(this).next().is(':visible')) {
			$(this).parent().siblings().children("ul").slideUp(0);
			$(this).addClass('active');
		}
		$(this).next().slideToggle(0);
	});
});