<?php

class XssHelper {
    /**
     * use jevix class to prevent value attack
     *
     * @param string $value
     *
     * @return null|string
     */
    public static function parse($value)
    {
        $jevix = new Jevix();

        $errors = null;
        $value = $jevix->parse($value, $errors);

        if (!$value && $value !== 0 && $value !== '0') {
            return null;
        } else {
            return $value;
        }
    }
}
