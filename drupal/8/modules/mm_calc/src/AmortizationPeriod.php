<?php

namespace Drupal\mm_calc;

class AmortizationPeriod {
	var $PeriodPrincipalBalance;
	var $PeriodPaymentAmount;
	var $PeriodInterestPaid;
	var $PeriodPrincipalApplied;
	var $PeriodTaxSavings;
	var $PeriodPropertyTaxes;
	var $PeriodInsurance;
	var $PeriodPMI;
	var $PeriodRemainingBalance;

	
	// formatted values
	var $Period;
	var $PaymentAmount;
	var $PrincipalApplied;
	var $InterestPaid;
	var $TaxSavings;
	var $PropertyTaxes;
	var $Insurance;
	var $PMI;
	var $RemainingBalance;

	var $PeriodsWithPMI;
	var $Periods;	

	var $Type;
}