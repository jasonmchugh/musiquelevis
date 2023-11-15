<?php

namespace CTXFeed\V5\Template;

use CTXFeed\V5\Utility\Config;
use CTXFeed\V5\Structure\CustomStructure;

class TemplateFactory
{


	/**
	 * @param $ids array
	 * @param $config Config
	 *
	 * @return Template
	 */
	public static function MakeFeed($ids, $config)
	{
		$group_class = self::get_grouped_templates($config->provider);
		if ($group_class) {
			$class = "\CTXFeed\V5\Template\\" . ucfirst($group_class) . 'Template';
		} else {
			$class = "\CTXFeed\V5\Template\\" . ucfirst($config->get_feed_template()) . 'Template';
		}

		if (class_exists($class)) {
			return new Template(new $class($ids, $config));
		}

		return new Template(new CustomTemplate($ids, $config));
	}

	/**
	 * @param $config
	 * @param $ids
	 *
	 * @return mixed
	 */
	public static function get_structure($config, $ids = [])
	{
		$template = $config->provider;

		$class = self::get_grouped_templates($config->provider);
		if ($class) {
			$template = $class;
		}
		$template = ucfirst(str_replace(['_', '.'], '', $template));
		$class = "\CTXFeed\V5\Structure\\" . $template . 'Structure';

		$file_type = strtoupper($config->feedType);
		$method = 'get' . $file_type . 'Structure';

		if ('Googlereview' === $template && class_exists($class) && method_exists($class, $method)) {
			return (new $class($config, $ids))->$method();
		}

		if (class_exists($class) && method_exists($class, $method)) {
			return (new $class($config))->$method();
		}

		return (new CustomStructure($config))->$method();
	}

	public static function get_feed_config($config)
	{
		return new Config($config);
	}


	/**
	 * @param $provider
	 *
	 * @return false|string
	 */
	public static function get_grouped_templates($provider)
	{
		$group_classes = [
			'google' => ['google_shopping_action', 'google_local', 'google_local_inventory'],
			'custom2' => ['custom2', 'admarkt', 'yandex_xml', 'glami'],
			'pinterest' => ['pinterest', 'pinterest_rss'],
		];

		foreach ($group_classes as $class => $providers) {
			if (in_array($provider, $providers)) {
				return $class;
			}
		}

		return false;
	}
}
