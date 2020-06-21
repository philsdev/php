<?php

namespace Drupal\mm_calc\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the settings administration form.
 *
 * @internal
 */
class SettingsForm extends FormBase {

  /**
   * The permission handler.
   *
   * @var \Drupal\user\PermissionHandlerInterface
   */
  protected $permissionHandler;

  /**
   * The role storage.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new UserPermissionsForm.
   *
   * @param \Drupal\user\PermissionHandlerInterface $permission_handler
   *   The permission handler.
   * @param \Drupal\user\RoleStorageInterface $role_storage
   *   The role storage.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(PermissionHandlerInterface $permission_handler, RoleStorageInterface $role_storage, ModuleHandlerInterface $module_handler) {
    $this->permissionHandler = $permission_handler;
    $this->roleStorage = $role_storage;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.permissions'),
      $container->get('entity_type.manager')->getStorage('user_role'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mm_calc_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // get saved settings
    $settings = \Drupal::config('mm_calc.settings')->get('calculators');

    $form['defaults'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Default Values'),
    ];

    $form['defaults']['loan_amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Loan Amount'),
      '#required' => TRUE,
      '#step' => 10000,
      '#min' => 50000,
      '#max' => 2000000,
      '#default_value' => $settings['loan_amount'],
    ];

    $form['defaults']['interest_rate'] = [
      '#type' => 'number',
      '#title' => $this->t('Interest Rate'),
      '#required' => TRUE,
      '#step' => .25,
      '#default_value' => $settings['interest_rate'],
      '#description' => '%',
    ];

    $form['defaults']['length'] = [
      '#type' => 'number',
      '#title' => $this->t('Length'),
      '#required' => TRUE,
      '#step' => 1,
      '#min' => 1,
      '#max' => 40,
      '#default_value' => $settings['length'],
      '#description' => 'yrs',
    ];

    $form['defaults']['monthly_payment'] = [
      '#type' => 'number',
      '#title' => $this->t('Monthly Payment'),
      '#required' => TRUE,
      '#step' => 50,
      '#min' => 50,
      '#max' => 5000,
      '#default_value' => $settings['monthly_payment'],
    ];

    $form['defaults']['months_paid'] = [
      '#type' => 'number',
      '#title' => $this->t('Months Paid'),
      '#required' => TRUE,
      '#step' => 1,
      '#min' => 1,
      '#max' => 600,
      '#default_value' => $settings['months_paid'],
    ];    

    $form['defaults']['home_value'] = [
      '#type' => 'number',
      '#title' => $this->t('Home Value'),
      '#required' => TRUE,
      '#step' => 10000,
      '#min' => 50000,
      '#max' => 2000000,
      '#default_value' => $settings['home_value'],
    ];

    $form['defaults']['annual_taxes'] = [
      '#type' => 'number',
      '#title' => $this->t('Annual Taxes'),
      '#required' => TRUE,
      '#step' => 100,
      '#min' => 500,
      '#max' => 50000,
      '#default_value' => $settings['annual_taxes'],
    ];

    $form['defaults']['annual_insurance'] = [
      '#type' => 'number',
      '#title' => $this->t('Annual Insurance'),
      '#required' => TRUE,
      '#step' => 100,
      '#min' => 500,
      '#max' => 50000,
      '#default_value' => $settings['annual_insurance'],
    ];

    $form['defaults']['annual_pmi'] = [
      '#type' => 'number',
      '#title' => $this->t('Annual PMI'),
      '#required' => TRUE,
      '#step' => .1,
      '#min' => 0,
      '#max' => 3,
      '#default_value' => $settings['annual_pmi'],
    ];

    $form['actions'] = [
      '#type' => 'actions'
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save calculator settings'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // get copy of input
    $input = $form_state->getUserInput();

    // remove unnecessary fields
    unset($input['form_build_id']);
    unset($input['form_token']);
    unset($input['form_id']);
    unset($input['op']);
    
    \Drupal::service('config.factory')
      ->getEditable('mm_calc.settings')
      ->set('calculators', $input)
      ->save();

    $this->messenger()->addStatus($this->t('The calculator settings have been saved.'));
  }

}
