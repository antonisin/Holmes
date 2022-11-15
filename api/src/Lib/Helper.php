<?php

namespace App\Lib;

/**
 * Application helper class.
 * This class is implemented as abstract class with static method. Helper and methods from helper are used in
 * application for different reasons and in different cases.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
abstract class Helper
{
    /**
     * Return string without newlines and spaces more than one.
     * Also, this method remove some special characters from the string.
     *
     * @param string $string - String to be normalized to default format.
     *
     * @return string - Normalized string.
     */
    public static function normalizeString(string $string): string
    {
        $string = preg_replace("/(\040{2,})/", " ", $string);
        $string = preg_replace("/(\040{2,})/", "", $string);
        $string = preg_replace("/(\n)(\040+)/", "$1", $string);
        $string = preg_replace("/(\t)/", "", $string);
        $string = preg_replace("/(\n)/", "", $string);
        $string = preg_replace("/[\040\t,]+$/", "", $string);
        $string = strip_tags($string);
        $string = trim($string);
        $string = preg_replace("/(\r{2,})/", " ", $string);

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $string = preg_replace('/^(")?(.*)(")$/', '$2', $string);

        return $string;
    }

    /**
     * Normalize and return phone number.
     * This method is used to extract and escape all characters from phone number except digits.
     *
     * @param mixed $phone - Phone number to be normalized.
     *
     * @return int - Normalized phone number.
     */
    public static function normalizePhone(mixed $phone): int
    {
        return (int) preg_replace('/\D+/', '', $phone);
    }

    /**
     * Return link to rand avatar.
     * This method is used to return link to random avatar from avatars collection.
     *
     * @return string - Link to random avatar.
     *
     * @throws \Exception
     */
    public static function randAvatar(): string
    {
        $avatar = 'https://avatars.dicebear.com/api/adventurer-neutral/%s.svg?size=200';

        return sprintf($avatar, bin2hex(random_bytes(18)));
    }

    /**
     * Return number of passed hours from argument date.
     * This method is used to return number of passed hours from argument date.
     *
     * @param \DateTime $dateTime - Date time to calculate passed hours.
     *
     * @return int
     */
    public static function passHours(\DateTime $dateTime): int
    {
        $now = new \DateTime();
        $diff = $now->diff($dateTime);

        return $diff->y * 365 * 24 + $diff->m * 30 * 24 + $diff->d * 24 + $diff->h;
    }
}
