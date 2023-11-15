<?php
namespace CTXFeed\V5\Template;
class Template {
	private $template;

	public function __construct( TemplateInterface $template ) {
		$this->template = $template;
	}

	public function get_feed() {
		return $this->template->get_feed();
	}

	public function get_header() {
		return $this->template->get_header();
	}

	public function get_footer() {
		return $this->template->get_footer();
	}

}
