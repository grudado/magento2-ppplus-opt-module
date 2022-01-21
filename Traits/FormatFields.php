<?php

namespace Paypal\PaypalPlusBrasil\Traits;

trait FormatFields
{
    public function onlyNumbers($string)
    {
        return preg_replace("/[^0-9]/", "", $string);
    }

    public function convertFloat($number)
    {
        return number_format($number, 2, '.', '');
    }
}
