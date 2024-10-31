<?php
class sch_settings {
	private $general_settings_key = 'seo_content_helper_settings';
	private $instructions_key = 'seo_content_helper_instructions';
	private $contribute_key = 'seo_content_helper_contribute';
	private $advanced_settings_key = 'my_advanced_settings';
	private $plugin_options_key = 'seo_content_helper_settings';
	private $plugin_settings_tabs = array();
	
	function __construct() {
		add_action( 'init', array( &$this, 'load_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_instructions' ) );
		add_action( 'admin_init', array( &$this, 'register_contribute' ) );
		#add_action( 'admin_init', array( &$this, 'register_advanced_settings' ) );
		add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );
	}
	
	function load_settings() {
		$this->general_settings = (array) get_option( $this->general_settings_key );
		$this->advanced_settings = (array) get_option( $this->advanced_settings_key );
		
		$this->general_settings = array_merge( array(
			'general_option' => 'General value'
		), $this->general_settings );
		
		$this->advanced_settings = array_merge( array(
			'advanced_option' => 'Advanced value'
		), $this->advanced_settings );
	}
	
	function register_general_settings() {
		$this->plugin_settings_tabs[$this->general_settings_key] = 'Settings';
		
		register_setting( $this->general_settings_key, $this->general_settings_key );
		add_settings_section( 'section_general', 'SEO Content Helper - On which post types?', array( &$this, 'section_general_desc' ), $this->general_settings_key );
		add_settings_field( 'general_option', 'Activate the SEO Helper meta box on one or more post types.', array( &$this, 'field_general_option' ), $this->general_settings_key, 'section_general' );
	}
	
	function register_advanced_settings() {
		$this->plugin_settings_tabs[$this->advanced_settings_key] = 'Advanced';
		
		register_setting( $this->advanced_settings_key, $this->advanced_settings_key );
		add_settings_section( 'section_advanced', 'Advanced Plugin Settings', array( &$this, 'section_advanced_desc' ), $this->advanced_settings_key );
		add_settings_field( 'advanced_option', 'An Advanced Option', array( &$this, 'field_advanced_option' ), $this->advanced_settings_key, 'section_advanced' );
	}

	function register_instructions() {
		$this->plugin_settings_tabs[$this->instructions_key] = 'Instructions';
		add_settings_section( 'section_instructions', 'Advanced Plugin Settings', array( &$this, 'section_instructions' ), $this->instructions_key );
	}

	function register_contribute() {
		$this->plugin_settings_tabs[$this->contribute_key] = 'Contribute';
		add_settings_section( 'section_contribute', 'Help me pay my bills', array( &$this, 'section_contribute' ), $this->contribute_key );
	}
	
	function section_general_desc() { echo ''; }
	function section_advanced_desc() { echo ''; }
	function section_instructions() {
		?>
		<ol>
			<li><p><strong>Choose post types</strong></p><p>Go to settings. Choose post types. A meta box will be added on the post types you select.</p></li>
			<li><p><strong>Add keywords</strong></p><p> Go to a post (or other post type). Find the SEO Content Helper meta box. Add some keywords.</p></li>
			<li><p><strong>Analyze and improve your post.</strong></p><p>Look at your keyword density, keyword distribution and so on.</p></li>
		</ol>
		<?php
	}

	function section_contribute() {
		?>
		<p>Support this plugin by making a donation.</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
		">
		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypalobjects.com/sv_SE/i/scr/pixel.gif" width="1" height="1">
		</form>

		<h3>Write a blog post</h3>
		<p>One of the best things you can do is to write a review of SEO Content Helper and link to us.</p>

		<h3>Spread the word</h3>
		<ul>
			<li>- Link to SEO Content Helper</li>
			<li>- Share this page in social media like Twitter and Facebook</li>
			<li>- Give us a Google +1</li>
		</ul>

		<h3>Vote us up</h3>
		<ul>
			<li>- On <a href="http://wordpress.org/extend/plugins/seo-content-helper/">http://wordpress.org/extend/plugins/seo-content-helper/</a></li>
			<li>- On <a href="">fd</a></li>
		</ul>

		<h3>Report a bug</h3>
		<p>Let us know if you find a bug. It helps us to fix it. Give us as much information as you can, what steps you made so we can repeat the process.</p>

		<h3>Suggest a feature</h3>
		<p>Have an idea? Let us know.</p>
		<?php
	}
	
	function field_general_option() {
		?>
		<select name="<?php echo $this->general_settings_key; ?>[post_types][]" multiple>
			<?php
				$blacklist = array('attachment', 'revision', 'nav_menu_item');
				$post_types = get_post_types($args,'names'); 
				
					foreach ($post_types as $post_type ) {
						if( ! in_array( $post_type, $blacklist ) ) {
							$selected = ( ! empty( $this->general_settings['post_types'] ) && in_array( $post_type, $this->general_settings['post_types'] ) ) ? ' selected' : '';
							echo '<option value="' . $post_type . '"' . $selected . '>' . $post_type . '</option>';
						}
					}
			?>
		</select>
		<?php
	}
	
	function field_advanced_option() {
		?>
		<input type="text" name="<?php echo $this->advanced_settings_key; ?>[advanced_option]" value="<?php echo esc_attr( $this->advanced_settings['advanced_option'] ); ?>" />
		<?php
	}
	
	function add_admin_menus() {
		add_options_page( 'SEO Content Helper', 'SEO Content Helper', 'manage_options', $this->plugin_options_key, array( &$this, 'plugin_options_page' ) );
	}
	
	function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
		?>
		<div class="wrap">
			<?php $this->plugin_options_tabs(); ?>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>
				<?php settings_fields( $tab ); ?>
				<?php do_settings_sections( $tab ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
	function plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
		}
		echo '</h2>';
	}
};

add_action( 'plugins_loaded', create_function( '', '$sch_settings = new sch_settings;' ) );
?>