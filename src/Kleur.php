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
