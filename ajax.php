<?php
add_action('wp_ajax_sch_action', 'sch_ajax_callback');
add_action( 'admin_footer', 'sch_javascript' );

function sch_javascript() {
	global $post;
	?>
	<script type="text/javascript" >
		jQuery(document).ready(function($) {
			var data = {
				action: 'sch_action',
				url: '<?php echo get_permalink( $post->ID ); ?>',
				post_id: <?php echo $post->ID; ?>
			};
			console.log(ajaxurl);
			$.post(ajaxurl, data, function(response) {
				var json_obj = JSON.parse(response);
				console.log(response);
				if( json_obj.status ) {
					title_html = '<div class="distribution"><div class="title-nag"><div class="title-sep">Title tag</div></div></div>';
					title_html += '<div class="ajax-title"><p>' + json_obj.title + '</p></div>';

					description_html = '<div class="distribution"><div class="title-nag"><div class="title-sep">Meta description</div></div></div>';
					description_html += '<div class="ajax-title"><p>' + json_obj.description + '</p></div>';
					$('#sch .distribution-html .ajax').html( title_html + description_html );
				} else {
					$('#sch .distribution-html .ajax').html( json_obj.message );
				}
			});
		});
	</script>
	<?php
}

function get_dom( $content ) {
	$dom = new DOMDocument();
	@$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content );
	return $dom;
}

function sch_ajax_callback() {
	// Kolla efter fel http://codex.wordpress.org/Function_API/wp_remote_post
	$url = $_POST['url'];
	#$url = 'http://www.adasdasdas.com';
	$remote_data = wp_remote_post( $url );

	if( is_wp_error( $remote_data ) ) {
		#echo "Something went wrong: $error_message";
		$array['status'] = false;
		$array['message'] = $remote_data->get_error_message();
	} elseif( $remote_data ) {
		$content = $remote_data['body'];
		$dom = get_dom( $content );
		$h1 = h1_count_tags($dom);
		$title_count_chars = title_count_chars($dom);

		$content_class = new sch_format();
		$keyword_distribution_class = new sch_keyword_distribution();
		global $post;

		$sch_keywords = get_post_meta( $_POST['post_id'], 'sch_keywords', true );

		if( ! empty( $sch_keywords ) )
		{
			$keywords1 = $sch_keywords['keyword1'];
			$keywords2 = $sch_keywords['keyword2'];
			if( ! empty( $keywords1 ) )
				sort($keywords1);
			if( ! empty( $keywords2 ) )
				sort($keywords2);
		}

		$description = get_meta_description( $dom );
		$description = $keyword_distribution_class->get_distribution_array( $keywords1, $keywords2, $description );

		$array['title'] = str_replace( '<br>', '', $keyword_distribution_class->get_distribution_array( $keywords1, $keywords2, $title_count_chars['content'] ) ) . '<br>';
		$array['description'] = str_replace('<br>', '', $description) . '<br>';
		$array['status'] = true;
	}
	$json = json_encode($array);
	echo $json;
	die();
}

function count_tags( $dom, $tag ) {
	$xpath = new DomXPath($dom);
	$tag = $dom->getElementsByTagName( $tag );
	$counter = $tag->length;

	#print_r($counter);
	for ($i = 0; $i < $counter; $i++) {
		$result = $tag->item($i)->nodeValue;
	}
	return $i;
}

function get_characters( $dom, $tag ) {
	$xpath = new DomXPath($dom);
	$tag = $dom->getElementsByTagName( $tag );
	$counter = $tag->length;
	for ($i = 0; $i < $counter; $i++) {
		$result = $tag->item($i)->nodeValue;
	}
	$result = html_entity_decode( $result );
	return $result;
}

function title_count_chars($dom) {
	$content = get_characters( $dom, 'title' );
	$count = strlen( get_characters( $dom, 'title' ) );
	$array['count'] = $count;
	$array['message'] = ( $count > 70 ) ? 'Too many characters.' : 'Below 70 characters. Good!';
	$array['class'] = ( $count > 70 ) ? 'red' : 'green';
	$array['content'] = $content;
	return $array;
}

function get_meta_description( $dom ) {
	$metas = $dom->getElementsByTagName('meta');

	foreach ($metas as $meta) {
		if (strtolower($meta->getAttribute('name')) == 'description') {
			$description = $meta->getAttribute('content');
		}
	}
	return $description;
}

function h1_count_tags($dom) {
	$count = count_tags( $dom, 'h1' );
	$array['count'] = $count;
	if( $count > 1 ) {
		$array['message'] = "Only use this tag once per page.";
		$array['class'] = 'orange';
	} elseif( $count < 1 ) {
		$array['message'] = "You should have this tag on the page.";
		$array['class'] = 'red';
	} else {
		$array['message'] = "Good!";
		$array['class'] = 'green';
	}
	$array['class'] = ( $count != 1 ) ? 'orange' : 'green';
	return $array;
}