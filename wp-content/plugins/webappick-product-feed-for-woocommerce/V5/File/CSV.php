<?php

namespace CTXFeed\V5\File;
use CTXFeed\V5\Utility\Config;

/**
 *
 */
class CSV implements FileInterface {

	/**
	 * @var
	 */
	private $data;
	/**
	 * @var Config $config
	 */
	private $config;

	/**
	 * @param $data
	 * @param $config
	 */
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


		$enclosure = $this->config->get_enclosure();
		$delimiter = $this->config->get_delimiter();

		if ( ! empty( $this->data ) && is_array( $this->data ) ) {
			$first = $this->implode_all( $delimiter, $enclosure, $this->data, 'key' ) . "\n";

			$HF = [
				'header' => $first,
				'footer' => '',
			];
		}

		return apply_filters( "ctx_make_{$this->config->feedType}_feed_header_footer", $HF, $this->data, $this->config );
	}

	/**
	 * Make CSV body.
	 *
	 * @return string
	 */
	public function make_body() {

		$column    = '';
		$enclosure = $this->config->get_enclosure();
		$delimiter = $this->config->get_delimiter();

		foreach ( $this->data as $product ) {
			$column .= $this->implode_all( $delimiter, $enclosure, $product ) . "\n";
		}

		return apply_filters( "ctx_make_{$this->config->feedType}_feed_body", $column, $this->data, $this->config );
	}

	/**
	 * Convert Multi Dimension array to string.
	 *
	 * @param        $delimiter
	 * @param        $enclosure
	 * @param        $arr
	 * @param string $kv Key or Value
	 *
	 * @return string
	 */
	private function implode_all( $delimiter, $enclosure, $arr, $kv = 'value' ) {
		foreach ( $arr as $i => $iValue ) {
			if ( is_array( $iValue ) ) {
				if ( 'value' === $kv ) {
					$arr[ $i ] = $enclosure . $this->implode_all( $delimiter, $enclosure, $iValue, $kv ) . $enclosure;
				} else {
					$arr[ $i ] = $enclosure . array_key_first( $iValue ) . $enclosure;
				}
			}
		}

		return implode( $delimiter, $arr );
	}
}

