<?php
/**
 * WC API Client Categories resource class
 *
 * @since 2.0
 */
class WC_API_Client_Resource_Product_Categories extends WC_API_Client_Resource {


	/**
	 * Setup the resource
	 *
	 * @since 2.0
	 * @param WC_API_Client $client class instance
	 */
	public function __construct( $client ) {

		parent::__construct( 'products/categories', 'product_cat', $client );
	}

	/**
	 * Get Categories
	 *
	 * GET /products/categories
	 * GET /products/categories/#{id}
	 *
	 * @since 2.0
	 * @param null|int $id category ID or null to get all categories
	 * @param array $args acceptable categories endpoint args, like `filter`
	 * @return array|object categories!
	 */
	public function get( $id = null, $args = array() ) {

		$this->set_request_args( array(
			'method' => 'GET',
			'path'   => $id,
			'params' => $args,
		) );

		return $this->do_request();
	}


	/**
	 * Create a category
	 *
	 * POST /products/categories
	 *
	 * @since 2.0
	 * @param array $data valid product data
	 * @return array|object your newly-created product
	 */
	public function create( $data ) {

		$this->set_request_args( array(
			'method' => 'POST',
			'body'   => $data,
		) );

		return $this->do_request();
	}


	/**
	 * Update a category
	 *
	 * PUT /product/categories/#{id}
	 *
	 * @since 2.0
	 * @param int $id product ID
	 * @param array $data product data to update
	 * @return array|object your newly-updated product
	 */
	public function update( $id, $data ) {

		$this->set_request_args( array(
			'method' => 'PUT',
			'path'   => $id,
			'body'   => $data,
		) );

		return $this->do_request();
	}


	/**
	 * Delete a categpry
	 *
	 * DELETE /products/categories/#{id}
	 *
	 * @since 2.0
	 * @param int $id category ID
	 * @param bool $force true to permanently delete the category, false to trash it
	 * @return array|object response
	 */
	public function delete( $id, $force = false ) {

		$this->set_request_args( array(
			'method' => 'DELETE',
			'path'   => $id,
			'params' => array( 'force' => $force ),
		) );

		return $this->do_request();
	}


}
