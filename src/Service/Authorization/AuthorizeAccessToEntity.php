<?php

namespace Deozza\PhilarmonyCoreBundle\Service\Authorization;

class AuthorizeAccessToEntity
{
    public function authorize($user, $by, $entity)
    {
        if(is_string($by))
        {
            return true;
        }
        
        if(empty($user))
        {
            return false;
        }
        
        if(array_key_exists('roles', $by))
        {
            foreach($by['roles'] as $role)
            {
                if(in_array($role, $user->getRoles())) return true;
            }
        }

        if(array_key_exists("users", $by))
        {
            foreach($by['users'] as $userKind)
            {
                $userPath = explode('.', $userKind);
                if($userPath[0] === "owner")
                {
                    if($entity->getOwner()->getId() === $user->getId())
                    {
                        return true;
                    }
                }
                else
                {
                    $properties = $entity->getProperties();
                    for($i = 0; $i < count($userPath); $i++)
                    {
                        $properties = $properties[$userPath[$i]];
                    }

                    if($user->getUuid() === $properties || in_array($user->getUuid(), $properties))
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}