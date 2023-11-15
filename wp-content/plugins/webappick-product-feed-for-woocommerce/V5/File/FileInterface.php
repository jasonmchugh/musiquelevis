<?php
namespace CTXFeed\V5\File;
interface FileInterface {
	/**
	 * Make Header & Footer.
	 *
	 * @return array
	 */
	public function make_header_footer();

	/**
	 * Make Feed File body.
	 *
	 * @return false|string
	 */
	public function make_body();
}

