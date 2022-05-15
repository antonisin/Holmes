<?php

namespace App\Security\Extractor;

use App\Entity\User;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use \J4k\OAuth2\Client\Provider\User as BaseProviderUser;

/**
 * Vkontakte extractor.
 * This class is implemented to extract information from VK social network object and setup in User entity/class
 * instance.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
abstract class VKExtractor implements ExtractorInterface
{
    /**
     * {@inheritDoc}
     *
     * @param BaseProviderUser|ResourceOwnerInterface $authUser - Social network object to extract.
     */
    public static function getUser(BaseProviderUser | ResourceOwnerInterface $authUser): User
    {
        $model = new User();

        $model
            ->setEmail($authUser->getEmail())
            ->setFirstName($authUser->getFirstName())
            ->setLastName($authUser->getLastName())
            ->setRefId($authUser->getId())
            ->setPicture($authUser->getPhotoMax())
            ->addRole(User::ROLE_VK)
        ;

        return $model;
    }
}