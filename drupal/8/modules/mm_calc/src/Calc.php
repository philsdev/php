<?php

namespace Drupal\mm_calc;

class Calc {

	const COMPOUND_PERIODS = 12;
	const LTV_MIN_PMI = 0.8;

	public static function getLtv($loan_amount, $home_value) {
		if ($home_value > 0) {
			return ($loan_amount / $home_value);
		} else {
			return 0;
		}
	}

	public static function isPmiRequired($ltv) {
		if ($ltv > self::LTV_MIN_PMI) {
			return true;
		} else {
			return false;
		}
	}

	public static function getMonths($years) {
		return $years * 12;
	}

	public static function getTimeIntervalParts($length) {
		$years  = floor($length);
		$m = $length - $years;

		$months = ceil(12 * $m);
		
		if ($m < 1/365) {
			$months--;
		}
		
		if ($months == 12) {
			$years++;
			$months = 0;
		}

		return [
			'years' => $years,
			'months' => $months,
		];
	}

	public static function getTimeIntervalDisplay($years=0, $months=0) {
		$display = [];

		if ($years > 0) {
			switch ($years) {
				case 1: {
					$display[] = "1 year";
					break;
				}
				default: {
					$display[] = "{$years} years";
					break;
				}
			}
		}

		if ($months > 0) {
			switch ($months) {
				case 1: {
					$display[] = "1 month";
					break;
				}
				default: {
					$display[] = "{$months} months";
					break;
				}
			}
		}

		return implode(", ", $display);
	}

	public static function PeriodPayment($amount, $interest, $length, $annualPayments = 12, $annualCompounds = 0) {
		
		if ($annualCompounds == 0) {
			$annualCompounds = self::COMPOUND_PERIODS;
		}

	    $interest = $interest / 100 / $annualCompounds;
	    
	    $interest = pow(1 + $interest , $annualCompounds / $annualPayments) - 1;
	    
	    if ($annualPayments <= 0 || $length <= 0 || $interest <= 0) {
	    	return null;
	    }
	    
    	return $amount * ($interest / (1 - pow(( 1 + $interest ), -$annualPayments * $length) ) );
	}
	
	public static function LoanAmount($periodPayment, $interest, $length, $annualPayments = 12, $annualCompounds = 0) {
		
		if ($annualCompounds == 0) {
			$annualCompounds = self::COMPOUND_PERIODS;
		}

	    $interest = $interest / 100 / $annualCompounds;
	    $interest = pow(1 + $interest , $annualCompounds / $annualPayments) - 1;
	    
	    if ($annualPayments <= 0 || $length <= 0 || $interest <= 0) {
	    	return null;
	    }
	    
		$N = $annualPayments * $length;
	    	
    	return $periodPayment * (pow(1 + $interest, $N) - 1) / ($interest * pow(1 + $interest, $N));
	}

	public static function LoanLength($amount, $interest, $periodPayment, $annualPayments = 12, $annualCompounds = 0) {
		
		if ($annualCompounds == 0) {
			$annualCompounds = self::COMPOUND_PERIODS;
		}

	    $interest = $interest / 100;
	    $interest = pow(1 + $interest, $annualCompounds / $annualCompounds) - 1;
	    
		return - (1 / $annualPayments ) * ( log ( 1 - (($amount * $interest) / ($periodPayment*$annualPayments)) ) / log ( 1+$interest/$annualPayments ) ); 
	}
	
	public static function BuildAmortizationTable($homevalue, $amount, $interest, $length, $options = null) {

		if ($options == null) {
			$options = new AmortizationOptions();
		}

		$periods   = $options->Periods;
		$compounds = $options->Compounds;

		if (empty($options->PeriodPayment)) {
			if (!$options->InterestOnly) {
				$periodPayment = Calc::PeriodPayment($amount, $interest, $length, $periods, $compounds);
			} else {
				$periodPayment = $amount * $interest / 100 / 12;
			}
		} else {
			$periodPayment = $options->PeriodPayment;
		}

		$periodPropertyTaxes = $options->PropertyTaxes / $periods;

		$periodInsurance     = $options->Insurance / $periods;
		$periodPMI           = $options->PMI / $periods;
			
		$totalPeriods = $periods * $length;
		
		$totalPayment = $periodPayment * $totalPeriods;
	    $interest = $interest / 100 / $compounds;
	    $interest = pow(1 + $interest , $compounds / $periods) - 1;

		$LTV = self::getLtv($amount, $homevalue);

		$currentBalance = $amount;
		$currentPeriod  = 1;
		$periodsWithPMI = 0;
		$i=0;

		$runningPMI = 0;
		$yearPMI = 0;
		$runningPayment = 0;
		$runningInterestPaid = 0;
		$runningTaxSavings = 0;
		$runningPrincipalApplied = 0;
		$runningPropertyTaxes = 0;
		$runningInsurance = 0;
		$yearPayment = 0;
		$yearInterestPaid = 0;
		$yearTaxSavings = 0;
		$yearPrincipalApplied = 0;
		$yearPropertyTaxes = 0;
		$yearInsurance = 0;
		$BreakCycle = false;
		
		$values = new AmortizationTable();
		
		while($currentBalance > 0) {
			// Limiter for the length of paid periods
			if ($options->PaidPeriods != 0 && $options->PaidPeriods < $currentPeriod + 1) {
				$BreakCycle = true;
			}
				
			if ($options->CalculateEveryPeriodPayment) {
				if (!$options->InterestOnly) {
					$periodPayment = Calc::PeriodPayment($currentBalance, $interest * $compounds * 100, $length, $periods, $compounds);
				} else {
					$periodPayment = $currentBalance * $interest;
				}
					
				$periodPayment += $options->AdditionalPayment;
				
				if ($periodPayment > $currentBalance) {
					$periodPayment = $currentBalance * (1 + $interest);
					$BreakCycle = true;
				}
			}
				
			$amortizationPeriod = new AmortizationPeriod();
			$amortizationPeriod->Period            = $currentPeriod;
			$amortizationPeriod->PrincipalBalance  = number_format($currentBalance, "2", ".", "");
			$amortizationPeriod->PaymentAmount     = number_format($periodPayment, "2", ".", "");

			if (!$options->InterestOnly) {
				$interestPaid = $currentBalance * $interest ;
			} else{
				if ($BreakCycle) {
					$interestPaid = $currentBalance * $interest;
				} else {
					$interestPaid = $periodPayment - $options->AdditionalPayment;
				}
			}
				
			$taxSavings = $interestPaid * $options->Taxes / 100;

			$amortizationPeriod->InterestPaid = number_format($interestPaid, "2", ".", "");

			// Monthly payment minus monthly interest is the value of 
			// applied principal.
			if (!$options->InterestOnly) {
				$principalApplied = $periodPayment - $interestPaid;
			} else {
				$principalApplied = $options->AdditionalPayment;

				if ($principalApplied > $currentBalance) {
					$principalApplied = $currentBalance;
				}
			}
			
			$currentBalance -= $principalApplied;

			// If it is less than zero - lets calculate last payment.
			if ($currentBalance <= 0.001) {
				$periodPayment    = $principalApplied + $currentBalance;
				$principalApplied = $periodPayment;
				$currentBalance   = 0;
				$BreakCycle       = true;
			}

			//if (!$options->InterestOnly) {
				$amortizationPeriod->PrincipalApplied   = number_format($principalApplied, "2", ".", "");
			//} else {
			//	$amortizationPeriod->PrincipalApplied   = '0';
			//}

			// Caculating LoanToValue for the current period
			$LTV = self::getLtv($currentBalance, $homevalue);

			// If LTV for current period is still greater 
			// than 80%, then PMI must be applied.
			if (self::isPmiRequired($LTV)) {
				$amortizationPeriod->PMI       = number_format($periodPMI, "2", ".", "");
				$amortizationPeriod->PeriodPMI = $periodPMI;
				$runningPMI           += $periodPMI;
				$yearPMI              += $periodPMI;
				$periodsWithPMI++;
			} else {
				$amortizationPeriod->PMI            = number_format(0, "2", ".", "");
			}

			$amortizationPeriod->TaxSavings         = number_format($taxSavings, "2", ".", "");
			$amortizationPeriod->PropertyTaxes      = number_format($periodPropertyTaxes, "2", ".", "");
			$amortizationPeriod->Insurance          = number_format($periodInsurance, "2", ".", "");				
			$amortizationPeriod->RemainingBalance   = number_format($currentBalance, "2", ".", "");
			
			$amortizationPeriod->PeriodPrincipalBalance   = $currentBalance;
			$amortizationPeriod->PeriodPaymentAmount      = $periodPayment;
			$amortizationPeriod->PeriodInterestPaid       = $interestPaid;
			$amortizationPeriod->PeriodPrincipalApplied   = $principalApplied;
			$amortizationPeriod->PeriodTaxSavings         = $taxSavings;
			$amortizationPeriod->PeriodPropertyTaxes      = $periodPropertyTaxes;
			$amortizationPeriod->PeriodInsurance          = $periodInsurance;
			$amortizationPeriod->PeriodRemainingBalance   = $currentBalance;


			if ($currentBalance > 0.001 || $BreakCycle) {
				$values->Schedule[$i] = $amortizationPeriod;
			}

			// Calculating running totals
			$runningPayment           += $periodPayment;
			$runningInterestPaid      += $interestPaid;
			$runningTaxSavings        += $taxSavings;
			$runningPrincipalApplied  += $principalApplied;
			$runningPropertyTaxes     += $periodPropertyTaxes;
			$runningInsurance         += $periodInsurance;

			// Calculating annual totals.
			$yearPayment           += $periodPayment;
			$yearInterestPaid      += $interestPaid;
			$yearTaxSavings        += $taxSavings;
			$yearPrincipalApplied  += $principalApplied;
			$yearPropertyTaxes     += $periodPropertyTaxes;
			$yearInsurance         += $periodInsurance;

			// If there are one from these three cases:
			// 1) New year happens
			// 2) Last payment
			// 3) Exceeded an amount of paid periods
			// then we need to calculate subtotals for the current year.
			if ((($currentPeriod % $periods) == 0 && $i != 0) || ($BreakCycle)) {
				$i++;
				$amortizationPeriod = new AmortizationPeriod();
				$amortizationPeriod->Type             = 'SubTotal';
				$amortizationPeriod->Period           = ceil($currentPeriod / $periods);
				$amortizationPeriod->PaymentAmount    = number_format($yearPayment, "2", ".", "");
				$amortizationPeriod->PrincipalApplied = number_format($yearPrincipalApplied, "2", ".", "");
				$amortizationPeriod->InterestPaid     = number_format($yearInterestPaid, "2", ".", "");
				$amortizationPeriod->TaxSavings       = number_format($yearTaxSavings, "2", ".", "");
				$amortizationPeriod->PropertyTaxes    = number_format($yearPropertyTaxes, "2", ".", "");
				$amortizationPeriod->Insurance        = number_format($yearInsurance, "2", ".", "");
				$amortizationPeriod->PMI              = number_format($yearPMI, "2", ".", "");
				$amortizationPeriod->RemainingBalance = number_format($currentBalance, "2", ".", "");

				$amortizationPeriod->PeriodPaymentAmount    = $yearPayment;
				$amortizationPeriod->PeriodPrincipalApplied = $yearPrincipalApplied;
				$amortizationPeriod->PeriodInterestPaid     = $yearInterestPaid;
				$amortizationPeriod->PeriodTaxSavings       = $yearTaxSavings;
				$amortizationPeriod->PeriodPropertyTaxes    = $yearPropertyTaxes;
				$amortizationPeriod->PeriodInsurance        = $yearInsurance;
				$amortizationPeriod->PeriodPMI              = $yearPMI;
				$amortizationPeriod->PeriodRemainingBalance = $currentBalance;
				
				$values->Schedule[$i]             = $amortizationPeriod;

				$yearPayment                    = 0;
				$yearInterestPaid               = 0;
				$yearTaxSavings                 = 0;
				$yearPrincipalApplied           = 0;
				$yearPropertyTaxes              = 0;
				$yearInsurance                  = 0;
				$yearPMI                        = 0;

			}

			if ($BreakCycle) {
				break;
			}

			if ($options->InterestOnly && $currentPeriod >= $totalPeriods) {
				break;
			}

			$i++;
			$currentPeriod++;
		}
		
		// Writing total values in resulting array.
		$values->PaymentAmount    = $runningPayment;
		$values->PrincipalApplied = $runningPrincipalApplied;
		$values->InterestPaid     = $runningInterestPaid;
		$values->TaxSavings       = $runningTaxSavings;
		$values->PropertyTaxes    = $runningPropertyTaxes;
		$values->Insurance        = $runningInsurance;
		$values->PMI              = $runningPMI;
		$values->RemainingBalance = $currentBalance;
		$values->PeriodsWithPMI   = $periodsWithPMI;
		$values->Periods          = $currentPeriod;
		
		// Writing formatted total values in resulting array.
		$values->TotalPaymentAmount    = number_format($runningPayment, "2", ".", "");
		$values->TotalPrincipalApplied = number_format($runningPrincipalApplied, "2", ".", "");
		$values->TotalInterestPaid     = number_format($runningInterestPaid, "2", ".", "");
		$values->TotalTaxSavings       = number_format($runningTaxSavings, "2", ".", "");
		$values->TotalPropertyTaxes    = number_format($runningPropertyTaxes, "2", ".", "");
		$values->TotalInsurance        = number_format($runningInsurance, "2", ".", "");
		$values->TotalPMI              = number_format($runningPMI, "2", ".", "");
		$values->TotalRemainingBalance = number_format($currentBalance, "2", ".", "");

		$values->TotalPeriodsWithPMI   = $periodsWithPMI;
		$values->TotalPeriods          = $currentPeriod;
		
		return $values;
	}
	
	public static function ConsolidateTables($table1, $table2) {
		//$table1 = new AmortizationTable();
		//$table2 = new AmortizationTable();
		
		$returnTable = new AmortizationTable();
		
		$periods = max(array(count($table1->Schedule), count($table2->Schedule)));

		$runningPayment           = 0;
		$runningInterestPaid      = 0;
		$runningTaxSavings        = 0;
		$runningPrincipalApplied  = 0;
		$runningPropertyTaxes     = 0;
		$runningInsurance         = 0;
		$runningPMI = 0;
		$runningRemainingBalance  = 0;
		$currentBalance = 0;

		$periodsWithPMI = 0;
		$currentPeriod = 1;
		$i = 0;
		
		for($i = 0; $i < $periods; $i++) {
			$period = new AmortizationPeriod();

			$period->Type   =  $table1->Schedule[$i]->Type;
			$period->Period =  $table1->Schedule[$i]->Period;
			
			if ($period->Type != 'SubTotal') {
				$currentPeriod++;
			}
				
			if ($table1->Schedule[$i]->PeriodPMI > 0 || $table1->Schedule[$i]->PeriodPMI > 0) {
				$periodsWithPMI++;
			}

			if (isset($table1->Schedule[$i])) {
				$period->PeriodPaymentAmount    =  $table1->Schedule[$i]->PeriodPaymentAmount;
				$period->PeriodPrincipalApplied =  $table1->Schedule[$i]->PeriodPrincipalApplied;
				$period->PeriodInterestPaid     =  $table1->Schedule[$i]->PeriodInterestPaid;
				$period->PeriodTaxSavings       =  $table1->Schedule[$i]->PeriodTaxSavings;
				$period->PeriodPropertyTaxes    =  $table1->Schedule[$i]->PeriodPropertyTaxes;
				$period->PeriodInsurance        =  $table1->Schedule[$i]->PeriodInsurance;
				$period->PeriodPMI              =  $table1->Schedule[$i]->PeriodPMI;
				$period->PeriodRemainingBalance =  $table1->Schedule[$i]->PeriodRemainingBalance;
			} else {
				$period->PeriodPaymentAmount    =  0;
				$period->PeriodPrincipalApplied =  0;
				$period->PeriodInterestPaid     =  0;
				$period->PeriodTaxSavings       =  0;
				$period->PeriodPropertyTaxes    =  0;
				$period->PeriodInsurance        =  0;
				$period->PeriodPMI              =  0;
				$period->PeriodRemainingBalance =  0;
			}

			if (isset($table2->Schedule[$i])) {
				$period->PeriodPaymentAmount    += $table2->Schedule[$i]->PeriodPaymentAmount;
				$period->PeriodPrincipalApplied += $table2->Schedule[$i]->PeriodPrincipalApplied;
				$period->PeriodInterestPaid     += $table2->Schedule[$i]->PeriodInterestPaid;
				$period->PeriodTaxSavings       += $table2->Schedule[$i]->PeriodTaxSavings;
				$period->PeriodPropertyTaxes    += $table2->Schedule[$i]->PeriodPropertyTaxes;
				$period->PeriodInsurance        += $table2->Schedule[$i]->PeriodInsurance;
				$period->PeriodPMI              += $table2->Schedule[$i]->PeriodPMI;
				$period->PeriodRemainingBalance += $table2->Schedule[$i]->PeriodRemainingBalance;
			}

			$period->PaymentAmount    =  number_format($period->PeriodPaymentAmount, "2", ".", "");
			$period->PrincipalApplied =  number_format($period->PeriodPrincipalApplied, "2", ".", "");
			$period->InterestPaid     =  number_format($period->PeriodInterestPaid, "2", ".", "");
			$period->TaxSavings       =  number_format($period->PeriodTaxSavings, "2", ".", "");
			$period->PropertyTaxes    =  number_format($period->PeriodPropertyTaxes, "2", ".", "");
			$period->Insurance        =  number_format($period->PeriodInsurance, "2", ".", "");
			$period->PMI              =  number_format($period->PeriodPMI, "2", ".", "");
			$period->RemainingBalance =  number_format($period->PeriodRemainingBalance, "2", ".", "");
			
			if ($period->Type != 'SubTotal') {
				$runningPayment           += $period->PeriodPaymentAmount;
				$runningPrincipalApplied  += $period->PeriodPrincipalApplied;
				$runningInterestPaid      += $period->PeriodInterestPaid;
				$runningTaxSavings        += $period->PeriodTaxSavings;
				$runningPropertyTaxes     += $period->PeriodPropertyTaxes;
				$runningInsurance         += $period->PeriodInsurance;
				$runningPMI               += $period->PeriodPMI;
			}
			//$runningRemainingBalance  += $period->PeriodRemainingBalance;
			
			$returnTable->Schedule[]  = $period;
		}
		
		$period = new AmortizationPeriod();
		
		// Writing total period in resulting array.
		$period->PaymentAmount    = $runningPayment;
		$period->PrincipalApplied = $runningPrincipalApplied;
		$period->InterestPaid     = $runningInterestPaid;
		$period->TaxSavings       = $runningTaxSavings;
		$period->PropertyTaxes    = $runningPropertyTaxes;
		$period->Insurance        = $runningInsurance;
		$period->PMI              = $runningPMI;
		$period->RemainingBalance = $currentBalance;
		$period->PeriodsWithPMI   = $periodsWithPMI;
		$period->Periods          = $currentPeriod;
		
		// Writing formatted total period in resulting array.
		$returnTable->TotalPaymentAmount    = number_format($runningPayment, "2", ".", "");
		$returnTable->TotalPrincipalApplied = number_format($runningPrincipalApplied, "2", ".", "");
		$returnTable->TotalInterestPaid     = number_format($runningInterestPaid, "2", ".", "");
		$returnTable->TotalTaxSavings       = number_format($runningTaxSavings, "2", ".", "");
		$returnTable->TotalPropertyTaxes    = number_format($runningPropertyTaxes, "2", ".", "");
		$returnTable->TotalInsurance        = number_format($runningInsurance, "2", ".", "");
		$returnTable->TotalPMI              = number_format($runningPMI, "2", ".", "");
		$returnTable->TotalRemainingBalance = number_format($currentBalance, "2", ".", "");

		$period->TotalPeriodsWithPMI   = $periodsWithPMI;
		$period->TotalPeriods          = $currentPeriod;
		
		return $returnTable;
	}

	public static function ActualAPR($amount, $closingCost, $interest, $length, $periodPayment) {
		if ($amount <= 0) {
			return 0;
		}
			
		if ($closingCost == $amount) {
			return 0;
		}
			
		if ($closingCost == 0) {
			return $interest;
		}
			
		$totalAmount = $amount - $closingCost;
		$aprA = $interest;
		$aprB = $interest;
		$aprX = 0;
		
		do {
			$aprA = $aprA / 2;
			$payment = Calc::PeriodPayment($totalAmount, $aprA, $length);
		} while($payment > $periodPayment);

		do {
			$aprB = $aprB * 2;
			$payment = Calc::PeriodPayment($totalAmount, $aprB, $length);
		} while($payment < $periodPayment);

		do {
			$aprX = ($aprA + $aprB) / 2;
			
			$payment = Calc::PeriodPayment($totalAmount, $aprX, $length);
			
			if ($payment > $periodPayment) {
				$aprB = $aprX;
			} else {
				$aprA = $aprX;
			}
		} while(abs($payment - $periodPayment) > 0.001);
		
		return $aprX;
	}

}