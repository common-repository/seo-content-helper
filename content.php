<?php
class sch_content {
	function content_to_words( $content ) {
		$content = wpautop( $content );
		$content = convert_chars( $content );
		$content = $this->remove_shortcodes( $content);
		$content = html_entity_decode( $content );
		$content = strip_tags( $content );
		$content = $this->keep_only_letters( $content );
		$content = mb_strtolower( $content );

		return $content;
	}

	function content_words_with_br( $content ) {
		$content = wpautop( $content );
		$content = convert_chars( $content );
		$content = mb_strtolower( $content );
		$content = $this->remove_shortcodes( $content);
		$content = $this->content_html_replaced( $content );

		$content = strip_tags( $content );
		$content = str_replace( array( "\n", "\r" ), array('', ''), $content );
		$content = $this->keep_only_letters( $content );
		return $content;
	}

	function content_html_replaced( $content ) {
		$single_linebreak = array( '</ul>', '</ol>', '</li>' );
		$double_linebreak = array( '</p>', '</address>', '</pre>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>' );

		$content = str_replace( array( '<h1>', '</h1>', '<h2>', '</h2>', '<h3>', '</h3>', '<h4>', '</h4>', '<h5>', '</h5>', '<h6>', '</h6>', '<li>', '<a', '</a>' ), array( 'schschh1start', 'schschh1end', 'schschh2start', 'schschh2end', 'schschh3start', 'schschh3end', 'schschh4start', 'schschh4end', 'schschh5start', 'schschh5end', 'schschh6start', 'schschh6end', 'schschli' ), $content );
		$content = str_replace( $single_linebreak, '1xlinebreak', $content );
		$content = str_replace( $double_linebreak, '2xlinebreak', $content );
		return $content;
	}

	function content_html_replaced_back( $content ) {
		$content = str_replace(
			array(
				'schschh1start', 'schschh1end', 'schschh2start', 'schschh2end', 'schschh3start', 'schschh3end', 'schschh4start', 'schschh4end', 'schschh5start', 'schschh5end', 'schschh6start', 'schschh6end', 'schschli' ),
			array(
				'<strong style="font-size: 18px; text-decoration: underline;">', '</strong><br><br>',
				'<strong style="font-size: 18px;">', '</strong><br><br>',
				'<strong style="font-size: 14px;">', '</strong><br><br>',
				'<strong style="text-transform: uppercase;">', '</strong><br><br>',
				'<strong style="text-transform: uppercase;">', '</strong><br><br>',
				'<strong style="text-transform: uppercase;">', '</strong><br><br>',
				' &bull; ' ),
			$content
		);
		$content = str_replace( array( '1xlinebreak', '2xlinebreak' ), array( '<br>', '<br><br>' ), $content );
		
		while(strstr($content, "<br><br><br>"))
		{
			$content = str_replace("<br><br><br>", "<br><br>", $content);
		}
		return $content;
	}

	function content_to_words_array( $content ) {
		$content_array = explode( ' ', $this->content_to_words( $content ) );
		return $content_array;
	}

	function get_single_keywords( $keywords ) {
		if( ! empty( $keywords ) ) {
			foreach( $keywords as $keyword ) {
				$keywords_split = explode( ' ', $keyword );
				foreach( $keywords_split as $keyword_split ) {
					$single_keywords[] = $keyword_split;
				}
			}
			return $single_keywords;
		}
	}

	function count_not_found_keyword( $keywords ) {
		var_dump($keywords1);
	}

	function remove_shortcodes( $content ) {
		$content = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $content);
		return $content;
	}

	function keep_only_letters( $content ) {
		$content = preg_replace('~[^\p{L}\p{N}]++~u', ' ', $content);
		return $content;
	}

	function get_post_title() {
		global $post;
		$title = $this->content_to_words( $post->post_title );
		return  $title;
	}
}
?>