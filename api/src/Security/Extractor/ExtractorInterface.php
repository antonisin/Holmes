<?php

namespace App\Security\Extractor;

use App\Entity\User;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use App\ThrowException\ModelException;

/**
 * Extractor interface.
 * This interface is used to describe and define social network extractors. The extractors are used to get info from
 * social network object and setup them into User entity/class.
 *
 * @author Maxim Antonisin <maxim.antonisin@gmail.com>
 *
 * @version 1.0.0
 */
interface ExtractorInterface
{
    /**
     * Get User entity instance from social network object.
     * This method is used to extract information from social network class and setup it into User class/entity
     * instance.
     *
     * @param ResourceOwnerInterface $authUser - Social Network object class.
     *
     * @return User
     *
     * @throws ModelException - On model validation/setup error. For example if role do not exist.
     */
    public static function getUser(ResourceOwnerInterface $authUser): User;

}