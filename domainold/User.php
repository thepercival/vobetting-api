<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-1-17
 * Time: 20:44
 */

namespace FCToernooi;

use Voetbal\Referee;

class User
{
	/**
	 * @var int
	 */
	private $id;

    /**
     * @var string
     */
    private $emailaddress;

	/**
	 * @var string
	 */
	private $password;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $forgetpassword;

    /**
     * @var boolean
     */
    private $helpSent;

	const MIN_LENGTH_EMAIL = Referee::MIN_LENGTH_EMAIL;
    const MAX_LENGTH_EMAIL = Referee::MAX_LENGTH_EMAIL;
    const MIN_LENGTH_PASSWORD = 3;
    const MAX_LENGTH_PASSWORD = 50;
    const MIN_LENGTH_NAME = 3;
    const MAX_LENGTH_NAME = 15;
    const MAX_LENGTH_HASH = 256;

	public function __construct( $emailaddress )
	{
	    $this->helpSent = false;
        $this->setEmailaddress( $emailaddress );
	}

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

    /**
     * @param int $id
     */
    public function setId( $id )
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getEmailaddress()
    {
        return $this->emailaddress;
    }

    /**
     * @param string $emailaddress
     */
    public function setEmailaddress( $emailaddress )
    {
        if ( strlen( $emailaddress ) < static::MIN_LENGTH_EMAIL or strlen( $emailaddress ) > static::MAX_LENGTH_EMAIL ){
            throw new \InvalidArgumentException( "het emailadres moet minimaal ".static::MIN_LENGTH_EMAIL." karakters bevatten en mag maximaal ".static::MAX_LENGTH_EMAIL." karakters bevatten", E_ERROR );
        }

        if (!filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException( "het emailadres ".$emailaddress." is niet valide", E_ERROR );
        }
        $this->emailaddress = $emailaddress;
    }

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword( $password )
	{
        if ( strlen( $password ) === 0  ){
            throw new \InvalidArgumentException( "de wachtwoord-hash mag niet leeg zijn", E_ERROR );
        }
		$this->password = $password;
	}

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     */
    public function setSalt( $salt )
    {
        $this->salt = $salt;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName( $name )
    {
        if ( strlen( $name ) < static::MIN_LENGTH_NAME or strlen( $name ) > static::MAX_LENGTH_NAME ){
            throw new \InvalidArgumentException( "de naam moet minimaal ".static::MIN_LENGTH_NAME." karakters bevatten en mag maximaal ".static::MAX_LENGTH_NAME." karakters bevatten", E_ERROR );
        }

        if( !ctype_alnum($name)){
            throw new \InvalidArgumentException( "de naam mag alleen cijfers en letters bevatten", E_ERROR );
        }
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getForgetpassword()
    {
        return $this->forgetpassword;
    }

    /**
     * @param string $forgetpassword
     */
    public function setForgetpassword( $forgetpassword )
    {
        $this->forgetpassword = $forgetpassword;
    }

    /**
     * return string
     */
    public function resetForgetpassword()
    {
        $forgetpassword = rand ( 100000, 999999 );
        $tomorrow = date("Y-m-d", strtotime('tomorrow'));
        $tomorrow = new \DateTimeImmutable($tomorrow);
        $tomorrow = $tomorrow->modify("+1 days");
        $this->setForgetpassword( $forgetpassword . ":" . $tomorrow->format("Y-m-d") );
    }

    /**
     * first 6 characters
     *
     * @return string
     */
    public function getForgetpasswordToken()
    {
        $forgetpassword = $this->getForgetpassword();
        if( strlen( $forgetpassword ) === 0 ) {
            return "";
        }
        $arrForgetPassword = explode(":", $forgetpassword);
        return $arrForgetPassword[0];
    }

    /**
     * last 10 characters
     *
     * @return \DateTimeImmutable|null
     * @throws \Exception
     */
    public function getForgetpasswordDeadline()
    {
        $forgetpassword = $this->getForgetpassword();
        if( strlen( $forgetpassword ) === 0 ) {
            return null;
        }
        $arrForgetPassword = explode(":", $forgetpassword);
        return new \DateTimeImmutable( $arrForgetPassword[1] );
    }

    /**
     * @return boolean
     */
    public function getHelpSent()
    {
        return $this->helpSent;
    }

    /**
     * @param boolean $helpSent
     */
    public function setHelpSent( $helpSent )
    {
        $this->helpSent = $helpSent;
    }
}