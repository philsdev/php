<?php
 
namespace Drupal\mm_calc\Controller;
 
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

class MMCalcController extends ControllerBase {
 
	public function getCalculatorLinks() {
		return [
			'payment' => [
				'title' => 'How much will my mortgage payment be?',
				'description' => 'Calculate mortgage monthly payment with applicable 
					financial charges, including PMI, hazard insurance and 
					property taxes',
				'form' => 'PaymentForm',
			],
			'principal' => [
				'title' => 'Mortgage principal calculator',
				'description' => 'This calculator allows you to "peek into the future", 
					allowing you to determine the remaining balance of your mortgage 
					after several years of payments.',
				'form' => 'PrincipalForm',
			],
			'length' => [
				'title' => 'Mortgage length calculator',
				'description' => 'This calculator will help you to determine your 
					savings in case you make bigger monthly payments.',
				'form' => 'LengthForm',
			],
		];
	}

	public function getFormPath($form_name) {
		$path = [
			'Drupal',
			'mm_calc',
			'Form',
			$form_name
		];

		return implode(chr(92), $path);
	}

	public function calculator() {
		$links = self::getCalculatorLinks();

		$item_list = [];

		foreach ($links as $stub => $properties) {
			$uri = "/calculator/{$stub}";

			$items[] = Link::fromTextAndUrl($properties['title'], Url::fromUri('internal:' . $uri))->toString();
		}

		return array(
			'#theme' => 'item_list',
			'#items' => $items,
			'#list_type' => 'ul',
		);
	}

	public function calculator_form($stub) {
		$links = self::getCalculatorLinks();

		if (isset($links[$stub])) {
			$link = $links[$stub];

			$title = $link['title'];

			$form_name = $link['form'];

			$form_path = self::getFormPath($form_name);

			$form = \Drupal::formBuilder()->getForm($form_path);

			$description = $link['description'];

			return array(
				'#markup' => "<h3>{$description}</h3>",
				'#title' => $title,
				'form' => $form,
			);
		} else {
			return array(
				'#markup' => "<h3>Calculator not found!</h3>",
			);
		}
	}
}