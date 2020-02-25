<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 1-10-17
 * Time: 12:14
 */

namespace FCToernooi\Role;

use FCToernooi\Tournament;
use FCToernooi\Role;
use FCToernooi\User;

class Service
{
    /**
     * Service constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param Tournament $tournament
     * @param User $user
     * @param int $roleValues
     * @throws \Exception
     */
    public function create( Tournament $tournament, User $user, int $roleValues )
    {
        // get roles
        // $rolesRet = new ArrayCollection();

        //try {

            // flush roles
            // $this->flushRoles( $tournament, $user );

            // save roles
            for($roleValue = 1 ; $roleValue < Role::ALL ; $roleValue *= 2 ){
                if ( ( $roleValue & $roleValues ) !== $roleValue ){
                    continue;
                }
                $role = new Role( $tournament, $user );
                $role->setValue( $roleValue );
                // $this->repos->save($role);
                // $rolesRet->add($role);
            }
//        }
//        catch( \Exception $e ){
//            throw new \Exception(urlencode($e->getMessage()), E_ERROR );
//        }
//
//        return $rolesRet;
    }
}