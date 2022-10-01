<?php

namespace App\Lib;

/**
 * Application helper class.
 * This class is implemented as abstract class with static method. Helper and methods from helper are used in
 * application for different reasons and in different cases.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
abstract class Helper
{
    /**
     * Return string without newlines and spaces more than one.
     * Also, this method remove some special characters from the string.
     *
     * @param string $string - String to be normalized to default format.
     *
     * @return string
     */
    public static function normalizeString(string $string): string
    {
        $string = preg_replace("/(\040{2,})/", " ", $string);
        $string = preg_replace("/(\040{2,})/", "", $string);
        $string = preg_replace("/(\n)(\040{1,})/", "$1", $string);
        $string = preg_replace("/(\t)/", "", $string);
        $string = preg_replace("/(\n)/", "", $string);
        $string = preg_replace("/[\040\t,]{1,}$/", "", $string);
        $string = strip_tags($string);
        $string = trim($string);
        $string = preg_replace("/(\r{2,})/", " ", $string);

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $string = preg_replace('/^(")?(.*)(")$/', '$2', $string);

        return $string;
    }
}