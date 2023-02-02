<?php
/*
Plugin Name: Advanced Custom Fields: Flexible Layout Templates
Plugin URI: https://github.com/devgeniem/acf-flexible-templates
Description: Create ready made templates for your Flexible Content pages.
Version: 0.0.4
Author: Miika Arponen / Geniem
Author URI: https://github.com/devgeniem
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: acf-flexible-templates
*/

class ACF_Flexible_Templates {

	/*
	 * Hook actions and filters in place
	 */
	public function __construct() {
		add_action( "init", array( $this, "register_post_type" ) );

		add_action( "add_meta_boxes", array( $this, "page_attributes" ) );
		
		add_action( "save_post", array( $this, "save_page_attributes" ) );

		add_action( "admin_menu", array( $this, "menu" ) );

		add_action( "admin_init", array( $this, "handle_creations" ) );

		add_action( "admin_notices", array( $this, "admin_notice" ) );
	}

	public function register_post_type() {
	
		$labels = array(
			'name'                => __( 'Layout Templates', 'acf-flexible-templates' ),
			'singular_name'       => __( 'Layout Template', 'acf-flexible-templates' ),
			'add_new'             => _x( 'Add New Layout Template', 'acf-flexible-templates', 'acf-flexible-templates' ),
			'add_new_item'        => __( 'Add New Layout Template', 'acf-flexible-templates' ),
			'edit_item'           => __( 'Edit Layout Template', 'acf-flexible-templates' ),
			'new_item'            => __( 'New Layout Template', 'acf-flexible-templates' ),
			'view_item'           => __( 'View Layout Template', 'acf-flexible-templates' ),
			'search_items'        => __( 'Search Layout Templates', 'acf-flexible-templates' ),
			'not_found'           => __( 'No Layout Templates found', 'acf-flexible-templates' ),
			'not_found_in_trash'  => __( 'No Layout Templates found in Trash', 'acf-flexible-templates' ),
			'parent_item_colon'   => __( 'Parent Layout Template:', 'acf-flexible-templates' ),
			'menu_name'           => __( 'Layout Templates', 'acf-flexible-templates' ),
		);
	
		$args = array(
			'labels'                   => $labels,
			'hierarchical'        => false,
			'description'         => __("Create layout templates for ACF Flexible Content Type", "acf-flexible-templates"),
			'taxonomies'          => array(),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => null,
			'menu_icon'           => null,
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => false,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
		);
	
		register_post_type( 'layout-template', $args );
	}
	
	public function page_attributes( $post_type ) {
		if ( "layout-template" == $post_type ) {
			remove_meta_box(
		        'pageparentdiv',
		        'layout-template',
		        'side'
		    );

		    remove_meta_box(
		    	'submitdiv',
		    	'layout-template',
		    	'side'
		    );

		    add_meta_box(
		        'acf-fl-page-attributes',
		        __("Page Attributes", "acf-flexible-templates"),
		        array( $this, 'page_attributes_callback' ), 
		        'layout-template', 
		        'side', 
		        'low'
		    );

		    add_meta_box(
		    	'acf-fl-submitdiv',
		    	__("Save", "acf-flexible-templates"),
		    	array( $this, 'page_attributes_save_box' ),
		    	'layout-template',
		    	'side',
		    	'high'
		    );
		}
	}

	public function page_attributes_callback( $post_id ) {
		global $post;

		$template = get_post_meta( $post_id, "_wp_page_template" );
		?>
		<p><strong><?php _e('Template') ?></strong><?php do_action( 'page_attributes_meta_box_template', $template, $post ); ?></p>
		<label class="screen-reader-text" for="page_template"><?php _e('Page Template') ?></label><select name="_wp_page_template" id="page_template">
		<?php $default_title = apply_filters( 'default_page_template_title',  __( 'Default Template' ), 'meta-box' ); ?>
		<option value="default"><?php echo esc_html( $default_title ); ?></option>
		<?php page_template_dropdown( $template ); ?>
		</select>
		<?php
	}

	public function save_page_attributes( $post_id ) {
		if ( "layout-template" == get_post_type( $post_id ) ) {
			if ( isset( $_REQUEST["_wp_page_template"] ) ) {
				update_post_meta( $post_id, "_wp_page_template", $_REQUEST["_wp_page_template"] );
			}
		}
	}

	public function page_attributes_save_box( $post_type ) {
		?>
		<div id="publishing-action">
			<span class="spinner"></span>
			<input type="submit" accesskey="p" value="<?php echo __("Save"); ?>" class="button button-primary button-large" id="publish" name="publish">
		</div>
		
		<div class="clear"></div>
		<?php
	}

	public function menu() {
		$templates = $this->get_templates();

		foreach ( $templates as $template ) {
			add_submenu_page( "edit.php?post_type=page", __("New page from ", "acf-flexible-templates") . $template->post_title, __("New page from ", "acf-flexible-templates") . $template->post_title, "edit_posts", "acf_ft_new_" . $template->post_name, "__return_false" );
		}
	}

	public function create_post_from_template( $post_id ) {
		$old_post = get_post( $post_id );

		if ( $old_post->post_type !== "layout-template" || is_null( $old_post ) ) {
			return false;
		}

		$new_post = array(
			"post_author" => get_current_user_id(),
			"post_content" => $old_post->post_content,
			"post_excerpt" => $old_post->post_excerpt,
			"menu_order" => $old_post->menu_order,
			"comment_status" => $old_post->comment_status,
			"ping_status" => $old_post->ping_status,
			"post_mime_type" => $old_post->post_mime_type,
			"post_status" => "draft",
			"post_title" => __("Created from ", "acf-flexible-templates") . $old_post->post_title,
			"post_type" => "page"
		);

		$new_post_id = wp_insert_post( $new_post );

		$old_post_meta = get_metadata( "post", $post_id );

		foreach ( $old_post_meta as $key => $value ) {
			if ( "key" !== "_edit_lock" ) {
				foreach ( $value as $val ) {
					update_metadata( "post", $new_post_id, $key, $value[0] );
				}
			}
		}

		add_metadata( "post", $new_post_id, "_acf_ft_template_id", $post_id );

		return $new_post_id;
	}

	public function get_templates() {
		if ( ! isset( $this->templates ) ) {
			$templates = get_posts( array(
				"post_type" => "layout-template",
			) );

			$return = array();

			foreach ( $templates as $template ) {
				$return[ $template->post_name ] = $template;
			}

			$this->templates = $return;
		}

		return $this->templates;
	}

	public function handle_creations() {
		if ( preg_match( "/acf_ft_new_(.+)/", $_SERVER["QUERY_STRING"], $matches ) ) {
			if ( count( $matches ) > 1 ) {
				$slug = $matches[1];

				$templates = $this->get_templates();

				if ( isset( $templates[ $slug ] ) ) {

					$id = $this->create_post_from_template( $templates[ $slug ]->ID );

					wp_redirect( admin_url( "post.php?post=" . $id ."&action=edit&acf_ft_from_template=true" ) );
				}
				else {
					?>
					<div class="notice notice-success is-dismissible">
				        <p><?php _e( "The template you asked for does not exist.", "acf-flexible-templates" ); ?></p>
				    </div>
				    <?php
				}
			}
		}
	}

	public function admin_notice() {
		if ( isset( $_GET["acf_ft_from_template"] ) ) {
			?>
			<div class="notice notice-success is-dismissible">
		        <p><?php _e( "This page has already been created as a draft. If you decide not to use it, you have to delete it.", "acf-flexible-templates" ); ?></p>
		    </div>
		    <?php
		}
	}
}

new ACF_Flexible_Templates();