<?php
/*
Plugin Name: SEO Content Helper
Plugin URI: http://www.xn--finanshjlpen-ncb.se/plugins/seo-content-helper/
Description: Helps to write content with search engines in mind, based apon your keywords.
Version: 1.0
Author: Jens Törnell
Author URI: http://www.jenst.se
*/

require_once dirname( __FILE__ ) . '/get-data.php';
require_once dirname( __FILE__ ) . '/xpath.php';
require_once dirname( __FILE__ ) . '/keyword-density.php';
require_once dirname( __FILE__ ) . '/keyword-distribution.php';
require_once dirname( __FILE__ ) . '/formatting.php';
require_once dirname( __FILE__ ) . '/ajax.php';
require_once dirname( __FILE__ ) . '/options.php';
require_once dirname( __FILE__ ) . '/metabox.php';

add_action( 'admin_print_styles-post-new.php', 'sch_enqueue' );
add_action( 'admin_print_styles-post.php', 'sch_enqueue' );

function sch_enqueue() {
	wp_enqueue_style( 'sch-style', plugins_url( 'style.css', __FILE__ ) );
	wp_enqueue_script( 'sch-script', plugins_url( 'metabox-tabs.js', __FILE__ ), array( 'jquery' ) );
}

class sch_core {
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_fields' ) );
	}

	public function default_options( $options ) {
		if( ! $options ) {
			$options = array(
				'post_types' => array( 'post', 'page' )
			);
		}
		return $options;
	}

	public function add_meta_box() {
		$options = $this->default_options( get_option('seo_content_helper_settings') );
		$post_types = $options['post_types'];
		$post_type = get_post_type();
		
		if( ! empty( $post_type ) ) {
			if( is_array( $post_types ) && in_array( $post_type, $post_types ) ) {
				add_meta_box(
						'sch_metabox',
						'SEO Content Helper',
						array( $this, 'sch_metabox' ),
						$post_type,
						'side',
						'high'
				);
			}
		}
	}

	public function sch_metabox( $post ) {
		$Get = new sch_get_data( $post );
		echo $Get->missing_keywords1;
		?>
		<div id="sch">
			<div class="tabs" class="keyword-tabs">
				<div class="keywords1">
					<p><strong>Primary keywords</strong></p>
					<span class="words">
						<?php if( ! empty( $Get->keywords1 ) ) : ?>
							<?php foreach( $Get->keywords1 as $keyword1 ) :
								$count = substr_count( $Get->content_title_images_words, $keyword1 );
								$keyword_count = ( $count > 1 ) ? ' <span>(' . $count . ')</span>' : '';
								$found = ( $count > 0 ) ? ' green1' : ' red1';
							?>
								<input type="hidden" name="keyword1[]" value="<?php echo $keyword1; ?>" data-value="<?php echo $keyword1; ?>">
								<span class="keyword keyword1 tab<?php echo $found; ?>" data-value="<?php echo $keyword1; ?>"><?php echo $keyword1 . $keyword_count; ?></span>
							<?php endforeach; ?>
						<?php endif; ?>
					</span>
					<span class="add tab" data-position="1"></span>
				</div>

				<div class="keywords2">
					<p><strong>Secondary keywords</strong></p>
					<span class="words">
						<?php if( ! empty( $Get->keywords2 ) ) : ?>
							<?php foreach( $Get->keywords2 as $keyword2 ) :
								$count = substr_count( $Get->content_title_images_words, $keyword2 );
								$keyword_count = ( $count > 1 ) ? ' <span>(' . $count . ')</span>' : '';
								$found = ( $count > 0 ) ? ' green2' : ' red2';
							 ?>
								<input type="hidden" name="keyword2[]" value="<?php echo $keyword2; ?>" data-value="<?php echo $keyword2; ?>">
								<span class="keyword keyword2 tab<?php echo $found; ?>" data-value="<?php echo $keyword2; ?>"><?php echo $keyword2 . $keyword_count; ?></span>
							<?php endforeach; ?>
						<?php endif; ?>
					</span>
					<span class="add tab" data-position="2"></span>
				</div>				
			</div>
			<div class="add-form">
				<p><strong>Add keyword</strong></p>
				<select>
					<option value="keyword1" data-position="1">Primary keywords</option>
					<option value="keyword2" data-position="2">Secondary keywords</option>
				</select><br>
				<textarea></textarea>
				<span class="submit-keywords button button-small alignright">Submit keywords</span>
			</div>

			<div class="edit-form">
				<p><strong>Rename keyword</strong></p>
				<input type="text" class="edit-keyword">

				<select>
					<option value="keyword1">Primary keyword</option>
					<option value="keyword2">Secondary keyword</option>
				</select>

				<span class="cancel-edit-keyword button button-small alignright">Close</span>
				<span class="delete-keyword button button-small alignright">Delete</span>
			</div>
			<ul class="accordion">
				<li>
					<?php $array = $Get->problem_array(); ?>
					<?php $count_errors = $Get->count_errors($array); ?>
					<div class="title"><span>SEO Analyzer<span class="red"><?php echo $count_errors['2']; ?></span><span class="orange"><?php echo $count_errors['1']; ?></span><span class="green"><?php echo $count_errors['0']; ?></span></span></div>
					<ul class="seo-analyzer">
						<li>
							<?php foreach( $array as $key => $section ) : ?>
								<div class="title-nag"><div class="title-sep"><?php echo $key; ?></div></div>
								<?php foreach( $section as $item ) :
									$class = str_replace( array(0, 1, 2), array('green','orange','red'), $item['status'] );
									?><p class="<?php echo $class; ?>"><?php echo $item['message']; ?></p>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</li>
					</ul>
				</li>
				<li>
					<div class="title"><span>Distribution - Editor</span></div>
					<ul>
						<li>
							<div class="distribution">
								<div class="title-nag">
									<div class="title-sep">Post title</div>
								</div>
								<?php echo $Get->distribution_title; ?><br>
								<div class="title-nag">
									<div class="title-sep">Content</div>
								</div>
								<?php echo $Get->distribution_content; ?>
							</div>
						</li>
					</ul>
				</li>
				<li>
					<div class="title"><span>Distribution - HTML</span></div>
					<ul class="distribution-html">
						<li>
							<div class="ajax"><img src="<?php echo plugins_url( 'img/ajax-loader.gif', __FILE__ ); ?>"></div>
						</li>
					</ul>
				</li>
				<li>
					<div class="title"><span>Keyword density</span></div>
					<ul>
						<li>
							<table class="form-table keyword-summery">
								<?php if( ! empty( $Get->total_density['found1'] ) ) : ?>
								<tr>
									<td><strong>Primary keywords:</strong></td>
									<td><?php echo $Get->total_density['found1']; ?> (<?php echo $Get->total_density['found1_density']; ?>)</td>
								</tr>
								<?php endif; ?>
								<?php if( ! empty( $Get->total_density['found2'] ) ) : ?>
								<tr>
									<td><strong>Secondary keywords:</strong></td>
									<td><?php echo $Get->total_density['found2']; ?> (<?php echo $Get->total_density['found2_density']; ?>)</td>
								</tr>
								<?php endif; ?>
								<?php if( ! empty( $Get->total_density['found1'] ) && ! empty( $Get->total_density['found2'] ) ) : ?>
								<tr>
									<td><strong>All keywords:</strong></td>
									<td><?php echo $Get->total_density['all']; ?> (<?php echo $Get->total_density['all_density']; ?>)</td>
								</tr>
								<?php endif; ?>
							</table>
							<table class="form-table keyword-density">							
								<tr>
									<th class="keyword">Keyword</th>
									<th class="count">Count</th>
									<th class="density">Density</th>
								</tr>
								<div class="density-all">
									<?php
									if( ! empty( $Get->content_density ) ) :
										foreach( $Get->content_density as $item ) :
											$length = mb_strlen( $item['keyword'], 'UTF-8' );
											$class1 = ( $item['found1'] === true ) ? 'green1' : '';
											$class2 = ( $item['found2'] === true ) ? 'green2' : '';
											if( $length > 3 || $item['found1'] === true || $item['found2'] === true ) :
												if( $item['count'] > 1 || $item['found1'] === true || $item['found2'] === true ) :
												?>
													<tr>
														<td class="keyword">
															<div class="<?php echo $class1 . ' ' . $class2; ?>"><?php echo $item['keyword']; ?></div>
														</th>
														<td class="count">
															<div class="<?php echo $class1 . ' ' . $class2; ?>"><?php echo $item['count']; ?></div>
														</td>
														<td class="density">
															<div class="<?php echo $class1 . ' ' . $class2; ?>"><?php echo $item['density']; ?></div>
														</td>
													</tr>
												<?php
												endif;
											endif;
										endforeach;
									endif;
								?>
								</div>
							</table>
						</li>
					</ul>
				</li>
				<li class="section-images">
					<div class="title"><span>Images</span></div>
					<ul>
						<li>
							<div class="section-title"><?php echo count($Get->images_filename) . ' images found in content'; ?></div>
							<?php if( ! empty( $Get->images_filename ) ) : ?>
								<?php foreach( $Get->images_filename as $key => $filename ) :
									$alt = ( ! empty( $Get->images_alt_found[$key] ) ) ? $Get->images_alt_found[$key] : '-';
									$filename = ( ! empty( $Get->images_filename_found[$key] ) ) ? $Get->images_filename_found[$key] : '-';
									$edit_url = ( ! empty( $Get->images_id[$key]['id'] ) ) ? get_bloginfo('wpurl') . '/wp-admin/media.php?attachment_id=' . $Get->images_id[$key]['id'] . '&action=edit' : '';
									?>
									<div class="image-block">
										<div class="small-images" style="width: 50px; height: 50px;">
											<?php if( ! empty( $edit_url ) ) : ?><a target="_blank" href="<?php echo $edit_url; ?>"><?php endif; ?>
											<?php echo $Get->images_html[$key]; ?>
											<?php if( ! empty( $edit_url ) ) : ?></a><?php endif; ?>
										</div>
										<div class="image-data">
											<strong>Filename:</strong><br><?php echo $filename; ?><br><br>
											<strong>Alt-attribute:</strong><br><?php echo strtolower( $alt ); ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<?php
	}

	function save_fields( $post_id ) {
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return;
		}

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

		$sch_keywords['keywords1'] = $_POST['keyword1'];
		$sch_keywords['keywords2'] = $_POST['keyword2'];
		update_post_meta( $post_id, 'seo_content_helper', $sch_keywords );
	}
}
new sch_core;
?>