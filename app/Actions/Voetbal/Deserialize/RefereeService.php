<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 19-2-19
 * Time: 16:00
 */

namespace App\Actions\Voetbal\Deserialize;

use Voetbal\Round\Number as RoundNumber;
use Voetbal\Place;

class RefereeService
{
    /**
     * @var array
     */
    protected $roundNumberPlaces;

    public function getPlace( RoundNumber $roundNumber, int $placeId): ?Place {
        if( $this->roundNumberPlaces === null ) {
            $this->roundNumberPlaces = $this->getPlaces( $roundNumber );
        }
        if( array_key_exists($placeId, $this->roundNumberPlaces) === false ) {
            return null;
        }
        return $this->roundNumberPlaces[$placeId];
    }

    protected function getPlaces( RoundNumber $roundNumber): array {
        $roundNumberPlaces = [];
        foreach( $roundNumber->getPoules() as $poule ) {
            foreach( $poule->getPlaces() as $place ) {
                $roundNumberPlaces[$place->getId()] = $place;
           }
        }
        return $roundNumberPlaces;
    }
}

