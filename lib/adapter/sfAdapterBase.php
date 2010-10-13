<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class sfAdapterBase {
    public abstract function run($data);

    protected function to_string($value) {
        return (string)$value;
    }

    protected function to_varchar($value) {
        return (string)$value;
    }

    protected function to_boolean($value) {
        return $value === 1 ? TRUE : FALSE;
    }

    protected function to_integer($value) {
        return intval($value);
    }

    protected function to_decimal($value) {
        return floatval($value);
    }

    protected function to_float($value) {
        return floatval($value);
    }

    protected function to_timestamp($value) {
        return $this->createDateTimeObject($value);
    }

    protected function to_time($value) {
        return $this->createDateTimeObject($value);
    }

    protected function to_date($value) {
        return $this->createDateTimeObject($value);
    }


    protected function createDateTimeObject($value) {
        if ($value != '') {
            return date_create($value);
        }
        return null;
    }
}
?>