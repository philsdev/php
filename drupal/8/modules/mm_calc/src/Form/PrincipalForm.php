<?php

namespace Drupal\mm_calc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mm_calc\Calc;
use Drupal\mm_calc\Utils;
use Drupal\mm_calc\AmortizationOptions;
use Drupal\mm_calc\AmortizationTable;

class PrincipalForm extends FormBase {
  public function getFormId() {

    // Unique ID of the form.
    return 'mm_calc_principal_form';
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {    

    // get user input
    $input = self::getFormValues();

    if (!empty($input)) {
      // get default values
      $defaults = Utils::getDefaultValues();

      $amortization_options = new AmortizationOptions;
      $amortization_options->PaidPeriods = $input['months_paid'];
  
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

      $form['analysis']['total_interest_paid'] = [
        '#type' => 'item',
        '#title' => $this->t('Total Interest Paid'),
        '#markup' => Utils::getCurrencyOutput($amortization_table->TotalInterestPaid),
      ];

      $form['analysis']['principal_applied'] = [
        '#type' => 'item',
        '#title' => $this->t('Principal Applied'),
        '#markup' => Utils::getCurrencyOutput($amortization_table->TotalPrincipalApplied),
      ];

      $form['analysis']['balance'] = [
        '#type' => 'item',
        '#title' => $this->t('Balance'),
        '#markup' => Utils::getCurrencyOutput($amortization_table->TotalRemainingBalance),
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

    $form['input_information']['months_already_paid'] = [
      '#type' => 'details',
      '#title' => $this->t('Months Already Paid'),
      '#open' => true,
    ];

    $form['input_information']['months_already_paid']['months_paid'] = [
      '#type' => 'number',
      '#title' => $this->t('Months Paid'),
      '#required' => TRUE,
      '#step' => 1,
      '#min' => 1,
      '#max' => 600,
      '#default_value' => Utils::getValue($input, $defaults, 'months_paid'),
    ];
    
    $form['calculate'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Calculate'),
    );   

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // get user input
    $input = $form_state->getUserInput();

    $total_months = Calc::getMonths($input['length']);

    if ($input['months_paid'] >= $total_months) {
      $form_state->setErrorByName('months_paid', $this->t('Months must be remaining on the loan'));
    }
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // get user input
    $input = $form_state->getUserInput();

    // save user input to session
    $_SESSION['mm_calc']['calculator']['mortgage']['principal'] = $input;
  }

  public function getFormValues() {
    if (isset($_SESSION['mm_calc']['calculator']['mortgage']['principal'])) {
      return $_SESSION['mm_calc']['calculator']['mortgage']['principal'];
    } else {
      return [];
    }
  }

}