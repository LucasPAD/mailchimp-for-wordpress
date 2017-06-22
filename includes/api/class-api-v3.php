<?php

/**
 * Class MC4WP_API_v3
 */
class MC4WP_API_v3 {

	/**
	 * @var MC4WP_API_v3_Client
	 */
	protected $client;

	/**
	 * @var bool Are we able to talk to the MailChimp API?
	 */
	protected $connected;
	
	/**
	 * Constructor
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key ) {
		$this->client = new MC4WP_API_v3_Client( $api_key );
	}

    /**
     * Gets the API client to perform raw API calls.
     *
     * @return MC4WP_API_v3_Client
     */
	public function get_client() {
        return $this->client;
    }

	/**
	 * Pings the MailChimp API to see if we're connected
	 *
	 * The result is cached to ensure a maximum of 1 API call per page load
	 *
	 * @return boolean
	 */
	public function is_connected() {

		if( is_null( $this->connected ) ) {
			$data = $this->client->get( '/' );
			$this->connected = is_object( $data ) && isset( $data->account_id );
		}

		return $this->connected;
	}

	/**
	 * @param $email_address
	 *
	 * @return string
	 */
	public function get_subscriber_hash( $email_address ) {
		return md5( strtolower( trim( $email_address ) ) );
	}

	/**
	 * Get recent daily, aggregated activity stats for a list.
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/activity/#read-get_lists_list_id_activity
	 *
	 * @param string $list_id
	 * @param array $args
     *
	 * @return array
	 */
	public function get_list_activity( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/activity', $list_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->activity ) ) {
			return $data->activity;
		}

		return array();
	}

	/**
	 * Gets the interest categories for a given List
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/#read-get_lists_list_id_interest_categories
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_interest_categories( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/interest-categories', $list_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->categories ) ) {
			return $data->categories;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/interests/#read-get_lists_list_id_interest_categories_interest_category_id_interests
	 *
	 * @param string $list_id
	 * @param string $interest_category_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_interest_category_interests( $list_id, $interest_category_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/interest-categories/%s/interests', $list_id, $interest_category_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->interests ) ) {
			return $data->interests;
		}

		return array();
	}

	/**
	 * Get merge vars for a given list
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#read-get_lists_list_id_merge_fields
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_merge_fields( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/merge-fields', $list_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->merge_fields ) ) {
			return $data->merge_fields;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_list( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s', $list_id );
		$data = $this->client->get( $resource, $args );
		return $data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_lists( $args = array() ) {
		$resource = '/lists';
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->lists ) ) {
			return $data->lists;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 *
	 * @param string $list_id
	 * @param string $email_address
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_list_member( $list_id, $email_address, array $args = array() ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
		$data = $this->client->get( $resource, $args );
		return $data;
	}

    /**
     * Batch subscribe / unsubscribe list members.
     *
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#create-post_lists_list_id
     *
     * @param string $list_id
     * @param array $args
     * @return object
     */
	public function add_list_members( $list_id, array $args ) {
	    $resource = sprintf( '/lists/%s', $list_id );
        return $this->client->post( $resource, $args );
    }

	/**
	 * Add or update (!) a member to a MailChimp list.
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_list_member( $list_id, array $args ) {
		$subscriber_hash = $this->get_subscriber_hash( $args['email_address'] );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );

		// make sure we're sending an object as the MailChimp schema requires this
		if( isset( $args['merge_fields'] ) ) {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if( isset( $args['interests'] ) ) {
			$args['interests'] = (object) $args['interests'];
		}

		// "put" updates the member if it's already on the list... take notice
		$data = $this->client->put( $resource, $args );
		return $data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
	 *
	 * @param $list_id
	 * @param $email_address
	 * @param array $args
	 *
	 * @return object
	 */
	public function update_list_member( $list_id, $email_address, array $args ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );

		// make sure we're sending an object as the MailChimp schema requires this
		if( isset( $args['merge_fields'] ) ) {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if( isset( $args['interests'] ) ) {
			$args['interests'] = (object) $args['interests'];
		}

		$data = $this->client->patch( $resource, $args );
		return $data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return bool
	 */
	public function delete_list_member( $list_id, $email_address ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
		$data = $this->client->delete( $resource );
		return !!$data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#read-get_ecommerce_stores_store_id
	 	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_ecommerce_store( $store_id, array $args = array() ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#create-post_ecommerce_stores
	 *
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_ecommerce_store( array $args ) {
		$resource = '/ecommerce/stores';
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#edit-patch_ecommerce_stores_store_id
     *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function update_ecommerce_store( $store_id, array $args ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#delete-delete_ecommerce_stores_store_id
     *
	 * @param string $store_id
	 *
	 * @return boolean
	 */
	public function delete_ecommerce_store( $store_id ) {
		$resource = sprintf( '/ecommerce/stores/%s', $store_id );
		return !!$this->client->delete( $resource );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/customers/#read-get_ecommerce_stores_store_id_customers
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_customers( $store_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/customers', $store_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/customers/#read-get_ecommerce_stores_store_id_customers_customer_id
     *
     * @param string $store_id
     * @param string $customer_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_customer( $store_id, $customer_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $customer_id );
        return $this->client->get( $resource, $args );
    }

	/**
     * Add OR update a store customer
     *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/customers/#edit-put_ecommerce_stores_store_id_customers_customer_id
	 *
	 * @param $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_ecommerce_store_customer( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $args['id'] );
		return $this->client->put( $resource, $args );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/customers/#edit-patch_ecommerce_stores_store_id_customers_customer_id
     *
     * @param string $store_id
     * @param string $customer_id
     * @param array $args
     *
     * @return object
     */
    public function update_ecommerce_store_customer( $store_id, $customer_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $customer_id );
        return $this->client->patch( $resource, $args );
    }

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/customers/#delete-delete_ecommerce_stores_store_id_customers_customer_id
	 *
	 * @param string $store_id
	 * @param string $customer_id
	 *
	 * @return bool
	 */
	public function delete_ecommerce_store_customer( $store_id, $customer_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $customer_id );
		return !!$this->client->delete( $resource );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/#read-get_ecommerce_stores_store_id_products
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_products( $store_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/products', $store_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/#read-get_ecommerce_stores_store_id_products_product_id
     *
     * @param string $store_id
     * @param string $product_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_product( $store_id, $product_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/products/%s', $store_id, $product_id );
        return $this->client->get( $resource, $args );
    }

	/**
	 * Add a product to a store
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/#create-post_ecommerce_stores_store_id_products
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_ecommerce_store_product( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/products', $store_id );
		return $this->client->post( $resource, $args );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/#edit-patch_ecommerce_stores_store_id_products_product_id
     *
     * @param string $store_id
     * @param string $product_id
     * @param array $args
     *
     * @return object
     */
    public function update_ecommerce_store_product( $store_id, $product_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/products/%s', $store_id, $product_id );
        return $this->client->patch( $resource, $args );
    }

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/#delete-delete_ecommerce_stores_store_id_products_product_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 *
	 * @return boolean
	 */
	public function delete_ecommerce_store_product( $store_id, $product_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s', $store_id, $product_id );
		return !!$this->client->delete( $resource );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/variants/#read-get_ecommerce_stores_store_id_products_product_id_variants
     *
     * @param string $store_id
     * @param string $product_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_product_variants( $store_id, $product_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/products/%s/variants', $store_id, $product_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/variants/#read-get_ecommerce_stores_store_id_products_product_id_variants_variant_id
     *
     * @param string $store_id
     * @param string $product_id
     * @param string $variant_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_product_variant( $store_id, $product_id, $variant_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id );
        return $this->client->get( $resource, $args );
    }

	/**
     * Add OR update a product variant.
     *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/variants/#edit-put_ecommerce_stores_store_id_products_product_id_variants_variant_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_ecommerce_store_product_variant( $store_id, $product_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $args['id'] );
		return $this->client->put( $resource, $args );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/variants/#edit-patch_ecommerce_stores_store_id_products_product_id_variants_variant_id
     *
     * @param string $store_id
     * @param string $product_id
     * @param string $variant_id
     * @param array $args
     *
     * @return object
     */
    public function update_ecommerce_store_product_variant( $store_id, $product_id, $variant_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id );
        return $this->client->patch( $resource, $args );
    }

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/products/variants/#delete-delete_ecommerce_stores_store_id_products_product_id_variants_variant_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param string $variant_id
	 *
	 * @return boolean
	 */
	public function delete_ecommerce_store_product_variant( $store_id, $product_id, $variant_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id );
		return !!$this->client->delete( $resource );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#read-get_ecommerce_stores_store_id_orders
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_orders( $store_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders', $store_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#read-get_ecommerce_stores_store_id_orders_order_id
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_order( $store_id, $order_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id );
        return $this->client->get( $resource, $args );
    }

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#create-post_ecommerce_stores_store_id_orders
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_ecommerce_store_order( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders', $store_id );
		return $this->client->post( $resource, $args );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#edit-patch_ecommerce_stores_store_id_orders_order_id
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     */
    public function update_ecommerce_store_order( $store_id, $order_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id );
        return $this->client->patch( $resource, $args );
    }

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#delete-delete_ecommerce_stores_store_id_orders_order_id
	 *
	 * @param string $store_id
	 * @param string $order_id
	 *
	 * @return bool
	 */
	public function delete_ecommerce_store_order( $store_id, $order_id ) {
		return !! $this->client->delete( sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id ) );
	}

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/lines/#create-post_ecommerce_stores_store_id_orders_order_id_lines
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     */
    public function add_ecommerce_store_order_line( $store_id, $order_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines', $store_id, $order_id );
        return $this->client->post( $resource, $args );
    }


    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/lines/#read-get_ecommerce_stores_store_id_orders_order_id_lines
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_order_lines( $store_id, $order_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines', $store_id, $order_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/lines/#read-get_ecommerce_stores_store_id_orders_order_id_lines_line_id
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_order_line( $store_id, $order_id, $line_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/lines/#edit-patch_ecommerce_stores_store_id_orders_order_id_lines_line_id
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     */
    public function update_ecommerce_store_order_line( $store_id, $order_id, $line_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id );
        return $this->client->patch( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/lines/#delete-delete_ecommerce_stores_store_id_orders_order_id_lines_line_id
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     *
     * @return bool
     */
    public function delete_ecommerce_store_order_line( $store_id, $order_id, $line_id ) {
        $resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id );
        return !! $this->client->delete( $resource );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#read-get_ecommerce_stores_store_id_carts
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_carts( $store_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts', $store_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#read-get_ecommerce_stores_store_id_carts_cart_id
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_cart( $store_id, $cart_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts/%s', $store_id, $cart_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#create-post_ecommerce_stores_store_id_carts
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     */
    public function add_ecommerce_store_cart( $store_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts', $store_id );
        return $this->client->post( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#edit-patch_ecommerce_stores_store_id_carts_cart_id
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     */
    public function update_ecommerce_store_cart( $store_id, $cart_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts/%s', $store_id, $cart_id );
        return $this->client->patch( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/#delete-delete_ecommerce_stores_store_id_carts_cart_id
     *
     * @param string $store_id
     * @param string $cart_id
     *
     * @return bool
     */
    public function delete_ecommerce_store_cart( $store_id, $cart_id ) {
        return !! $this->client->delete( sprintf( '/ecommerce/stores/%s/carts/%s', $store_id, $cart_id ) );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/lines/#read-get_ecommerce_stores_store_id_carts_cart_id_lines
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_cart_lines( $store_id, $cart_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts/%/lines', $store_id, $cart_id);
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/lines/#read-get_ecommerce_stores_store_id_carts_cart_id_lines_line_id
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     */
    public function get_ecommerce_store_cart_line( $store_id, $cart_id, $line_id, array $args = array() ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id );
        return $this->client->get( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/lines/#create-post_ecommerce_stores_store_id_carts_cart_id_lines
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     */
    public function add_ecommerce_store_cart_line( $store_id, $cart_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines', $store_id, $cart_id );
        return $this->client->post( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/lines/#edit-patch_ecommerce_stores_store_id_carts_cart_id_lines_line_id
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     */
    public function update_ecommerce_store_cart_line( $store_id, $cart_id, $line_id, array $args ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id );
        return $this->client->patch( $resource, $args );
    }

    /**
     * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/carts/lines/#delete-delete_ecommerce_stores_store_id_carts_cart_id_lines_line_id
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     *
     * @return bool
     */
    public function delete_ecommerce_store_cart_line( $store_id, $cart_id, $line_id ) {
        $resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id );
        return !! $this->client->delete( $resource );
	}

	/**
	 * Get a list of an account's available templates
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/templates/#read-get_templates
	 * @param array $args
	 * @return object
	 */
	public function get_templates( array $args = array() ) {
		$resource = '/templates';
		return $this->client->get( $resource, $args );
	}

	/**
	 * Create a new campaign
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#create-post_campaigns
	 * @param array $args
	 * @return object
	 */ 
	public function add_campaign( array $args ) {
		$resource = '/campaigns';
		return $this->client->post( $resource, $args );
	}

	/**
	 * Get all campaigns in an account
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#read-get_campaigns
	 * @param array $args
	 * @return object
	 */ 
	public function get_campaigns( array $args = array() ) {
		$resource = '/campaigns';
		return $this->client->get( $resource, $args );
	}
	
	/**
	 * Get information about a specific campaign.
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#read-get_campaigns_campaign_id
	 * @param string $campaign_id
	 * @param array $args
	 * @return object
	 */ 
	public function get_campaign( $campaign_id, array $args = array() ) {
		$resource = sprintf( '/campaigns/%s', $campaign_id );
		return $this->get( $resource, $args );
	}

	/**
	 * Update some or all of the settings for a specific campaign.
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#edit-patch_campaigns_campaign_id
	 * @param string $campaign_id
	 * @param array $args
	 * @return object
	 */ 
	public function update_campaign( $campaign_id, array $args ) {
		$resource = sprintf( '/campaigns/%s', $campaign_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * Remove a campaign from the MailChimp account
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#delete-delete_campaigns_campaign_id
	 * @param string $campaign_id
	 * @return bool
	 */  
	public function delete_campaign( $campaign_id ) {
		$resource = sprintf( '/campaigns/%s', $campaign_id );
		return !! $this->client->delete( $resource );
	}

	/**
	 * Perform an action on a MailChimp campaign
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/#action-post_campaigns
	 *
	 * @param string $campaign_id
	 * @param string $action 
	 * @param array $args
	 * @return object
	 */ 
	public function campaign_action( $campaign_id, $action, array $args = array() ) {
		$resource = sprintf( '/campaigns/%s/actions/%s', $campaign_id, $action );
		return $this->client->post( $resource, $args );
	}

	/**
	 * Get the HTML and plain-text content for a campaign
	 * 
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/content/#read-get_campaigns_campaign_id_content
	 * @param string $campaign_id
	 * @param array $args
	 * @return object
	 */ 
	public function get_campaign_content( $campaign_id, array $args = array() ) {
		$resource = sprintf( '/campaigns/%s/content', $campaign_id );
		return $this->client->get( $resource, $args );
	}
	
	/**
	 * Set the content for a campaign
	 *
	 * @link https://developer.mailchimp.com/documentation/mailchimp/reference/campaigns/content/#edit-put_campaigns_campaign_id_content
	 * @param string $campaign_id
	 * @param array $args 
	 * @return object
	 */ 
	public function update_campaign_content( $campaign_id, array $args ) {
		$resource = sprintf( '/campaigns/%s/content', $campaign_id );
		return $this->client->put( $resource, $args );
	}

	/**
	 * @return string
	 */
	public function get_last_response_body() {
		return $this->client->get_last_response_body();
	}

	/**
	 * @return array
	 */
	public function get_last_response_headers() {
		return $this->client->get_last_response_headers();
	}


}