<?php
namespace CTXFeed\V5\File;
class XLSX implements FileInterface {


	private $data;
	private $config;

	public function __construct( $data, $config ) {

		$this->data = $data;
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
	public function make_body(  ) {

		$column    = '';
		$enclosure = ! $this->config->get_enclosure() ? '"' : $this->config->get_enclosure();
		$delimiter = $enclosure . $this->config->get_delimiter() . $enclosure;

		foreach ( $this->data as $product ) {
			$column .= $enclosure . $this->implode_all( $delimiter, $enclosure, $product ) . $enclosure . "\n";
		}

		return apply_filters( "ctx_make_{$this->config->feedType}_feed_body", $column, $this->data, $this->config );
	}

	/**
	 * Convert Multi Dimension array to string.
	 *
	 * @param $delimiter
	 * @param $enclosure
	 * @param $arr
	 *
	 * @return string
	 */
	private function implode_all( $delimiter, $enclosure, $arr ) {
		foreach ( $arr as $i => $iValue ) {
			if ( is_array( $iValue ) ) {
				$arr[ $i ] = $this->implode_all( $delimiter, $enclosure, $iValue );
			}
		}

		return implode( $delimiter, $arr );
	}
}

