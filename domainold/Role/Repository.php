<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 1-10-17
 * Time: 12:10
 */

namespace FCToernooi\Role;

use FCToernooi\Role;
use FCToernooi\Tournament;
use FCToernooi\User;
use FCToernooi\User\Repository as UserRepository;

/**
 * Class Repository
 * @package FCToernooi\Role
 */
class Repository extends \Voetbal\Repository
{
    public function syncRefereeRoles( Tournament $tournament ): array
    {
        $conn = $this->_em->getConnection();
        $conn->beginTransaction();
        try {
            // remove referee roles
            {
                $params = ['value' => Role::REFEREE, 'tournament' => $tournament];
                $refereeRoles = $this->findBy( $params );
                foreach( $refereeRoles as $refereeRole ) {
                    $this->_em->remove( $refereeRole );
                }
            }
            $this->_em->flush();

            // add referee roles
            $userRepos = new UserRepository($this->_em, $this->_em->getClassMetaData(User::class));
            $referees = $tournament->getCompetition()->getReferees();
            foreach( $referees as $referee ) {
                if( strlen( $referee->getEmailaddress() ) === 0 ) {
                    continue;
                }
                /** @var \FCToernooi\User|null $user */
                $user = $userRepos->findOneBy( ['emailaddress' => $referee->getEmailaddress()] );
                if( $user === null ) {
                    continue;
                }
                $refereeRole = new Role( $tournament, $user);
                $refereeRole->setValue(Role::REFEREE);
                $this->_em->persist( $refereeRole );
            }
            $rolesRet = $tournament->getRoles()->toArray();

            $this->_em->flush();
            $conn->commit();
            return $rolesRet;
        } catch (\Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}