<?php

namespace Kleur;

class Kleur {

	static function extendTwig(&$twig) {
		$twig->addFilter(new \Twig_SimpleFilter('rgbColor',                ['\Kleur\Kleur', 'getRgbColor']));
		$twig->addFilter(new \Twig_SimpleFilter('extractColors',           ['\Kleur\Kleur', 'extractColors']));
		$twig->addFilter(new \Twig_SimpleFilter('orderColorsByBrightness', ['\Kleur\Kleur', 'orderColorsByBrightness']));
	}

	static function getRgbColor($color) {
		if ($color instanceof \MischiefCollective\ColorJizz\ColorJizz) {

		} elseif ($color instanceof \Katu\Types\TColorRgb) {
			$color = new \MischiefCollective\ColorJizz\Formats\RGB($color->r, $color->g, $color->b);
		} elseif (is_array($color)) {
			$color = new \MischiefCollective\ColorJizz\Formats\RGB($color[0], $color[1], $color[2]);
		} else {
			$color = (new \MischiefCollective\ColorJizz\Formats\Hex(hexdec(ltrim($color, '#'))))->toRGB();
		}

		return $color;
	}

	static function extractColors($uri, $n = 1) {
		try {
			return \Katu\Utils\Cache::get(['image', 'color', 'extract', $uri, $n], function() use($uri, $n) {
				$client = new \League\ColorExtractor\Client;

				$image = \Katu\Utils\Image::getVersionUrl($uri, [
					'filters' => [
						[
							'filter' => 'fit',
							'width' => 600,
							'height' => 600,
						],
					],
				]);

				$extracted = (array) $client->loadJpeg($image)->extract($n);

				return array_map(function($i) {
					$color = \Katu\Types\TColorRgb::getFromHex($i);

					return new \MischiefCollective\ColorJizz\Formats\RGB($color->r, $color->g, $color->b);
				}, $extracted);
			});
		} catch (\Exception $e) {
			\Katu\ErrorHandler::log($e);

			return false;
		}
	}

	static function orderColorsByBrightness($colors) {
		$sorted = [];

		foreach ((array) $colors as $color) {
			$sorted[] = [
				'color' => $color,
				'brightness' => $color->greyscale()->red,
			];
		}

		array_multisort(array_map(function($i) {
			return $i['brightness'];
		}, $sorted), $sorted, SORT_NUMERIC);

		$sorted = array_map(function($i) {
			return $i['color'];
		}, $sorted);

		return $sorted;
	}

}
