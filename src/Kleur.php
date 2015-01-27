<?php

namespace Kleur;

class Kleur {

	static function extendTwig($twig) {
		$twig->addFilter(new \Twig_SimpleFilter('rgbColor', ['\Kleur\Kleur', 'getRgbColor']));
		$twig->addFilter(new \Twig_SimpleFilter('extractColors', ['Kleur\Kleur', 'extractColors']));
		$twig->addFilter(new \Twig_SimpleFilter('orderColorsByBrightness', ['\Kleur\Kleur', 'orderColorsByBrightness']));
	}

	static function getRgbColor($color) {
		if ($color instanceof \MischiefCollective\ColorJizz\ColorJizz) {

		} elseif ($color instanceof \Katu\Types\TColorRgb) {
			$color = new \MischiefCollective\ColorJizz\Formats\RGB($color->r, $color->g, $color->b);
		} else {
			$color = (new \MischiefCollective\ColorJizz\Formats\Hex($color))->toRGB();
		}

		return $color;
	}

	static function extractColors($uri, $n = 1) {
		try {
			return \Katu\Utils\Cache::get(['image', 'color', 'extract', $uri, $n], function() use($uri, $n) {
				$client = new \League\ColorExtractor\Client;

				$image = \Katu\Utils\Image::getThumbnailUrl($uri, 600, 100, [
					'extension' => 'jpg',
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
