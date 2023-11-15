<?php
namespace CTXFeed\V5\Template;
//TODO: Google Review Implementation
use CTXFeed\V5\Utility\Config;
use CTXFeed\V5\File\FileFactory;
use CTXFeed\V5\Product\ProductFactory;

class GooglereviewTemplate implements TemplateInterface {
	/**
	 * @var Config $config Contain Feed Config.
	 */
	private $config;
	/**
	 * @var array $ids Contain Product Ids.
	 */
	private $ids;
	/**
	 * @var mixed
	 */
	private $structure;

	public function __construct( $ids, $config ) {
		$this->ids    = $ids;
		$this->config = $config;
		$this->structure = TemplateFactory::get_structure( $config, $this->ids);
	}

	/**
	 * Get Feed Body.
	 *
	 * @return false|string
	 */
	public function get_feed() {
		$feed = ProductFactory::get_content( $this->ids, $this->config,$this->structure );

		return $feed->make_body();
	}

	/**
	 * Get Feed Header.
	 *
	 * @return mixed
	 */
	public function get_header() {
		$feed = FileFactory::GetData( $this->structure, $this->config );
		$feed = $feed->make_header_footer();

		return $feed['header'];
	}

	/**
	 * Get Feed Footer.
	 *
	 * @return mixed
	 */
	public function get_footer() {
		$feed = FileFactory::GetData( $this->ids, $this->config );
		$feed = $feed->make_header_footer();

		return $feed['footer'];
	}
}
