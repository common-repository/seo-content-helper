<?php
class sch_get_data {
	public $keywords = array();
	public $keywords1 = array();
	public $keywords2 = array();
	public $single_keywords1 = array();
	public $single_keywords2 = array();
	public $missing_keywords1_content = 0;
	public $missing_keywords1_post_title = 0;
	public $content;
	public $content_words;
	public $content_array;
	public $content_title_images_words;
	public $content_density;
	public $total_density;
	public $distribution_title;
	public $distribution_content;
	public $images_alt;
	public $images_class;
	public $images_filename;
	public $images_alt_found;
	public $images_filename_found;
	public $images_id;
	public $images_missing_alt;
	public $count_h1;
	public $count_h2;
	public $count_h3;
	public $count_h4;
	public $count_h5;
	public $count_h6;

	public function __construct( $post ) {
		$Format = new sch_format();
		$Density = new sch_keyword_density();
		$Distribution = new sch_keyword_distribution();
		$Xpath = new sch_xpath();

		$this->keywords = get_post_meta( $post->ID, 'seo_content_helper', true );

		/* Content */
		$this->content = $post->post_content;
		$this->content_words = $Format->content_to_words( $this->content );
		$this->content_title_images_words = $Format->content_to_words( $post->post_title . ' ' . $this->content );
		$this->content_array = $Format->content_to_words_array( $this->content );

		/* Keywords */
		$this->set_keywords( $this->keywords );
		$this->single_keywords1 = $Format->get_single_keywords( $this->keywords1 );
		$this->single_keywords2 = $Format->get_single_keywords( $this->keywords2 );

		$this->missing_keywords1_content = $Format->count_missing_keywords( $this->keywords1, $this->content_words );
		$this->found_keywords1_post_title = $Format->count_found_keywords( $this->keywords1, $post->post_title );

		/* Density */
		$this->content_density = $Density->get_density_array( $this->keywords1, $this->keywords2, $this->content);
		$this->total_density = $Density->get_total_density( $this->content, $this->content_density, 'all' );

		/* Distribution */
		$this->distribution_title = str_replace( '<br>', '', $Distribution->get_distribution_array( $this->keywords1, $this->keywords2, $post->post_title ) );
		$this->distribution_content = $Distribution->get_distribution_array( $this->keywords1, $this->keywords2, $this->content);

		/* Count */
		$this->count_h1 = $Xpath->count_tag( 'h1', $this->content );
		$this->count_h2 = $Xpath->count_tag( 'h2', $this->content );
		$this->count_h3 = $Xpath->count_tag( 'h3', $this->content );
		$this->count_h4 = $Xpath->count_tag( 'h4', $this->content );
		$this->count_h5 = $Xpath->count_tag( 'h5', $this->content );
		$this->count_h6 = $Xpath->count_tag( 'h6', $this->content );

		/* Images */
		$this->images_filename = $Xpath->find_attribute('img', 'src', $this->content);
		$this->images_alt = $Xpath->find_attribute('img', 'alt', $this->content);
		$this->images_class = $Xpath->find_attribute('img', 'class', $this->content);
		$this->images_html = $Xpath->find_attribute('img', null, $this->content);
		$this->images_missing_alt = $Xpath->count_missing_alt( $this->content );

		$this->images_alt_found = $this->alt();
		$this->images_filename_found = $this->image_url();
		$this->images_id = $this->images_id( $images_class );
	}

	public function set_keywords() {
		if( ! empty( $this->keywords ) )
		{
			$this->keywords1 = $this->keywords['keywords1'];
			$this->keywords2 = $this->keywords['keywords2'];
			if( ! empty( $this->keywords1 ) )
				sort( $this->keywords1 );
			if( ! empty( $this->keywords2 ) )
				sort( $this->keywords2 );
		}
	}

	public function alt() {
		$Format = new sch_format();
		$Distribution = new sch_keyword_distribution();
		if( ! empty( $this->images_alt ) ) {
			foreach( $this->images_alt as $alt ) {
				$alt_array[] = $Distribution->get_distribution_array( $this->keywords1, $this->keywords2, $alt);
			}
			return $alt_array;
		}
	}

	public function image_url() {
		$Format = new sch_format();
		$Distribution = new sch_keyword_distribution();
		if( ! empty( $this->images_filename ) ) {
			foreach( $this->images_filename as $filename ) {
				$filename = basename( $filename );
				$filename_array[] = str_replace( array('<br>'), array(''), $Distribution->get_distribution_array( $this->keywords1, $this->keywords2, $filename) ) ;
			}
			return $filename_array;
		}
	}

	public function images_id() {
		$Format = new sch_format();
		if( ! empty( $this->images_class ) ) {
			foreach( $this->images_class as $key => $class_att ) {
				$classes = explode( ' ', $class_att );
				foreach( $classes as $class ) {
					if( $Format->is_string_in_string( 'wp-image', $class ) ) {
						$ids[$key]['id'] = preg_replace('/\D/', '', $class);
					}
				}
				if( empty( $ids[$key]['id'] ) ) {
					$ids[$key]['id'] = false;
				}
			}
			return $ids;
		}
	}

	public function problem_array() {
		//$Get->content_array
		$message_start = '<strong>h2 tags</strong> - ';
		$message_end = '<span class="counter">' . $this->count_h2 . '</span>';
		if( $this->count_h2 == 0 ) {
			$message = 'No tags found. Add some!';
			$array['content_editor']['count_h2']['status'] = 2;
		} elseif( $this->count_h2 == 1 ) {
			$message = 'Some found. Too few!';
			$array['content_editor']['count_h2']['status'] = 1;
		} else {
			$message = 'Many found. Great!';
			$array['content_editor']['count_h2']['status'] = 0;
		}
		$array['content_editor']['count_h2']['message'] = $message_start . $message . $message_end;
		$array['content_editor']['count_h2']['count'] = $this->count_h2;

		$message_start = '<strong>h3-h6 tags</strong> - ';
		$h2_h6 = $this->count_h3 + $this->count_h4 + $this->count_h5 + $this->count_h6;
		$counter = ( $h2_h6 == 0 ) ? '' : $h2_h6;
		$message_end = '<span class="counter">' . $counter . '</span>';
		if( $h2_h6 == 0 ) {
			$message = 'No found. Add some!';
			$array['content_editor']['count_h3_h6']['status'] = 1;
		} else {
			$message = 'Found, great!';
			$array['content_editor']['count_h3_h6']['status'] = 0;
		}
		$array['content_editor']['count_h3_h6']['message'] = $message_start . $message . $message_end;
		$array['content_editor']['count_h3_h6']['count'] = $this->h2_h6;

		$message_start = '<strong>Title keywords</strong> - ';
		$counter = ( $this->found_keywords1_post_title == 0 ) ? '' : $this->found_keywords1_post_title;
		$message_end = '<span class="counter">' . $counter . '</span>';
		if( count( $this->keywords1 ) == 0 ) {
			$message = 'No primary added.';
			$array['content_editor']['missing_keywords1_post_title']['status'] = 2;
		} elseif( $this->found_keywords1_post_title == 0 ) {
			$message = 'No primary found.';
			$array['content_editor']['missing_keywords1_post_title']['status'] = 2;
		} else {
			$s = ( $this->found_keywords1_post_title != 1 ) ? 's' : '';
			$message = 'Primary found.';
			$array['content_editor']['missing_keywords1_post_title']['status'] = 0;
		}
		$array['content_editor']['missing_keywords1_post_title']['message'] = $message_start . $message . $message_end;
		$array['content_editor']['missing_keywords1_post_title']['count'] = $this->found_keywords1_post_title;

		$number_words = count( $this->content_array );
		$message_start = '<strong>Content words</strong> - ';
		$counter = ( $number_words == 0 ) ? '' : $number_words;
		$message_end = '<span class="counter">' . $counter . '</span>';
		if( $number_words < 300 ) {
			$message = 'Content too short!';
			$array['content_editor']['number_words']['status'] = 2;
		} elseif( $number_words < 500 ) {
			$message = 'Content too short!';
			$array['content_editor']['number_words']['status'] = 1;
		} else {
			$message = 'Great content length!';
			$array['content_editor']['number_words']['status'] = 0;
		}
		$array['content_editor']['number_words']['message'] = $message_start . $message . $message_end;
		$array['content_editor']['number_words']['count'] = $number_words;

		$message_start = '<strong>Content keywords</strong> - ';
		$counter = ( $this->missing_keywords1_content == 0 ) ? '' : $this->missing_keywords1_content;
		$message_end = '<span class="counter">' . $counter . '</span>';
		if( count( $this->keywords1 ) == 0 ) {
			$message = 'No primary added.';
			$array['content_editor']['missing_keywords1_content']['status'] = 2;
		} elseif( $this->missing_keywords1_content > 0 ) {
			$message = 'Primary missing.';
			$array['content_editor']['missing_keywords1_content']['status'] = 2;
		} else {
			$message = 'Primary found.';
			$array['content_editor']['missing_keywords1_content']['status'] = 1;
		}
		$array['content_editor']['missing_keywords1_content']['message'] = $message_start . $message . $message_end;
		$array['content_editor']['missing_keywords1_content']['count'] = $this->missing_keywords1_content;

		$number_images = count( $this->images_filename );
		$message_start = '<strong>Images</strong> - ';
		$counter = ( $number_images == 0 ) ? '' : $number_images;
		$message_end = '<span class="counter">' . $counter . '</span>';
		if( $number_images == 0 ) {
			$message = 'No found. Add some!';
			$array['images']['image_count']['status'] = 2;
		} else {
			$message = 'Found, great!';
			$array['images']['image_count']['status'] = 0;
		}
		$array['images']['image_count']['message'] = $message_start . $message . $message_end;

		$message_start = '<strong>Missing alt tags</strong> - ';
		$counter = ( $this->images_missing_alt == 0 ) ? '' : $this->images_missing_alt;
		$message_end = '<span class="counter">' . $counter . '</span>';
		if( $number_images == 0 ) {
			$message = 'No images!';
			$array['images']['images_missing_alt']['status'] = 2;
		} elseif( $this->images_missing_alt > 0 ) {
			$message = 'Add some!';
			$array['images']['images_missing_alt']['status'] = 2;
		} else {
			$message = 'All found, great!';
			$array['images']['images_missing_alt']['status'] = 0;
		}
		$array['images']['images_missing_alt']['message'] = $message_start . $message . $message_end;

		return $array;
	}

	function count_errors( $array ) {
		$errors = array();
		$errors[0] = 0;
		$errors[1] = 0;
		$errors[2] = 0;
		foreach( $array as $key => $section ) {
			foreach( $section as $item ) {
				if( $item['status'] == 0 ) {
					$errors[0]++;
				} elseif( $item['status'] == 1 ) {
					$errors[1]++;
				} elseif( $item['status'] == 2 ) {
					$errors[2]++;
				}
			}
		}
		return $errors;
	}
}