<?php

namespace Drupal\mm_calc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mm_calc\Calc;
use Drupal\mm_calc\Utils;
use Drupal\mm_calc\AmortizationOptions;
use Drupal\mm_calc\AmortizationTable;

class LengthForm extends FormBase {
  public function getFormId() {

    // Unique ID of the form.
    return 'mm_calc_length_form';
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {    

    // get user input
    $input = self::getFormValues();

    if (!empty($input)) {
      // get default values
      $defaults = Utils::getDefaultValues();

      $results = [];

      $results['original_payment'] = Calc::PeriodPayment(
        $input['loan_amount'], 
        $input['interest_rate'],
        $input['length']
      );

      $results['new_length'] = Calc::LoanLength(
        $input['loan_amount'], 
        $input['interest_rate'],
        $input['monthly_payment']
      );

      if (is_numeric($results['new_length'])) {

        $results['time_interval_parts'] = Calc::getTimeIntervalParts($results['new_length']);

        $results['time_interval_display'] = Calc::getTimeIntervalDisplay(
          $results['time_interval_parts']['years'],
          $results['time_interval_parts']['months']
        );

        $amortization_options = new AmortizationOptions;
        $amortization_options->PeriodPayment = $input['monthly_payment'];
    
        $amortization_table = Calc::BuildAmortizationTable(
          $defaults['home_value'], 
          $input['loan_amount'], 
          $input['interest_rate'],
          $input['length'],
          $amortization_options
        );     

        $form['analysis'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Financial Analysis'),
        ];

        $form['analysis']['original_payment'] = [
          '#type' => 'item',
          '#title' => $this->t('Original Payment'),
          '#markup' => Utils::getCurrencyOutput($results['original_payment']),
        ];

        $form['analysis']['new_payment'] = [
          '#type' => 'item',
          '#title' => $this->t('New Payment'),
          '#markup' => Utils::getCurrencyOutput($input['monthly_payment']),
        ];

        $form['analysis']['new_length'] = [
          '#type' => 'item',
          '#title' => $this->t('New Length of Loan'),
          '#markup' => $results['time_interval_display'],
        ];

        $form['analysis']['amortization_table'] = [
          '#type' => 'details',
          '#title' => $this->t('Amortization Table'),
          '#open' => false,
        ];

        $amortization_table_header = [
          'Period',
          'Interest',
          'Principal',
          'Balance',
        ];
        
        $amortization_table_rows = [];

        if (count($amortization_table->Schedule)) {
          foreach ($amortization_table->Schedule as $item) {
            if ($item->Type == 'SubTotal') {
              $amortization_table_rows[] = [
                'class' => ['summary'],
                'data' => [
                  'Year ' . $item->Period,
                  Utils::getCurrencyOutput($item->InterestPaid),
                  Utils::getCurrencyOutput($item->PrincipalApplied),
                  '',
                ],
              ];
            } else {
               $amortization_table_rows[] = [
                'data' => [
                  $item->Period,
                  Utils::getCurrencyOutput($item->InterestPaid),
                  Utils::getCurrencyOutput($item->PrincipalApplied),
                  Utils::getCurrencyOutput($item->RemainingBalance),
                ],
              ];
            }
          }

          $amortization_table_rows[] = [
            'class' => ['total last'],
            'data' => [
              'Total',
              Utils::getCurrencyOutput($amortization_table->TotalInterestPaid),
              Utils::getCurrencyOutput($amortization_table->TotalPrincipalApplied),
              Utils::getCurrencyOutput($amortization_table->TotalRemainingBalance),
            ],
          ];
        }    

        $form['analysis']['amortization_table']['schedule'] = [
          '#type' => 'table',
          '#sticky' => true,
          '#header' => $amortization_table_header,
          '#rows' => $amortization_table_rows,
        ];
      }
    }

    // get saved settings
    $defaults = Utils::getDefaultValues();

    // build form
    $form['input_information'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Input Information'),
    ];

    $form['input_information']['loan_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Loan Information'),
      '#open' => true,
    ];

    $form['input_information']['loan_information']['loan_amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount'),
      '#required' => TRUE,
      '#step' => 10000,
      '#min' => 10000,
      '#max' => 2000000,
      '#default_value' => Utils::getValue($input, $defaults, 'loan_amount'),
    ];

    $form['input_information']['loan_information']['interest_rate'] = [
      '#type' => 'number',
      '#title' => $this->t('Interest Rate'),
      '#required' => TRUE,
      '#step' => .25,
      '#default_value' => Utils::getValue($input, $defaults, 'interest_rate'),
      '#description' => '%',
    ];

    $form['input_information']['loan_information']['length'] = [
      '#type' => 'number',
      '#title' => $this->t('Length'),
      '#required' => TRUE,
      '#step' => 1,
      '#min' => 1,
      '#max' => 40,
      '#default_value' => Utils::getValue($input, $defaults, 'length'),
      '#description' => 'years',
    ];

    $form['input_information']['considered_payment'] = [
      '#type' => 'details',
      '#title' => $this->t('Considered Monthly Payment'),
      '#open' => true,
    ];

    $form['input_information']['considered_payment']['monthly_payment'] = [
      '#type' => 'number',
      '#title' => $this->t('Monthly Payment'),
      '#required' => TRUE,
      '#step' => 50,
      '#min' => 50,
      '#max' => 10000,
      '#default_value' => Utils::getValue($input, $defaults, 'monthly_payment'),
    ];
    
    $form['calculate'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Calculate'),
    );   

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // get user input
    $input = $form_state->getUserInput();

    // save user input to session
    $_SESSION['mm_calc']['calculator']['mortgage']['length'] = $input;
  }

  public function getFormValues() {
    if (isset($_SESSION['mm_calc']['calculator']['mortgage']['length'])) {
      return $_SESSION['mm_calc']['calculator']['mortgage']['length'];
    } else {
      return [];
    }
  }

}