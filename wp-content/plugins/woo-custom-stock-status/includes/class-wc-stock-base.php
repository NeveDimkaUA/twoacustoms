<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
* Base Class For Common var & functions
*/
class Woo_Stock_Base {
		
	public function __construct() {
		//change stock status based on product
		add_filter( 'woocommerce_get_availability', array( $this,'woo_rename_stock_status' ) , 10 , 2);
	}

	/**
	 * Default stock status and its names (array format key as meta_key value as Label)
	 */
	public $status_array = array(
							'in_stock' 				=> 'In stock',
							'only_s_left_in_stock' 	=> 'Only %s left in stock',
							'can_be_backordered' 	=> '(can be backordered)',
							's_in_stock'			=> '%s in stock',
							'available_on_backorder'=> 'Available on backorder',
							'out_of_stock' 			=> 'Out of stock',
						);

	/**
	 * Default stock status colors
	 */
	public $status_color_array = array(
							'in_stock_color' 				=> array('default'=> '#77a464', 'label' => 'In stock'),
							'only_s_left_in_stock_color' 	=> array('default'=> '#77a464', 'label' => 'Only %s left in stock'),
							's_in_stock_color'				=> array('default'=> '#77a464', 'label' => '%s in stock'),
							'available_on_backorder_color'	=> array('default'=> '#77a464', 'label' => 'Available on backorder'),
							'can_be_backordered_color' 		=> array('default'=> '#77a464', 'label' => '(can be backordered)'),
							'out_of_stock_color' 			=> array('default'=> '#ff0000', 'label' => 'Out of stock'),
						);
	

	/*
	 * Rename the default stock list names based on Product
	 */
	public function woo_rename_stock_status( $message_array ,  $this_obj ) {
	
		$availability = $class = '';

		foreach($this->status_array as $status=>$label){
			$$status = $label;
		}
		/**
		 * Change Stock Status Based on Product wise. now, we used simple & variation product types only 
		 * if product doesn't have stock status we use global stock status (Settings->Custom Stock) tab
		 */
		foreach( $this->status_array as $status=>$label ){
			if( $this_obj->is_type( 'simple' ) ) {
				$stock_status = get_post_meta( $this_obj->id , $status , true );
				if( !empty( $stock_status ) ) {
					$$status = get_post_meta( $this_obj->id , $status , true );
				} else {
					$$status = (get_option('wc_slr_'.$status,$$status)=='') ? $$status : get_option('wc_slr_'.$status,$$status);
				}
			} elseif ( $this_obj->is_type( 'variation' ) ) {
				$stock_status = get_post_meta( $this_obj->variation_id , '_'.$status.'_status' , true );
				if( !empty( $stock_status ) ) {
					$$status = get_post_meta( $this_obj->variation_id , '_'.$status.'_status' , true );
				} else {
					$$status = (get_option('wc_slr_'.$status,$$status)=='') ? $$status : get_option('wc_slr_'.$status,$$status);
				}	
			}
			else {
				$$status = (get_option('wc_slr_'.$status,$$status)=='') ? $$status : get_option('wc_slr_'.$status,$$status);
			}
		}
		
		if ( $this_obj->managing_stock() ) {

			if ( $this_obj->is_in_stock() && $this_obj->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) ) {

				switch ( get_option( 'woocommerce_stock_format' ) ) {

					case 'no_amount' :
						$availability = __( $in_stock, 'woocommerce' );
						$extra_class  = 'in_stock_color';
					break;

					case 'low_amount' :
						if ( $this_obj->get_total_stock() <= get_option( 'woocommerce_notify_low_stock_amount' ) ) {
							$availability = sprintf( __( $only_s_left_in_stock, 'woocommerce' ), $this_obj->get_total_stock() );

							$extra_class  = 'only_s_left_in_stock_color';

							if ( $this_obj->backorders_allowed() && $this_obj->backorders_require_notification() ) {
								$availability .= ' ' . __( $can_be_backordered, 'woocommerce' );
								$extra_class  .= ' can_be_backordered_color';
							}
						} else {
							$availability = __( $in_stock, 'woocommerce' );
							$extra_class  = 'in_stock_color';
						}
					break;

					default :
						$availability = sprintf( __( $s_in_stock, 'woocommerce' ), $this_obj->get_total_stock() );

						$extra_class  = 's_in_stock_color';

						if ( $this_obj->backorders_allowed() && $this_obj->backorders_require_notification() ) {
							$availability .= ' ' . __( $can_be_backordered, 'woocommerce' );
							$extra_class  .= ' can_be_backordered_color';
						}
					break;
				}

				$class        = 'in-stock ';

				$class		 .= $extra_class;

			} elseif ( $this_obj->backorders_allowed() && $this_obj->backorders_require_notification() ) {

				$availability = __( $available_on_backorder, 'woocommerce' );
				$class        = 'available-on-backorder available_on_backorder_color';

			} elseif ( $this_obj->backorders_allowed() ) {

				$availability = __( $in_stock, 'woocommerce' );
				$class        = 'in-stock in_stock_color';

			} else {

				$availability = __( $out_of_stock, 'woocommerce' );
				$class        = 'out-of-stock out_of_stock_color';
			}

		} elseif ( ! $this_obj->is_in_stock() ) {

			$availability = __( $out_of_stock, 'woocommerce' );
			$class        = 'out-of-stock out_of_stock_color';
		}
		
		$message_array['availability'] = $availability;
		$message_array['class'] = $class;
		return $message_array;
	}
}

?>