<?php

if ( ! class_exists( 'WC_Connect_Shipping_Label' ) ) {

	class WC_Connect_Shipping_Label {

		/**
		 * @var WC_Connect_API_Client
		 */
		protected $api_client;

		/**
		 * @var WC_Connect_Service_Settings_Store
		 */
		protected $settings_store;

		/**
		 * @var WC_Connect_Service_Schemas_Store
		 */
		protected $service_schemas_store;

		/**
		 * @var WC_Connect_Payment_Methods_Store
		 */
		protected $payment_methods_store;

		/**
		 * @var array array of currently unsupported US states
		 */
		private $unsupported_states = array( 'AA', 'AE', 'AP' );

		public function __construct(
			WC_Connect_API_Client $api_client,
			WC_Connect_Service_Settings_Store $settings_store,
			WC_Connect_Service_Schemas_Store $service_schemas_store,
			WC_Connect_Payment_Methods_Store $payment_methods_store
		) {
			$this->api_client = $api_client;
			$this->settings_store = $settings_store;
			$this->service_schemas_store = $service_schemas_store;
			$this->payment_methods_store = $payment_methods_store;
		}

		public function get_items_as_individual_packages( WC_Order $order ) {
			$packages   = array();
			$item_count = 0;

			foreach( $order->get_items() as $item ) {
				$product = WC_Connect_Compatibility::instance()->get_item_product( $order, $item );
				if ( ! $product || ! $product->needs_shipping() ) {
					continue;
				}
				$height = 0;
				$length = 0;
				$weight = $product->get_weight();
				$width = 0;

				if ( $product->has_dimensions() ) {
					$height = $product->get_height();
					$length = $product->get_length();
					$width  = $product->get_width();
				}

				for ( $i = 0; $i < $item[ 'qty' ]; $i++ ) {
					$id = 'weight_' . $item_count++ . '_individual';
					$product_data = array(
						'height'     => ( float ) $height,
						'product_id' => $item[ 'product_id' ],
						'length'     => ( float ) $length,
						'quantity'   => 1,
						'weight'     => ( float ) $weight,
						'width'      => ( float ) $width,
						'name'       => $this->get_name( $product ),
						'url'        => get_edit_post_link( WC_Connect_Compatibility::instance()->get_parent_product_id( $product ), null ),
					);

					if ( $product->is_type( 'variation' ) ) {
						$product_data[ 'attributes' ] = WC_Connect_Compatibility::instance()->get_formatted_variation( $product, true );
					}

					$packages[ $id ] = array(
						'id'     => $id,
						'box_id' => 'individual',
						'height' => ( float ) $height,
						'length' => ( float ) $length,
						'weight' => ( float ) $weight,
						'width'  => ( float ) $width,
						'items'  => array( $product_data ),
					);
				}
			}

			return $packages;
		}

		protected function get_packaging_from_shipping_method( $shipping_method ) {
			if ( ! $shipping_method || ! isset( $shipping_method[ 'wc_connect_packages' ] ) ) {
				return array();
			}

			$packages_data = $shipping_method[ 'wc_connect_packages' ];
			if ( ! $packages_data ) {
				return array();
			}

			// WC3 retrieves metadata as non-scalar values
			if ( is_array( $packages_data ) ) {
				return $packages_data;
			}

			// WC2.6 stores non-scalar values as string, but doesn't deserialize it on retrieval
			$packages = maybe_unserialize( $packages_data );
			if ( is_array( $packages ) ) {
				return $packages;
			}

			// legacy WCS stored the labels as JSON
			$packages = json_decode( $packages_data, true );
			if ( $packages ) {
				return $packages;
			}

			$packages_data = $this->settings_store->try_recover_invalid_json_string( 'box_id', $packages_data );
			$packages = json_decode( $packages_data, true );
			if ( $packages ) {
				return $packages;
			}

			return array();
		}

		protected function get_packaging_metadata( WC_Order $order ) {
			$shipping_methods = $order->get_shipping_methods();
			$shipping_method = reset( $shipping_methods );
			return $this->get_packaging_from_shipping_method( $shipping_method );
		}

		protected function get_name( WC_Product $product ) {
			if ( $product->get_sku() ) {
				$identifier = $product->get_sku();
			} else {
				$identifier = '#' . WC_Connect_Compatibility::instance()->get_product_id( $product );

			}
			return sprintf( '%s - %s', $identifier, $product->get_title() );
		}

		public function get_selected_packages( WC_Order $order ) {
			$packages = $this->get_packaging_metadata( $order );
			if ( ! $packages ) {
				return $this->get_items_as_individual_packages( $order );
			}

			$formatted_packages = array();

			foreach( $packages as $package_obj ) {
				$package = ( array ) $package_obj;
				$package_id = $package[ 'id' ];
				$formatted_packages[ $package_id ] = $package;

				foreach( $package[ 'items' ] as $item_index => $item ) {
					$product_data = ( array ) $item;
					$product = WC_Connect_Compatibility::instance()->get_item_product( $order, $product_data );

					if ( $product ) {
						$product_data[ 'name' ] = $this->get_name( $product );
						$product_data[ 'url' ] = get_edit_post_link( WC_Connect_Compatibility::instance()->get_parent_product_id( $product ), null );
						if ( $product->is_type( 'variation' ) ) {
							$formatted = WC_Connect_Compatibility::instance()->get_formatted_variation( $product, true );
							$product_data[ 'attributes' ] = $formatted;
						}
					} else {
						$product_data[ 'name' ] = WC_Connect_Compatibility::instance()->get_product_name_from_order( $item[ 'product_id' ], $order );
					}

					$formatted_packages[ $package_id ][ 'items' ][ $item_index ] = $product_data;
				}
			}

			return $formatted_packages;
		}

		protected function get_all_packages() {
			$custom_packages = $this->settings_store->get_packages();

			$formatted_packages = array();

			foreach( $custom_packages as $package ) {
				$package_id = $package[ 'name' ];
				$formatted_packages[ $package_id ] = $package;
			}

			$predefined_packages_schema = $this->service_schemas_store->get_predefined_packages_schema();
			$enabled_predefined_packages = $this->settings_store->get_predefined_packages();

			foreach ( $predefined_packages_schema as $service_id => $service_predefined_packages_schema ) {
				$service_enabled_predefined_packages = isset( $enabled_predefined_packages[ $service_id ] ) ? $enabled_predefined_packages[ $service_id ] : array();
				foreach ( $service_predefined_packages_schema as $group ) {
					foreach ( $group->definitions as $package ) {
						if ( ! $package->is_flat_rate && ! in_array( $package->id, $service_enabled_predefined_packages ) ) {
							continue;
						}

						$formatted_packages[ $package->id ] = $package;
					}
				}
			}

			return ( object ) $formatted_packages;
		}

		protected function get_flat_rate_packages_groups() {
			$predefined_packages_schema = $this->service_schemas_store->get_predefined_packages_schema();
			$groups = array();

			foreach ( $predefined_packages_schema as $service_id => $service_predefined_packages_schema ) {
				foreach ( $service_predefined_packages_schema as $group_id => $group ) {
					$groups[ $group_id ] = $group->title;
				}
			}

			return $groups;
		}

		public function get_selected_rates( WC_Order $order ) {
			$shipping_methods = $order->get_shipping_methods();
			$shipping_method = reset( $shipping_methods );
			$packages = $this->get_packaging_from_shipping_method( $shipping_method );
			$rates = array();

			foreach( $packages as $idx => $package_obj ) {
				$package = ( array ) $package_obj;
				// Abort if the package data is malformed
				if ( ! isset( $package[ 'id' ] ) || ! isset( $package[ 'service_id' ] ) ) {
					return array();
				}

				$rates[ $package[ 'id' ] ] = $package[ 'service_id' ];
			}

			return $rates;
		}

		protected function format_address_for_api( $address ) {
			// Combine first and last name
			if ( ! isset( $address[ 'name' ] ) ) {
				$first_name = isset( $address[ 'first_name' ] ) ? trim( $address[ 'first_name' ] ) : '';
				$last_name  = isset( $address[ 'last_name' ] ) ? trim( $address[ 'last_name' ] ) : '';

				$address[ 'name' ] = $first_name . ' ' . $last_name;
			}

			// Rename address_1 to address
			if ( ! isset( $address[ 'address' ] ) && isset( $address[ 'address_1' ] ) ) {
				$address[ 'address' ] = $address[ 'address_1' ];
			}

			// Remove now defunct keys
			unset( $address[ 'first_name' ], $address[ 'last_name' ], $address[ 'address_1' ] );

			return $address;
		}

		protected function get_origin_address() {
			$origin = $this->format_address_for_api( $this->settings_store->get_origin_address() );

			return $origin;
		}

		protected function get_destination_address( WC_Order $order ) {
			$order_address = $order->get_address( 'shipping' );
			$destination   = $this->format_address_for_api( $order_address );

			return $destination;
		}

		protected function get_form_data( WC_Order $order ) {
			$order_id               = WC_Connect_Compatibility::instance()->get_order_id( $order );
			$selected_packages      = $this->get_selected_packages( $order );
			$all_packages           = $this->get_all_packages();
			$flat_rate_groups       = $this->get_flat_rate_packages_groups();
			$is_packed              = ( false !== $this->get_packaging_metadata( $order ) );
			$origin                 = $this->get_origin_address();
			$selected_rates         = $this->get_selected_rates( $order );
			$destination            = $this->get_destination_address( $order );

			if ( ! $destination[ 'country' ] ) {
				$destination[ 'country' ] = $origin[ 'country' ];
			}

			$destination_normalized = ( bool ) get_post_meta( $order_id, '_wc_connect_destination_normalized', true );

			$form_data = compact( 'is_packed', 'selected_packages', 'all_packages', 'flat_rate_groups', 'origin', 'destination', 'destination_normalized' );

			$form_data[ 'rates' ] = array(
				'selected'  => (object) $selected_rates,
			);

			$form_data[ 'order_id' ] = $order_id;

			return $form_data;
		}

		protected function get_states_map() {
			$result = array();
			foreach( WC()->countries->get_countries() as $code => $name ) {
				$result[ $code ] = array( 'name' => html_entity_decode( $name ) );
			}
			foreach( WC()->countries->get_states() as $country => $states ) {
				$result[ $country ][ 'states' ] = array();
				foreach ( $states as $code => $name ) {
					if ( 'US' === $country && in_array( $code, $this->unsupported_states ) ) {
						continue;
					}

					$result[ $country ][ 'states' ][ $code ] = html_entity_decode( $name );
				}
			}
			return $result;
		}

		public function should_show_meta_box() {
			$order = wc_get_order();

			if ( ! $order ) {
				return false;
			}

			// If the order already has purchased labels, show the meta-box no matter what
			if ( get_post_meta( WC_Connect_Compatibility::instance()->get_order_id( $order ), 'wc_connect_labels', true ) ) {
				return true;
			}

			// Restrict showing the meta-box to supported origin and destinations: US domestic, for now
			$base_location = wc_get_base_location();
			if ( 'US' !== $base_location[ 'country' ] ) {
				return false;
			}

			$dest_address = $order->get_address( 'shipping' );
			if ( ( $dest_address[ 'country' ] && 'US' !== $dest_address[ 'country' ] )
				|| in_array( $dest_address[ 'state' ], $this->unsupported_states ) ) {
				return false;
			}

			// If the order was created using WCS checkout rates, show the meta-box regardless of the products' state
			if ( $this->get_packaging_metadata( $order ) ) {
				return true;
			}

			// At this point (no packaging data), only show if there's at least one existing and shippable product
			foreach( $order->get_items() as $item ) {
				$product = WC_Connect_Compatibility::instance()->get_item_product( $order, $item );
				if ( $product && $product->needs_shipping() ) {
					return true;
				}
			}

			return false;
		}

		public function get_selected_payment_method() {
			// Account settings contains the payment method id
			$account_settings = $this->settings_store->get_account_settings();

			// No selected payment method case
			if ( ! isset( $account_settings[ 'selected_payment_method_id' ] ) ) {
				return null;
			}

			$selected_payment_method_id = $account_settings[ 'selected_payment_method_id' ];

			// Get all known payment methods
			$payment_methods = $this->payment_methods_store->get_payment_methods();

			// Find the selected payment method and return the card digits (e.g. "4242")
			foreach ( (array) $payment_methods as $payment_method ) {
				if ( ! property_exists( $payment_method, 'payment_method_id' ) ) {
					continue;
				}

				if ( $selected_payment_method_id != $payment_method->payment_method_id ) {
					continue;
				}

				return property_exists( $payment_method, 'card_digits' ) ? $payment_method->card_digits : null;
			}

			return null;
		}

		public function meta_box( $post ) {
			$order = wc_get_order( $post );

			$order_id = WC_Connect_Compatibility::instance()->get_order_id( $order );
			$payload = array(
				'purchaseURL'             => get_rest_url( null, '/wc/v1/connect/label/' . $order_id ),
				'addressNormalizationURL' => get_rest_url( null, '/wc/v1/connect/normalize-address' ),
				'getRatesURL'             => get_rest_url( null, '/wc/v1/connect/label/' . $order_id . '/rates' ),
				'labelStatusURL'          => get_rest_url( null, '/wc/v1/connect/label/' . $order_id . '/%d' ),
				'labelRefundURL'          => get_rest_url( null, '/wc/v1/connect/label/' . $order_id . '/%d/refund' ),
				'labelsPrintURL'          => get_rest_url( null, '/wc/v1/connect/label/print' ),
				'paperSize'               => $this->settings_store->get_preferred_paper_size(),
				'nonce'                   => wp_create_nonce( 'wp_rest' ),
				'formData'                => $this->get_form_data( $order ),
				'paymentMethod'           => $this->get_selected_payment_method(),
				'labelsData'              => $this->settings_store->get_label_order_meta_data( $order_id ),
			);

			$store_options = $this->settings_store->get_store_options();
			$store_options[ 'countriesData' ] = $this->get_states_map();
			$payload[ 'storeOptions' ] = $store_options;

			do_action( 'enqueue_wc_connect_script', 'wc-connect-create-shipping-label', $payload );
		}

	}
}
