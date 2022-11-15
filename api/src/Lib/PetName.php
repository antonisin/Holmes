<?php

namespace App\Lib;

/**
 * Pet Name Generator class.
 * This class is implemented to generate pet name with additional params as advert or adjective.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
class PetName implements PetNameInterface
{
    /**
     * Return rand advert.
     * Method returns a random adverb from a list of pet names adverbs.
     *
     * @return string
     */
    public static function randAdvert(): string
    {
        return self::ADVERBS[mt_rand(0, count(self::ADVERBS) - 1)];
    }

    /**
     * Return rand adjective.
     * Method returns a random adjective from a list of pet names adjectives.
     *
     * @return string
     */
    public static function randAdjective(): string
    {
        return self::ADJECTIVES[mt_rand(0, count(self::ADJECTIVES) - 1)];
    }

    /**
     * Name returns a random name from a list of pet names.
     *
     * @return string
     */
    public static function randName(): string
    {
        return self::NAMES[mt_rand(0, count(self::NAMES) - 1)];
    }

    /**
     * Generate array of pet names with additional params.
     * This method is used to generate array of pet names with additional params as advert or adjective.
     *
     * @return array - Array of pet names.
     */
    public static function rand(): array
    {
        return [
            self::randAdvert(),
            self::randAdjective(),
            self::randName(),
        ];
    }
}