<?php

namespace Drupal\mm_calc\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\mm_calc\Controller\MMCalcController;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "my_calc_calculator_menu_block",
 *   admin_label = @Translation("Calculator Menu"),
 * )
 */
class CalculatorMenu extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'links' => MMCalcController::calculator(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['mm_calc_block_settings'] = $form_state->getValue('mm_calc_block_settings');
  }
}