<?php
namespace CTXFeed\V5\File;
class FileInfo {
	private $file;

	public function __construct( FileInterface $file) {
		$this->file   = $file;
	}

	/**
	 * Make Header Footer.
	 *
	 * @return array
	 */
	public function make_header_footer() {
		return $this->file->make_header_footer();
	}

	/**
	 * Make XML body
	 *
	 * @return false|string
	 */
	public function make_body() {
		return $this->file->make_body();
	}
}