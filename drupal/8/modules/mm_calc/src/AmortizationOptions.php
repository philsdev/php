<?php

namespace Drupal\mm_calc;

class AmortizationOptions {
	var $Periods       = 12;
	var $Compounds     = Calc::COMPOUND_PERIODS;
	var $PeriodPayment = 0;
	
	var $PaidPeriods   = 0;

	var $Taxes         = 0;
	
	var $PropertyTaxes = 0;
	var $Insurance     = 0;
	var $PMI           = 0;
	
	var $AdditionalPayment = 0;

	var $InterestOnly  = false;
	var $CalculateEveryPeriodPayment = false;

	function AmortizationOptions() {
		$this->Compounds = Calc::COMPOUND_PERIODS;
	}
}