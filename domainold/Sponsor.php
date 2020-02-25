<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-5-18
 * Time: 12:14
 */

namespace FCToernooi;

class Sponsor
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * @var int
     */
    private $screenNr;

    /**
     * @var Tournament
     */
    private $tournament;

    const MIN_LENGTH_NAME = 2;
    const MAX_LENGTH_NAME = 30;
    const MAX_LENGTH_URL = 100;

    public function __construct( Tournament $tournament, $name )
    {
        $this->tournament = $tournament;
        $this->setName( $name );
    }

    /**
     * @return Tournament
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * @param Tournament $tournament
     */
    public function setTournament( Tournament $tournament )
    {
        $this->tournament = $tournament;
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
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl( $url )
    {
        if ( strlen( $url ) > 0 ) {
            if (strlen($url) > static::MAX_LENGTH_URL) {
                throw new \InvalidArgumentException("de url mag maximaal " . static::MAX_LENGTH_URL . " karakters bevatten",
                    E_ERROR);
            }
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException("de url " . $url . " is niet valide (begin met https://)", E_ERROR);
            }
        }
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @param string $url
     */
    public function setLogoUrl( $url )
    {
        if ( strlen( $url ) > 0 ) {
            if (strlen($url) > static::MAX_LENGTH_URL) {
                throw new \InvalidArgumentException("de url mag maximaal " . static::MAX_LENGTH_URL . " karakters bevatten",
                    E_ERROR);
            }
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException("de url " . $url . " is niet valide (begin met https://)", E_ERROR);
            }
        }
        $this->logoUrl = $url;
    }

    /**
     * @return int
     */
    public function getScreenNr()
    {
        return $this->screenNr;
    }

    /**
     * @param int $screenNr
     */
    public function setScreenNr( $screenNr )
    {
        $this->screenNr = $screenNr;
    }
}
