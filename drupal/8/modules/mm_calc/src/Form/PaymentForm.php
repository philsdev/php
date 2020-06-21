<?php

namespace Drupal\mm_calc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mm_calc\Calc;
use Drupal\mm_calc\Utils;
use Drupal\mm_calc\AmortizationOptions;
use Drupal\mm_calc\AmortizationTable;
use Drupal\mm_calc\AmortizationPeriod;

class PaymentForm extends FormBase {
  public function getFormId() {

    // Unique ID of the form.
    return 'mm_calc_payment_form';
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {    

    // get user input
    $input = self::getFormValues();

    if (!empty($input)) {

      $results = [];

      $results['monthly_principal_interest'] = Calc::PeriodPayment(
        $input['loan_amount'], 
        $input['interest_rate'], 
        $input['length']
      );

      $results['monthly_taxes'] = $input['annual_taxes'] / 12;
      $results['monthly_insurance'] = $input['annual_insurance'] / 12;
      $results['monthly_pmi'] = ($input['loan_amount'] * ($input['annual_pmi'] / 100)) / 12;
      $results['annual_pmi'] = $results['monthly_pmi'] * 12;

      $results['ltv'] = Calc::getLtv($input['loan_amount'], $input['home_value']);

      $amortization_options = new AmortizationOptions;
      $amortization_options->PMI           = $results['annual_pmi'];
      $amortization_options->PropertyTaxes = $input['annual_taxes'];
      $amortization_options->Insurance     = $input['annual_insurance'];
  
      $amortization_table = Calc::BuildAmortizationTable(
        $input['home_value'], 
        $input['loan_amount'], 
        $input['interest_rate'],
        $input['length'],
        $amortization_options
      );

      if (!Calc::isPmiRequired($results['ltv'])) {
        $results['monthly_pmi'] = 0;
      }

      $results['monthly_total'] = $results['monthly_principal_interest'];
      $results['monthly_total'] += $results['monthly_taxes'];
      $results['monthly_total'] += $results['monthly_insurance'];
      $results['monthly_total'] += $results['monthly_pmi'];

      $form['analysis'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Financial Analysis'),
      ];

      $form['analysis']['monthly_principal_interest'] = [
        '#type' => 'item',
        '#title' => $this->t('Monthly Principal and Interest'),
        '#markup' => Utils::getCurrencyOutput($results['monthly_principal_interest']),
      ];

      $form['analysis']['monthly_taxes'] = [
        '#type' => 'item',
        '#title' => $this->t('Monthly Real Estate Taxes'),
        '#markup' => Utils::getCurrencyOutput($results['monthly_taxes']),
      ];

      $form['analysis']['monthly_insurance'] = [
        '#type' => 'item',
        '#title' => $this->t('Monthly Insurance'),
        '#markup' => Utils::getCurrencyOutput($results['monthly_insurance']),
      ];

      $form['analysis']['ltv'] = [
        '#type' => 'item',
        '#title' => $this->t('Loan to Value Ratio'),
        '#markup' => Utils::getPercentOutput($results['ltv']),
      ];

      $form['analysis']['monthly_pmi'] = [
        '#type' => 'item',
        '#title' => $this->t('Monthly PMI'),
        '#markup' => Utils::getCurrencyOutput($results['monthly_pmi']),
      ];

      $form['analysis']['months_with_pmi'] = [
        '#type' => 'item',
        '#title' => $this->t('Months With PMI'),
        '#markup' => $amortization_table->TotalPeriodsWithPMI,
      ];

      $form['analysis']['monthly_total'] = [
        '#type' => 'item',
        '#title' => $this->t('Total Monthly Payment'),
        '#markup' => Utils::getCurrencyOutput($results['monthly_total']),
      ];

      $form['analysis']['amortization_table'] = [
        '#type' => 'details',
        '#title' => $this->t('Amortization Table'),
        '#open' => false,
      ];

      $amortization_table_header = [
        'Period',
        'Interest Paid',
        'Principal',
        'PMI',
        'New Balance',
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
                Utils::getCurrencyOutput($item->PMI),
                '',
              ],
            ];
          } else {
             $amortization_table_rows[] = [
              'data' => [
                $item->Period,
                Utils::getCurrencyOutput($item->InterestPaid),
                Utils::getCurrencyOutput($item->PrincipalApplied),
                Utils::getCurrencyOutput($item->PMI),
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
            Utils::getCurrencyOutput($amortization_table->TotalPMI),
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

    $form['input_information']['property_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Property Information'),
      '#open' => true,
    ];

    $form['input_information']['property_information']['home_value'] = [
      '#type' => 'number',
      '#title' => $this->t('Home Value'),
      '#required' => TRUE,
      '#step' => 10000,
      '#min' => 50000,
      '#max' => 2000000,
      '#default_value' => Utils::getValue($input, $defaults, 'home_value'),
    ];

    $form['input_information']['taxes_insurance'] = [
      '#type' => 'details',
      '#title' => $this->t('Taxes and Insurance'),
      '#open' => true,
    ];

    $form['input_information']['taxes_insurance']['annual_taxes'] = [
      '#type' => 'number',
      '#title' => $this->t('Annual Taxes'),
      '#required' => TRUE,
      '#step' => 100,
      '#min' => 500,
      '#max' => 50000,
      '#default_value' => Utils::getValue($input, $defaults, 'annual_taxes'),
    ];

    $form['input_information']['taxes_insurance']['annual_insurance'] = [
      '#type' => 'number',
      '#title' => $this->t('Annual Insurance'),
      '#required' => TRUE,
      '#step' => 100,
      '#min' => 500,
      '#max' => 50000,
      '#default_value' => Utils::getValue($input, $defaults, 'annual_insurance'),
    ];

    $form['input_information']['taxes_insurance']['annual_pmi'] = [
      '#type' => 'number',
      '#title' => $this->t('Annual PMI'),
      '#required' => TRUE,
      '#step' => .1,
      '#min' => 0,
      '#max' => 3,
      '#default_value' => Utils::getValue($input, $defaults, 'annual_pmi'),
      '#description' => '%',
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

    // get ltv
    $ltv = Calc::getLtv($input['loan_amount'], $input['home_value']);

    $pmi_is_required = Calc::isPmiRequired($ltv);

    if ($pmi_is_required && empty($input['annual_pmi'])) {
      $form_state->setErrorByName('annual_pmi', $this->t('PMI must be added'));
    }

  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // get user input
    $input = $form_state->getUserInput();

    // save user input to session
    $_SESSION['mm_calc']['calculator']['mortgage']['payment'] = $input;
  }

  public function getFormValues() {
    if (isset($_SESSION['mm_calc']['calculator']['mortgage']['payment'])) {
      return $_SESSION['mm_calc']['calculator']['mortgage']['payment'];
    } else {
      return [];
    }
  }

}