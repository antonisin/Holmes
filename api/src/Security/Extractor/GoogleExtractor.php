<?php

namespace App\Security\Extractor;

use App\Entity\User;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Google extractor.
 * This class is implemented to extract information from Google social network object and setup in User entity/class
 * instance.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.1.0
 */
abstract class GoogleExtractor implements ExtractorInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GoogleUser|ResourceOwnerInterface $authUser - Social network object to extract.
     */
    public static function getUser(GoogleUser | ResourceOwnerInterface $authUser): User
    {
        $model = new User();

        $model
            ->setFirstName($authUser->getFirstName())
            ->setLastName($authUser->getLastName())
            ->setRefId($authUser->getId())
            ->setPicture($authUser->getAvatar())
            ->addRole(User::ROLE_GOOGLE)

            ->getNotification()
            ->setEmail($authUser->getEmail())
            ->setEmailVerified(true)
        ;

        return $model;
    }
}