<?php
class sch_keyword_density {
	function get_density_array( $keywords1, $keywords2, $content ) {
		$content_class = new sch_format();
		$content = $content_class->content_to_words( $content );
		$content_array = $content_class->content_to_words_array( $content );

		$word_count_total = count( $content_array );
		$word_count_array = ( array_count_values( $content_array ) );
		arsort( $word_count_array );

		$single_keywords1 = $content_class->get_single_keywords( $keywords1 );
		$single_keywords2 = $content_class->get_single_keywords( $keywords2 );

		foreach ($word_count_array as $keyword => $word_count) {
			$found1 = ( is_array( $single_keywords1 ) && in_array( $keyword, $single_keywords1 ) ) ? true : false;
			$found2 = ( is_array( $single_keywords2 ) && in_array( $keyword, $single_keywords2 ) ) ? true : false;
			$density[] = array(
				'keyword' => $keyword,
				'count' => $word_count,
				'density' => number_format( ( $word_count / $word_count_total ) * 100, 2 ) . "%",
				'found1' => $found1,
				'found2' => $found2
			);
		}
		return $density;
	}

	function get_total_density( $content, $density_array ) {
		$content_class = new sch_format();
		$content = $content_class->content_to_words( $content );
		$content_array = $content_class->content_to_words_array( $content );
		$matches['total'] = count($content_array);

		if( ! empty( $density_array ) ) {
			foreach( $density_array as $item ) {
				if( $item['found1'] ) {
					$matches['found1'] += (int)$item['count'];
					$matches['all'] += (int)$item['count'];
				}
				elseif( $item['found2'] ) {
					$matches['found2'] += (int)$item['count'];
					$matches['all'] += (int)$item['count'];
				}
			}
		}
		if( ! empty( $matches['found1'] ) ) {
			$matches['found1_density'] = number_format( ( $matches['found1'] / $matches['total'] ) * 100, 2 ) . "%";
		}
		if( ! empty( $matches['found2'] ) ) {
			$matches['found2_density'] = number_format( ( $matches['found2'] / $matches['total'] ) * 100, 2 ) . "%";
		}
		if( ! empty( $matches['found1'] ) && ! empty( $matches['found2'] ) ) {
			$matches['all_density'] = number_format( ( ( $matches['found1'] + $matches['found2'] ) / $matches['total'] ) * 100, 2 ) . "%";
		}
		return $matches;
	}
}
?>