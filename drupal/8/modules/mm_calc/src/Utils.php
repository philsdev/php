<?php

namespace Drupal\mm_calc;

class Utils {

  public static function getDefaultValues() {
    return \Drupal::config('mm_calc.settings')->get('calculators');
  }

  public static function getValue($input, $defaults, $key) {
    return isset($input[$key]) ? $input[$key] : $defaults[$key];
  }

  public static function getCurrencyOutput($amount) {
  	return '$' . number_format($amount, 2);
  }

  public static function getPercentOutput($amount) {
  	return number_format($amount * 100, 2) . '%';
  }
  
  public static function getDump($input) {
    $input_dump = print_r($input, true);

    return "<pre>{$input_dump}</pre>";
  }

}