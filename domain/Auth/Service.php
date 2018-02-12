<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 21:20
 */

namespace VOBetting\Auth;

use VOBetting\User;
use VOBetting\User\Repository as UserRepository;

class Service
{
    /**
     * @var UserRepository
     */
    protected $repos;

    /**
     * Service constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct( UserRepository $userRepository )
    {
        $this->repos = $userRepository;
    }

    /**
     * @param string $emailaddress
     * @param string $password
     * @param string $name
     *
     * @throws \Exception
     */
    public function register( $emailaddress, $password, $name = null )
    {
        if ( strlen( $password ) < User::MIN_LENGTH_PASSWORD or strlen( $password ) > User::MAX_LENGTH_PASSWORD ){
            throw new \InvalidArgumentException( "het wachtwoord moet minimaal ".User::MIN_LENGTH_PASSWORD." karakters bevatten en mag maximaal ".User::MAX_LENGTH_PASSWORD." karakters bevatten", E_ERROR );
        }
        if ( $emailaddress !== "coendunnink@gmail.com" ) {
            throw new \Exception("at this moment this site is in beta and can not register users",E_ERROR);
        }
        $userTmp = $this->repos->findOneBy( array('emailaddress' => $emailaddress ) );
        if ( $userTmp ) {
            throw new \Exception("het emailadres is al in gebruik",E_ERROR);
        }
        if ( $name !== null ) {
            $userTmp = $this->repos->findOneBy( array('name' => $name ) );
            if ( $userTmp ) {
                throw new \Exception("de gebruikersnaam is al in gebruik",E_ERROR);
            }
        }
        $user = new User($emailaddress);
        $user->setSalt( bin2hex(random_bytes(15) ) );
        $user->setPassword( password_hash( $user->getSalt() . $password, PASSWORD_DEFAULT) );
        $savedUser = $this->repos->save($user);
        $this->sendRegisterEmail( $emailaddress );
        return $savedUser;
    }

    protected function sendRegisterEmail( $emailAddress )
    {
        $subject = 'welkom bij VOBetting';
        $body = '
        <p>Hallo,</p>
        <p>            
        Welkom bij VOBetting! Wij wensen je veel plezier met het gebruik van de VOBetting.
        </p>
        <p>
        Mocht je vragen/opmerkingen/klachten/suggesties/etc hebben ga dan naar <a href="https://github.com/thepercival/VOBetting/issues">https://github.com/thepercival/VOBetting/issues</a>
        </p>        
        <p>
        met vriendelijke groet,
        <br>
        VOBetting
        </p>';

        $from = "VOBetting";
        $fromEmail = "noreply@VOBetting.nl";
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: ".$from." <" . $fromEmail . ">" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $params = "-r ".$fromEmail;

        if ( !mail( $emailAddress, $subject, $body, $headers, $params) ) {
            // $app->flash("error", "We're having trouble with our mail servers at the moment.  Please try again later, or contact us directly by phone.");
            error_log('Mailer Error!' );
            // $app->halt(500);
        }
    }

    public function sendPasswordCode( $emailAddress ) {
        $user = $this->repos->findOneBy( array( 'emailaddress' => $emailAddress ) );
        if (!$user) {
            throw new \Exception( "kan geen code versturen");
        }
        $user->resetForgetpassword();
        $this->repos->save( $user );
        $this->mailPasswordCode( $user );
        return true;
    }

    protected function mailPasswordCode( User $user )
    {
        $subject = 'wachtwoord herstellen';
        $body = '
        <p>Hallo,</p>
        <p>            
        Met deze code kun je je wachtwoord herstellen: ' . $user->getForgetpasswordToken() . '.
        </p>
        <p>            
        Let op : je kunt deze code gebruiken tot en met ' . $user->getForgetpasswordDeadline()->modify("-1 days")->format("Y-m-d") . '.
        </p>
        <p>
        met vriendelijke groet,
        <br>
        VOBetting
        </p>';

        $from = "VOBetting";
        $fromEmail = "noreply@VOBetting.nl";
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: ".$from." <" . $fromEmail . ">" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $params = "-r ".$fromEmail;

        if ( !mail( $user->getEmailaddress(), $subject, $body, $headers, $params) ) {
            // $app->flash("error", "We're having trouble with our mail servers at the moment.  Please try again later, or contact us directly by phone.");
            error_log('Mailer Error!' );
            // $app->halt(500);
        }
    }

    public function changePassword( $emailAddress, $password, $code )
    {
        $user = $this->repos->findOneBy( array( 'emailaddress' => $emailAddress ) );
        if (!$user) {
            throw new \Exception( "het wachtwoord kan niet gewijzigd worden");
        }
        // check code and deadline
        if ($user->getForgetpasswordToken() !== $code ) {
            throw new \Exception( "het wachtwoord kan niet gewijzigd worden, je hebt een onjuiste code gebruikt");
        }
        $now = new \DateTimeImmutable();
        if ( $now > $user->getForgetpasswordDeadline() ) {
            throw new \Exception( "het wachtwoord kan niet gewijzigd worden, de wijzigingstermijn is voorbij");
        }

        // set password
        $user->setPassword( password_hash( $user->getSalt() . $password, PASSWORD_DEFAULT) );
        $user->setForgetpassword( null );
        return $this->repos->save($user);
    }
}