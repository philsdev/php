<?php
 
namespace Drupal\mm_calc\Controller;
 
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

class MMCalcController extends ControllerBase {
 
	public function calculator() {
		$links = [
			'/calculator/payment' => 'How much will my mortgage payment be?',
			'/calculator/principal' => 'Mortgage principal calculator',
			'/calculator/length' => 'Mortgage length calculator',
		];

		$item_list = [];

		foreach ($links as $uri => $title) {
			$items[] = Link::fromTextAndUrl($title, Url::fromUri('internal:' . $uri))->toString();
		}

		return array(
			'#theme' => 'item_list',
			'#items' => $items,
			'#list_type' => 'ul',
		);
	}

	public function calculator_payment() {
		$form = \Drupal::formBuilder()->getForm('Drupal\mm_calc\Form\PaymentForm');

		return array(
			'#markup' => '<h3>Calculate mortgage monthly payment with applicable 
				financial charges, including PMI, hazard insurance and 
				property taxes.</h3>',
			'form' => $form,
		);
	}

	public function calculator_principal() {
		$form = \Drupal::formBuilder()->getForm('Drupal\mm_calc\Form\PrincipalForm');

		return array(
			'#markup' => '<h3>This calculator allows you to "peek into the future", 
				allowing you to determine the remaining balance of your mortgage 
				after several years of payments.</h3>',
			'form' => $form,
		);
	}

	public function calculator_length() {
		$form = \Drupal::formBuilder()->getForm('Drupal\mm_calc\Form\LengthForm');

		return array(
			'#markup' => '<h3>This calculator will help you to determine your 
				savings in case you make bigger monthly payments.</h3>',
			'form' => $form,
		);
	}
 
}