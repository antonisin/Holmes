<?php

namespace App\Security\Extractor;

use App\Entity\User;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Facebook extractor.
 * This class is implemented to extract information from facebook social network object and setup in User entity/class
 * instance.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
abstract class FacebookExtractor implements ExtractorInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FacebookUser|ResourceOwnerInterface $authUser - Social network object to extract.
     */
    public static function getUser(FacebookUser | ResourceOwnerInterface $authUser): User
    {
        $model = new User();

        $model
            ->setEmail($authUser->getEmail())
            ->setFirstName($authUser->getFirstName())
            ->setLastName($authUser->getLastName())
            ->setRefId($authUser->getId())
            ->setPicture($authUser->getPictureUrl())
            ->addRole(User::ROLE_FACEBOOK)
        ;

        return $model;
    }
}