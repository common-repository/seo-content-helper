<?php
class sch_xpath {
	function find_attribute( $tag, $attribute = null, $content ) {
		$content_class = new sch_format();
		$content = $content_class->remove_shortcodes( $content );

		$html = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
		$dom = @DOMDocument::loadHTML( $html );
		if( ! empty( $dom ) )
		{
			$xpath = new DOMXPath( $dom );
			$query = $xpath->query( '//' . $tag );
			$result = '';

			foreach ($query as $node){
				if( $attribute == null )
					$result[] = $dom->saveXml($node);
				else
					$result[] = $node->getAttribute($attribute);
			}
			if( ! empty( $result ) )
				return $result;
		}
	}

	function count_missing_alt($html) {
		if( ! empty( $html ) ) {
			$dom = new DOMDocument();
			$dom->loadHTML($html);
			$images = $dom->getElementsByTagName("img");
			$missing_alt_count = 0;
			if( ! empty( $images ) ) {
				foreach( $images as $image ) {
					$alt = $image->getAttribute('alt');
					if ( ! $image->hasAttribute("alt") || empty( $alt ) ) {
						$missing_alt_count++;
					}
				}
			}
			return $missing_alt_count;
		}
	}

	function count_tag( $tag, $html ) {
		if( ! empty( $html ) ) {
			$dom = new DOMDocument;
			$dom->loadHTML( $html );
			$elements = $dom->getElementsByTagName( $tag );
			return $elements->length;
		}
	}
}
?>