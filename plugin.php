<?php
/*
Plugin Name: Advanced Custom Fields: Flexible Layout Templates
Plugin URI: https://github.com/devgeniem/acf-flexible-templates
Description: Create ready made templates for your Flexible Content pages.
Version: 0.0.1
Author: Miika Arponen / Geniem
Author URI: https://github.com/devgeniem
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class ACF_Flexible_Templates {

	/*
	 * Hook actions and filters in place
	 */
	public function __construct() {
		add_action( "init", array( $this, "register_post_type" ) );

		add_action( "add_meta_boxes", array( $this, "page_attributes" ) );
		
		add_action( "save_post", array( $this, "save_page_attributes" ) );
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
			'description'         => 'Create layout templates for ACF Flexible Content Type',
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
		        __('Page Attributes'),
		        array( $this, 'page_attributes_callback' ), 
		        'layout-template', 
		        'side', 
		        'low'
		    );

		    add_meta_box(
		    	'acf-fl-submitdiv',
		    	__('Save'),
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
			update_post_meta( $post_id, "_wp_page_template", $_REQUEST["_wp_page_template"] );
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

	/** TODO
	*
	*	- templatesta luonti Uusi sivu -alavalikoksi
	*	- disabloi komponenttien luonti / poisto -rasti
	* 
	*/
}

new ACF_Flexible_Templates();