<?php

namespace CTXFeed\V5\File;
class JSON implements FileInterface {
	
	private $data;
	private $config;
	
	public function __construct( $data, $config ) {
		
		$this->data   = $data;
		$this->config = $config;
	}
	
	/**
	 * Make Header & Footer.
	 *
	 * @return array
	 */
	public function make_header_footer() {
		$HF = [
			'header' => '',
			'footer' => '',
		];
		
		return apply_filters( "ctx_make_{$this->config->feedType}_feed_header_footer", $HF, $this->data, $this->config );
	}
	
	/**
	 * Make JSON body.
	 *
	 * @return string
	 */
	public function make_body() {
		
		$content = $this->data;
		
		//TODO: Multi dimension to single array.
		return apply_filters( "ctx_make_{$this->config->feedType}_feed_body", $content, $this->data, $this->config );
	}
	
}

