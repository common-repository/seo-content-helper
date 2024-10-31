<?php
class sch_keyword_distribution {
	function get_distribution_array( $keywords1, $keywords2, $content ) {
		$content_class = new sch_format();

		$content = $content_class->content_words_with_br( $content );

		$keywords1 = ( is_array( $keywords1 ) ) ? $keywords1 : array();
		$keywords2 = ( is_array( $keywords2 ) ) ? $keywords2 : array();
		$keywords_total = array_merge( $keywords1, $keywords2 );

		usort( $keywords_total, array( $this, 'usort_array_by_word_count') );

		foreach( $keywords_total as $keyword ) {
			$class1 = '';
			$class2 = '';
			if( in_array( $keyword, $keywords1 ) ) {
				$class1 = 'found1 green1';
			} elseif( in_array( $keyword, $keywords2 ) ) {
				$class2 = 'found2 green2';
			}
			$content = str_replace( $keyword, '<span class="' . $class1 . ' ' . $class2 . '">' . $keyword . '</span>', $content);
		}

		$content = $content_class->content_html_replaced_back( $content );
		return $content;
	}

	function usort_array_by_word_count($a, $b) {
		$a = str_word_count($a);
		$b = str_word_count($b);
		if ($a == $b) {
			return 0;
		}
		return ($a > $b) ? -1 : 1;
	}
}
?>