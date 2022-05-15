<?php

namespace App\Security\Extractor;

use Aego\OAuth2\Client\Provider\OdnoklassnikiResourceOwner;
use App\Entity\User;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Odnoklassniki extractor.
 * This class is implemented to extract information from OK social network object and setup in User entity/class
 * instance.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
abstract class OKExtractor implements ExtractorInterface
{
    /**
     * {@inheritDoc}
     *
     * @param OdnoklassnikiResourceOwner|ResourceOwnerInterface $authUser - Social network object to extract.
     */
    public static function getUser(OdnoklassnikiResourceOwner | ResourceOwnerInterface $authUser): User
    {
        $model = new User();

        $model
            ->setFirstName($authUser->getFirstName())
            ->setLastName($authUser->getLastName())
            ->setRefId($authUser->getId())
            ->setPicture($authUser->getImageUrl())
            ->addRole(User::ROLE_OK)
        ;

        return $model;
    }
}