<?php

namespace CTXFeed\V5\Structure;

use CTXFeed\V5\Helper\FeedHelper;
use CTXFeed\V5\Utility\Config;

class Custom2Structure implements StructureInterface {

	/**
	 * @var Config $config
	 */
	private $config;
	/**
	 * @var false|int|string
	 */
	private $forSubLoop;
	/**
	 * @var int
	 */
	private $variationElementsStart;

	public function __construct( $config ) {
		$this->config = $config;
	}

	public function getXMLStructure() {
		$xml = trim( preg_replace( '/\+/', '', $this->config->feed_config_custom2 ) );

		// Get XML nodes for each product
		$getFeedBody = FeedHelper::get_string_between( $xml, '{{each product start}}', '{{each product end}}' );
		// Explode each element by new line
		$getElements = explode( "\n", $getFeedBody );

		$Elements = array();
		$i        = 1;

		$subLoopsStart = [
			'ifVariationAvailable' => '{{if variation available}}',
			'variation'            => '{{each variation start}}',
			'images'               => '{{each image start}}',
			'shipping'             => '{{each shipping start}}',
			'tax'                  => '{{each tax start}}',
			'categories'           => '{{each category start}}',
			'crossSale'            => '{{each crossSale start}}',
			'upSale'               => '{{each upSale start}}',
			'relatedProducts'      => '{{each relatedProducts start}}',
			'associatedProduct'    => '{{each associatedProduct start}}'
		];

		$subLoopsEnd = [
			'ifVariationAvailableEnd' => '{{endif variation}}',
			'variationEnd'            => '{{each variation end}}',
			'imagesEnd'               => '{{each image end}}',
			'shippingEnd'             => '{{each shipping end}}',
			'taxEnd'                  => '{{each tax end}}',
			'categoryEnd'             => '{{each category end}}',
			'crossSaleEnd'            => '{{each crossSale end}}',
			'upSaleEnd'               => '{{each upSale end}}',
			'relatedProductsEnd'      => '{{each relatedProducts end}}',
			'associatedProductEnd'    => '{{each associatedProduct end}}'
		];

		if ( ! empty( $getElements ) ) {
			foreach ( $getElements as $value ) {
				if ( ! empty( $value ) ) {

					if ( in_array( trim( $value ), $subLoopsStart ) ) {
						$this->forSubLoop = array_search( trim( $value ), $subLoopsStart, false );
						if ( $this->forSubLoop === 'variation' ) {
							$this->variationElementsStart = $i;
						}
						continue;
					}

					if ( in_array( trim( $value ), $subLoopsEnd ) ) {
						$loopKey = array_search( trim( $value ), $subLoopsEnd, false );
						if ( $loopKey === 'ifVariationAvailableEnd' ) {
							$Elements[ $i - 1 ]['for'] = 'ifVariationAvailable';
						}
						$this->forSubLoop = "";
						continue;
					}

					// Get Element info
					$element = FeedHelper::get_string_between( $value, '<', '>' );

					if ( empty( $element ) ) {
						continue;
					}

					// Set Element for
					$Elements[ $i ]['for'] = $this->forSubLoop;

					// Get starting element
					$Elements[ $i ]['start'] = $this->removeQuotation( $element );
					// Get ending element
					$Elements[ $i ]['end'] = FeedHelper::get_string_between( $value, '</', '>' );

					// Set CDATA status and remove CDATA
					$elementTextInfo                 = FeedHelper::get_string_between( $value, '>', '</' );
					$Elements[ $i ]['include_cdata'] = 'no';
					if ( stripos( $elementTextInfo, 'CDATA' ) !== false ) {
						$Elements[ $i ]['include_cdata'] = 'yes';
						$elementTextInfo                 = $this->removeCDATA( $elementTextInfo );
					}
					// Get Pattern of the xml node
					$Elements[ $i ]['elementTextInfo'] = $elementTextInfo;

					if ( ! empty( $Elements[ $i ]['elementTextInfo'] ) ) {
						// Get type of the attribute pattern
						if ( strpos( $elementTextInfo, '{' ) === false && strpos( $elementTextInfo, '}' ) === false ) {
							$Elements[ $i ]['attr_type']  = 'text';
							$Elements[ $i ]['attr_value'] = $elementTextInfo;
						} elseif ( strpos( $elementTextInfo, 'return' ) !== false ) {
							$Elements[ $i ]['attr_type'] = 'return';
							$return                      = FeedHelper::get_string_between( $elementTextInfo, '{(', ')}' );
							$Elements[ $i ]['to_return'] = $return;
						} elseif ( strpos( $elementTextInfo, 'php ' ) !== false ) {
							$Elements[ $i ]['attr_type'] = 'php';
							$php                         = FeedHelper::get_string_between( $elementTextInfo, '{(', ')}' );
							$Elements[ $i ]['to_return'] = str_replace( 'php', '', $php );
						} else {
							$Elements[ $i ]['attr_type'] = 'attribute';
							$attribute                   = FeedHelper::get_string_between( $elementTextInfo, '{', '}' );
							$getAttrBaseFormat           = explode( ',', $attribute );

							$attrInfo = $getAttrBaseFormat[0];
							if ( count( $getAttrBaseFormat ) > 1 ) {
								$j = 0;
								foreach ( $getAttrBaseFormat as $_value ) {
									if ( $value !== "" ) {
										$formatters = FeedHelper::get_string_between( $_value, '[', ']' );
										if ( ! empty( $formatters ) ) {
											$Elements[ $i ]['formatter'][ $j ] = $formatters;
											$j ++;
										}
									}
								}
							}

							$getAttrCodes                = explode( '|', $attrInfo );
							$Elements[ $i ]['attr_code'] = $getAttrCodes[0];
							$Elements[ $i ]['id_type']   = isset( $getAttrCodes[1] ) ? $getAttrCodes[1] : '';
						}

						// Get prefix of the attribute node value
						$Elements[ $i ]['prefix'] = '';
						if ( 'text' !== $Elements[ $i ]['attr_type'] && strpos( trim( $elementTextInfo ), '{' ) !== 0 ) {
							$getPrefix                = explode( '{', $elementTextInfo );
							$Elements[ $i ]['prefix'] = ( count( $getPrefix ) > 1 ) ? $getPrefix[0] : '';
						}
						// Get suffix of the attribute node value
						$Elements[ $i ]['suffix'] = '';
						if ( 'text' != $Elements[ $i ]['attr_type'] && strpos( trim( $elementTextInfo ), '}' ) !== 0 ) {
							$getSuffix                = explode( '}', $elementTextInfo );
							$Elements[ $i ]['suffix'] = ( count( $getSuffix ) > 1 ) ? $getSuffix[1] : '';
						}
					}

					preg_match_all( '/{(.*?)}/', $element, $matches );
					$startCodes                   = ( isset( $matches[0] ) ? $matches[0] : '' );
					$Elements[ $i ]['start_code'] = array_filter( $startCodes );
					$i ++;
				}
			}
		}

		return [
			'variationElementsStart' => $this->variationElementsStart,
			'structure'              => $Elements
		];
	}

	/** Remove CDATA from String
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	private function removeCDATA( $output ) {
		return str_replace( [ "<![CDATA[", "]]>" ], "", $output );
	}

	/**
	 * Remove Quotation mark from xml element.
	 *
	 * @return string
	 */
	private function removeQuotation( $string ) {
		return wp_unslash( str_replace( array( "'", "\"", "&quot;" ), "", $string ) );
	}

	public function getCSVStructure() {
		// TODO: Implement getCSVStructure() method.
	}

	public function getTSVStructure() {
		// TODO: Implement getTSVStructure() method.
	}

	public function getTXTStructure() {
		// TODO: Implement getTXTStructure() method.
	}

	public function getXLSStructure() {
		// TODO: Implement getXLSStructure() method.
	}

	public function getJSONStructure() {
		// TODO: Implement getJSONStructure() method.
	}

	public function getXLSXStructure() {
		// TODO: Implement getXLSXStructure() method.
	}
}
