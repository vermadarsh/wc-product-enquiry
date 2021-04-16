<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://gurukullab.com/
 * @since      1.0.0
 *
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/admin
 * @author     Gurukullab <info@gurukullab.com>
 */
class Wc_Product_Enquiry_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wcpe_admin_enqueue_scripts_callback() {
		wp_enqueue_style(
			$this->plugin_name . '-font-awesome',
			WCPE_PLUGIN_URL . 'admin/css/font-awesome.min.css',
			array(),
			filemtime( WCPE_PLUGIN_PATH . 'admin/css/font-awesome.min.css' ),
			'all'
		);

		wp_enqueue_style(
			$this->plugin_name,
			WCPE_PLUGIN_URL . 'admin/css/wc-product-enquiry-admin.css',
			array(),
			filemtime( WCPE_PLUGIN_PATH . 'admin/css/wc-product-enquiry-admin.css' ),
			'all'
		);

		wp_enqueue_script(
			$this->plugin_name,
			WCPE_PLUGIN_URL . 'admin/js/wc-product-enquiry-admin.js',
			array( 'jquery' ),
			filemtime( WCPE_PLUGIN_PATH . 'admin/js/wc-product-enquiry-admin.js' ),
			true
		);

		wp_localize_script(
			$this->plugin_name,
			'WCPE_Admin_JS_Obj',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Actions to be taken at admin initialization.
	 */
	public function wcpe_admin_init_callback() {

		// Redirect after plugin redirect.
		if ( get_option( 'wcpe_do_activation_redirect' ) ) {
			delete_option( 'wcpe_do_activation_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=wcpe' ) );
			exit;
		}
	}

	/**
	 * Admin settings for syncing marketplace.
	 *
	 * @param array $settings Array of WC settings.
	 * @return array
	 */
	public function wcpe_woocommerce_get_settings_pages_callback( $settings ) {
		$settings[] = include __DIR__ . '/settings/class-wc-product-enquiry-settings.php';

		return $settings;
	}

	/**
	 * Enquiries listing shortcode.
	 *
	 * @param array $args Shortcode arguments.
	 * @return string
	 */
	public function wcpe_product_enquiries_callback( $args ) {
		$enquiries = wcpe_get_enquiries();
		ob_start();
		?>
		<div class="woocommerce wcpe-enquiries-container">
			<?php
			if ( empty( $enquiries ) ) {
				echo wp_kses_post( wcpe_get_no_enquiries_html() );
			} else {
				echo wcpe_get_enquiries_table_html( $enquiries ) . wcpe_get_enquiry_form_html();
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add admin menu pages.
	 */
	public function wcpe_admin_menu_callback() {
		// Menu page for listing the enquiries.
		add_menu_page(
			__( 'Product Enquiries', 'wxpe' ),
			__( 'Enquiries', 'wcpe' ),
			'manage_options',
			'enquiries',
			array(
				$this,
				'wcpe_enquiries_stats_page',
			),
			'dashicons-smiley',
			5
		);

		// Enquiries dashboard.
		add_submenu_page(
			'enquiries',
			__( 'Dashboard', 'wcpe' ),
			__( 'Dashboard', 'wcpe' ),
			'manage_options',
			'dashboard',
			array(
				$this,
				'wcpe_enquiries_stats_page',
			)
		);

		// Enquiries settings.
		add_submenu_page(
			'enquiries',
			__( 'Settings', 'wcpe' ),
			__( 'Settings', 'wcpe' ),
			'manage_options',
			admin_url( '/admin.php?page=wc-settings&tab=wcpe' ),
			null
		);

		// Remove publish metabox from enquiries CPT.
		remove_meta_box( 'submitdiv', wcpe_get_enquiry_cpt_slug(), 'side' );
	}

	/**
	 * Enquiries stats page markup.
	 */
	public function wcpe_enquiries_stats_page() {
		require_once WCPE_PLUGIN_PATH . 'admin/includes/stats.php';
	}

	/**
	 * Add custom columns to the enquiries listing.
	 *
	 * @param array $cols Array of columns.
	 * @return array
	 */
	public function wcpe_manage_enquiry_posts_columns( $cols ) {

		// Items column.
		if ( ! array_key_exists( 'items', $cols ) ) {
			$cols['items'] = __( 'Items', 'wcpe' );
		}

		// Enquirer column.
		if ( ! array_key_exists( 'enquirer', $cols ) ) {
			$cols['enquirer'] = __( 'Enquirer', 'wcpe' );
		}

		// Comment column.
		if ( ! array_key_exists( 'comment', $cols ) ) {
			$cols['comment'] = __( 'Comment', 'wcpe' );
		}

		// Unset the author column.
		unset( $cols['author'] );

		/**
		 * WP list table columns filter.
		 *
		 * This filter helps us to manage columns of the admin listing of the enquiries.
		 *
		 * @param array $cols Holds the columns array.
		 * @return array
		 */
		return apply_filters( 'wcpe_enquiry_list_cols', $cols );
	}

	/**
	 * Manage the data to the custom columns.
	 *
	 * @param string $col Holds the column slug.
	 * @param int    $post_id Holds the post ID.
	 */
	public function wcpe_manage_enquiry_posts_custom_column( $col, $post_id ) {
		switch ( $col ) {
			case 'items':
				$this->wcpe_enquiry_items( $post_id );
				break;

			case 'enquirer':
				$this->wcpe_enquiry_enquirer( $post_id );
				break;

			case 'comment':
				$this->wcpe_enquiry_comment( $post_id );
				break;
		}
	}

	/**
	 * Show the enquiry items.
	 *
	 * @param int $post_id Holds the post ID.
	 */
	private function wcpe_enquiry_items( $post_id ) {
		$enquiry_items = get_post_meta( $post_id, '_enquiries', true );

		if ( ! empty( $enquiry_items ) && is_array( $enquiry_items ) ) {
			foreach ( $enquiry_items as $item_id => $item_data ) {
				$quantity  = $item_data['quantity'];
				$attach_id = get_post_thumbnail_id( $item_id );
				$thumbnail = wcpe_get_image_src_by_id( $attach_id );
				$item_edit = get_edit_post_link( $item_id );
				echo wp_kses_post( apply_filters( 'wcpe_enquiry_item_html', '<a href="' . esc_url( $item_edit ) . '" class="wcpe-enquiry-item" title="' . $quantity . ' * ' . get_the_title( $item_id ) . '"><img src="' . esc_url( $thumbnail ) . '" alt="enquiry-item-thumbnail" /></a>' ) );
			}
		} else {
			echo esc_html( apply_filters( 'wcpe_no_enquiry_items_found_message', __( 'No enquiry items found dude !!', 'wcpe' ) ) );
		}
	}

	/**
	 * Show the enquirer details.
	 *
	 * @param int $post_id Holds the post ID.
	 */
	private function wcpe_enquiry_enquirer( $post_id ) {
		$post_meta  = get_post_meta( $post_id );
		$first_name = ( ! empty( $post_meta['_by_first_name'][0] ) ) ? $post_meta['_by_first_name'][0] : '';
		$last_name  = ( ! empty( $post_meta['_by_last_name'][0] ) ) ? $post_meta['_by_last_name'][0] : '';
		$name       = "{$first_name} {$last_name}";
		$email      = ( ! empty( $post_meta['_by_email'][0] ) ) ? $post_meta['_by_email'][0] : '';
		$phone      = ( ! empty( $post_meta['_by_phone'][0] ) ) ? $post_meta['_by_phone'][0] : '';
		echo wp_kses_post(
			"<p><i class='fa fa-user'></i>{$name}</p>" .
			"<p><i class='fa fa-envelope'></i><a href='mailto:{$email}'>{$email}</a></p>" .
			"<p><i class='fa fa-phone'></i><a href='tel:{$phone}'>{$phone}</a></p>"
		);
	}

	/**
	 * Show the enquiry comment.
	 *
	 * @param int $post_id Holds the post ID.
	 */
	private function wcpe_enquiry_comment( $post_id ) {
		$comment = get_the_excerpt( $post_id );
		echo wp_kses_post( "<p>{$comment}</p>" );
	}

	/**
	 * Add custom metaboxes to enquiry post type.
	 */
	public function wcpe_add_meta_boxes_callback() {
		add_meta_box(
			'wcpe-enquiry-enquirer',
			__( 'Enquirer', 'wcpe' ),
			array(
				$this,
				'wcpe_enquiry_enquirer_callback',
			),
			wcpe_get_enquiry_cpt_slug(),
			'normal'
		);

		add_meta_box(
			'wcpe-enquiry-items',
			__( 'Items', 'wcpe' ),
			array(
				$this,
				'wcpe_enquiry_items_callback',
			),
			wcpe_get_enquiry_cpt_slug(),
			'normal'
		);
	}

	/**
	 * Enquirer details in metabox.
	 */
	public function wcpe_enquiry_enquirer_callback() {
		$post_meta  = get_post_meta( get_the_ID() );
		$first_name = ( ! empty( $post_meta['_by_first_name'][0] ) ) ? $post_meta['_by_first_name'][0] : '';
		$last_name  = ( ! empty( $post_meta['_by_last_name'][0] ) ) ? $post_meta['_by_last_name'][0] : '';
		$name       = "{$first_name} {$last_name}";
		$email      = ( ! empty( $post_meta['_by_email'][0] ) ) ? $post_meta['_by_email'][0] : '';
		$phone      = ( ! empty( $post_meta['_by_phone'][0] ) ) ? $post_meta['_by_phone'][0] : '';
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="">
							<?php esc_html_e( 'Name', 'wcpe' ); ?>
						</label>
					</th>
					<td><?php echo esc_html( $name ); ?></td>
				</tr>
				<tr>
					<th scope="row">
						<label for="">
							<?php esc_html_e( 'Email', 'wcpe' ); ?>
						</label>
					</th>
					<td><a href="mailto:<?php echo esc_html( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
				</tr>
				<tr>
					<th scope="row">
						<label for="">
							<?php esc_html_e( 'Phone', 'wcpe' ); ?>
						</label>
					</th>
					<td><a href="tel:<?php echo esc_html( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Enquiry items details in metabox.
	 */
	public function wcpe_enquiry_items_callback() {
		$items              = get_post_meta( get_the_ID(), '_enquiries', true );
		$enquiry_table_cols = apply_filters(
			'wcpe_enquiry_items_admin_table_columns',
			array(
				'thumbnail'     => array(
					'class' => 'product-thumbnail',
					'text'  => '&nbsp;',
				),
				'item-name'     => array(
					'class' => 'product-name wcpe-product-name',
					'text'  => __( 'Product', 'wcpe' ),
				),
				'item-price'    => array(
					'class' => 'product-price',
					'text'  => __( 'Price', 'wcpe' ),
				),
				'item-quantity' => array(
					'class' => 'product-quantity',
					'text'  => __( 'Quantity', 'wcpe' ),
				),
				'item-subtotal' => array(
					'class' => 'product-subtotal',
					'text'  => __( 'Subtotal', 'wcpe' ),
				),
				'item-remarks'  => array(
					'class' => 'product-remarks',
					'text'  => __( 'Remarks', 'wcpe' ),
				),
			)
		);

		$items_total = 0.00;
		?>
		<table class="form-table">
			<thead>
				<tr>
				<?php
				if ( ! empty( $enquiry_table_cols ) && is_array( $enquiry_table_cols ) ) {
					foreach ( $enquiry_table_cols as $col ) {
						?>
						<th class="<?php echo esc_attr( $col['class'] ); ?>"><?php echo esc_html( $col['text'] ); ?></th>
						<?php
					}
				}
				?>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $items as $item_id => $item ) {
				$product = wc_get_product( $item_id );

				// Skip the item with 0 ID.
				if ( '' === $item_id ) {
					continue;
				}
				?>
				<tr data-id="<?php echo esc_attr( $item_id ); ?>">
					<?php foreach ( $enquiry_table_cols as $col_index => $col ) { ?>
					<td class="<?php echo esc_attr( $col['class'] ); ?>">
						<?php
						switch ( $col_index ) {
							case 'thumbnail':
								$attach_id = get_post_thumbnail_id( $item_id );
								$thumbnail = wcpe_get_image_src_by_id( $attach_id );
								?>
								<a href="<?php echo esc_url( get_edit_post_link( $item_id ) ); ?>">
									<img width="50px" height="50px"
										src="<?php echo esc_url( $thumbnail ); ?>"
										class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail"
										alt="">
								</a>
								<?php
								break;

							case 'item-name':
								?>
								<a href="<?php echo esc_url( get_edit_post_link( $item_id ) ); ?>"><?php echo esc_html( get_the_title( $item_id ) ); ?></a>
								<?php
								break;

							case 'item-price':
								echo wp_kses_post( $product->get_price_html() );
								break;

							case 'item-quantity':
								?>
								<div class="quantity"><?php echo esc_attr( $item['quantity'] ); ?></div>
								<?php
								break;

							case 'item-subtotal':
								$price        = (float) $product->get_price();
								$subtotal     = $price * $item['quantity'];
								$items_total += $subtotal;
								echo wp_kses_post( wc_price( $subtotal ) );
								break;

							case 'item-remarks':
								echo ( ! empty( $item['remarks'] ) ) ? esc_html( $item['remarks'] ) : '-';
								break;

							default:
								echo wp_kses_post( apply_filters( "wcpe_enquiry_item_admin_{$col_index}_data", '', $item_id, $item ) );
								break;
						}
						?>
					</td>
				<?php } ?>
				</tr>
			<?php } ?>
			</tbody>
			<tfoot class="wcpe-items-total-footer">
				<tr>
					<?php /* translators: %s: items total html */ ?>
					<td colspan="6"><?php echo wp_kses_post( sprintf( __( 'Total: %1$s', 'wcpe' ), wc_price( $items_total ) ) ); ?></td>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * Manage enquiries row actions.
	 *
	 * @param array  $actions Holds the array of actionable items.
	 * @param object $post Holds the post object.
	 * @return array
	 */
	public function wcpe_post_row_actions_callback( $actions, $post ) {

		if ( wcpe_get_enquiry_cpt_slug() !== $post->post_type ) {
			return $actions;
		}

		unset( $actions['view'] );
		unset( $actions['inline hide-if-no-js'] );

		return $actions;
	}
}
