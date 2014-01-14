<?php
if(!class_exists('Production'))
{
	/**
   * A Production class that provides 3 additional meta fields
   */
	class Production
	{
		const POST_TYPE          = "production";
		const POST_TYPE_PLURAL   = "productions";
		protected $_meta         = array();
		protected $_taxonomies   = array();

		/**
     * The Constructor
     */
		public function __construct()
		{
			// register actions
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));

			register_activation_hook( __FILE__ , array(&$this, 'activation'));
		} // END public function __construct()

		/**
     * hook into WP's init action hook
     */
		public function init()
		{
			// Initialize Post Type
			$this->create_post_type();
			$this->create_taxonomies();
			add_action('save_post', array(&$this, 'save_post'));
		} // END public function init()

		/**
     * Create the post type
     */
		public function create_post_type()
		{
			$labels = array(
				'name'                => __(ucwords(str_replace("_", " ", self::POST_TYPE_PLURAL))),
				'singular_name'       => __(ucwords(str_replace("_", " ", self::POST_TYPE)))
				'menu_name'           => __(ucwords(str_replace("_", " ", self::POST_TYPE_PLURAL))),
				'parent_item_colon'   => __( '' ),
				'all_items'           => __(sprintf('All %s', ucwords(str_replace("_", " ", self::POST_TYPE_PLURAL)))),
				'view_item'           => __(sprintf('View %s', ucwords(str_replace("_", " ", self::POST_TYPE)))),
				'add_new_item'        => __(sprintf('Add New %s', ucwords(str_replace("_", " ", self::POST_TYPE)))),
				'add_new'             => __( 'Add New' ),
				'edit_item'           => __(sprintf('Edit %s', ucwords(str_replace("_", " ", self::POST_TYPE)))),
				'update_item'         => __(sprintf('Update %s', ucwords(str_replace("_", " ", self::POST_TYPE)))),
				'search_items'        => __(sprintf('Search %s', ucwords(str_replace("_", " ", self::POST_TYPE_PLURAL)))),
				'not_found'           => __(sprintf('No %s found', str_replace("_", " ", self::POST_TYPE_PLURAL))),
				'not_found_in_trash'  => __(sprintf('No %s found in Trash', str_replace("_", " ", self::POST_TYPE_PLURAL)))
			);

			$rewrite = array(
				'slug'                => self::POST_TYPE_PLURAL,
				'with_front'          => true,
				'pages'               => true,
				'feeds'               => true,
			);

			$args = array(
				'description'         => __(sprintf('%s information pages', ucwords(str_replace("_", " ", self::POST_TYPE)))),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
				'public'              => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-exerpt-view',
				'can_export'          => true,
				'has_archive'         => true,
				'taxonomies'          => $this->_taxonomies,
				'public'              => true,
				'rewrite'             => $rewrite,
				'capability_type'     => 'page',
			);

			register_post_type( self::POST_TYPE, $args );
		}

		/**
     * Create taxonomies
     */
		public function create_taxonomies() {
		} // END public function create_taxonomies()

		/**
     * Save the metaboxes for this custom post type
     */
		public function save_post($post_id)
		{
			// verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			{
				return;
			}

			if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
			{
				foreach($this->_meta as $field_name)
				{
					// Update the post's meta field
					update_post_meta($post_id, $field_name, $_POST[$field_name]);
				}
			}
			else
			{
				return;
			} // if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
		} // END public function save_post($post_id)

		/**
     * hook into WP's admin_init action hook
     */
		public function admin_init()
		{
			// Add metaboxes
			add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
		} // END public function admin_init()

		/**
     * hook into WP's add_meta_boxes action hook
     */
		public function add_meta_boxes()
		{
			// Add this metabox to every selected post
			add_meta_box(
				sprintf('wp_heartsandeyes_plugin_%s_section', self::POST_TYPE),
				sprintf('%s Information', ucwords(str_replace("_", " ", self::POST_TYPE))),
				array(&$this, 'add_inner_meta_boxes'),
				self::POST_TYPE
			);
		} // END public function add_meta_boxes()

		/**
     * called off of the add meta box
     */
		public function add_inner_meta_boxes($post)
		{
			// Render the job order metabox
			include(sprintf("%s/../templates/%s_metabox.php", dirname(__FILE__), self::POST_TYPE));
		} // END public function add_inner_meta_boxes($post)

		/**
     * hook into WP's activation registration hook
     */
		function activation() {
			// First, we "add" the custom post type via the above written function.
			// Note: "add" is written with quotes, as CPTs don't get added to the DB,
			// They are only referenced in the post_type column with a post entry,
			// when you add a post of this CPT.
			$this->init();

			// ATTENTION: This is *only* done during plugin activation hook in this example!
			// You should *NEVER EVER* do this on every page load!!
			flush_rewrite_rules();
		}
	} // END class Production
} // END if(!class_exists('Production'))
