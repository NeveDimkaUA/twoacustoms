<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
* WC stock status for Products if product stock status are empty they get global stock status ( Setting tab Status )
*/

class Woo_Stock_Product extends Woo_Stock_Base {
	
	public function __construct() {
		
		// add stock status tab to product tab
		add_filter( 'woocommerce_product_data_tabs', array( $this , 'woo_add_simple_product_stock_status' ) );

		// display stock status fields for ( Simple,Grouped,External ) Products
		add_action( 'woocommerce_product_data_panels' , array( $this , 'woo_stock_status_fields' ) );

		// save stock fields value for ( Simple ) Product
		add_action( 'woocommerce_process_product_meta_simple' , array( $this , 'save_stock_status_message' ) );

		// add stock status message in content-product template page
		add_action( 'woocommerce_after_shop_loop_item_title' , array( $this , 'add_stack_status_in_summary' ) , 15 ); // after price woocommerce\templates\content-product.php line:60

		/**
		 * Hide save stock fields value for Grouped,External Products
		 */
		
		// add_action( 'woocommerce_process_product_meta_grouped' , array( $this , 'save_stock_status_message' ) );
		// add_action( 'woocommerce_process_product_meta_external' , array( $this , 'save_stock_status_message' ) );

		// variration stock status field
		add_action( 'woocommerce_variation_options_inventory' , array( $this , 'woo_variation_stock_status_field' ) , 10 , 3 ); 

		//save variation stock status
		add_action( 'woocommerce_save_product_variation' , array( $this , 'save_variation_stock_status' ) , 10 , 2 );
	}

	public function woo_add_simple_product_stock_status( $tabs ) {
		$tabs['stockstatus'] = array(
										'label'  => __( 'Stock Status', 'woo-custom-stock-status' ),
										'target' => 'custom_stock_status_data',
										'class'  => array( 'show_if_simple' ), // depend upon product type to show & hide
									);

		return $tabs;
	}

	public function woo_stock_status_fields() {
		echo '<div id="custom_stock_status_data" class="panel woocommerce_options_panel">';
		foreach ($this->status_array as $key => $value) {
			woocommerce_wp_text_input(
										array( 
												'id' => $key, 
												'label' => __( $value , 'woo-custom-stock-status' ),
												'placeholder' => $value 
											)
									);
		}
		echo '</div>';
	}

	public function save_stock_status_message( $post_id ) {
		foreach ($this->status_array as $meta_key => $val) {
			if(isset( $_POST[$meta_key] ) && !empty( $_POST[$meta_key] ) ) {
				update_post_meta( $post_id , $meta_key , sanitize_text_field( $_POST[$meta_key] ) );
			} else {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}

	public function woo_variation_stock_status_field( $loop, $variation_data, $variation ) {
		$right_side = array('in_stock','can_be_backordered','available_on_backorder');
		echo '<div style="clear:both"></div><p style="font-size:14px;"><b>'.__( 'Custom Stock Status' , 'woo-custom-stock-status' ).'</b></p>';
		foreach ($this->status_array as $key => $name) { ?>
			<p class="form-row <?php echo in_array( $key,$right_side ) ? 'form-row-first' : 'form-row-last' ?>">
				<label><?php _e( $name , 'woo-custom-stock-status' ); ?></label>
				<input type="text" placeholder="<?php echo $name; ?>" name="variable_<?php echo $key; ?>_status[<?php echo $loop; ?>]" value="<?php echo get_post_meta( $variation->ID , '_'.$key.'_status' , true ); ?>" />
			</p>
		<?php
		}
	}

	public function save_variation_stock_status( $post_id , $variation_key ) {
		foreach ($this->status_array as $meta_key => $val) {
			if(isset( $_POST['variable_'.$meta_key.'_status'][$variation_key] ) && !empty( $_POST['variable_'.$meta_key.'_status'][$variation_key] ) ) {
				update_post_meta( $post_id , '_'.$meta_key.'_status' , sanitize_text_field( $_POST['variable_'.$meta_key.'_status'][$variation_key] ) );
			} else {
				delete_post_meta( $post_id, '_'.$meta_key.'_status' );
			}
		}
	}
	
	/**
	 * Show stock status in product listing page
	 */
	public function add_stack_status_in_summary(){
		global $product;
		$availability      = $product->get_availability();
		$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';

		echo $availability_html;
	}
}