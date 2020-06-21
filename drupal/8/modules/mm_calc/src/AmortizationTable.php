<?php

namespace Drupal\mm_calc;

class AmortizationTable {
	var $Schedule = array();

	// Non-formatted values
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
	
	// Formatted values
	var $TotalPaymentAmount;
	var $TotalPrincipalApplied;
	var $TotalInterestPaid;
	var $TotalTaxSavings;
	var $TotalPropertyTaxes;
	var $TotalInsurance;
	var $TotalPMI;
	var $TotalRemainingBalance;
	var $TotalPeriodsWithPMI;
	var $TotalPeriods;
	
}