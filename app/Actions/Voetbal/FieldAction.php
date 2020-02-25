<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-11-17
 * Time: 14:02
 */

namespace App\Actions\Voetbal;

use App\Response\ErrorResponse;
use App\Response\ForbiddenResponse as ForbiddenResponse;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\Competition\Repository as CompetitionRepos;
use Voetbal\Field as FieldBase;
use Voetbal\Field\Repository as FieldRepository;
use Voetbal\Referee as RefereeBase;
use Voetbal\Sport\Repository as SportRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use Voetbal\Field;
use Voetbal\Competition;

final class FieldAction extends Action
{
    /**
     * @var FieldRepository
     */
    protected $fieldRepos;
    /**
     * @var SportRepository
     */
    protected $sportRepos;
    /**
     * @var CompetitionRepos
     */
    protected $competitionRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        FieldRepository $fieldRepos,
        SportRepository $sportRepos,
        CompetitionRepos $competitionRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->fieldRepos = $fieldRepos;
        $this->sportRepos = $sportRepos;
        $this->competitionRepos = $competitionRepos;
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Field $field */
            $field = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Field', 'json');
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $fieldsWithSameName = $competition->getFields()->filter( function( $fieldIt ) use ( $field ) {
                return $fieldIt->getName() === $field->getName() || $fieldIt->getNumber() === $field->getNumber();
            });
            if( !$fieldsWithSameName->isEmpty() ) {
                throw new \Exception("het veldnummer \"".$field->getNumber()."\" of de veldnaam \"".$field->getName()."\" bestaat al", E_ERROR );
            }
            $sport = $this->sportRepos->find($field->getSportIdSer());
            if ( $sport === null ) {
                throw new \Exception("de sport kan niet gevonden worden", E_ERROR);
            }

            $newField = new FieldBase( $competition, $field->getNumber() );
            $newField->setName( $field->getName() );
            $newField->setSport( $sport );

            $this->fieldRepos->save( $newField );

            $json = $this->serializer->serialize( $newField, 'json');
            return $this->respondWithJson($response, $json);
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit($request, $response, $args)
    {
        try {
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $field = $this->getFieldFromInput((int)$args["fieldId"], $competition);
            /** @var \Voetbal\Field|false $fieldSer */
            $fieldSer = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Field', 'json');
            if ($fieldSer === false) {
                throw new \Exception("het veld kon niet gevonden worden o.b.v. de invoer", E_ERROR);
            }

            $fieldsWithSameName = $competition->getFields()->filter( function( $fieldIt ) use ( $fieldSer, $field ) {
                return $fieldIt->getName() === $fieldSer->getName() && $field !== $fieldIt;
            });
            if( !$fieldsWithSameName->isEmpty() ) {
                throw new \Exception("het veld \"".$fieldSer->getName()."\" bestaat al", E_ERROR );
            }

            $sport = $this->sportRepos->find($fieldSer->getSportIdSer());
            if ( $sport === null ) {
                throw new \Exception("de sport kan niet gevonden worden", E_ERROR);
            }
            $field->setName( $fieldSer->getName() );
            $field->setSport( $sport );

            $this->fieldRepos->save( $field );

            $json = $this->serializer->serialize( $field, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function remove( Request $request, Response $response, $args ): Response
    {
        try{
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $field = $this->getFieldFromInput((int)$args["fieldId"], $competition);

            $competition->getFields()->removeElement($field);
            $this->fieldRepos->remove($field);

            $fieldNumber = 1;
            foreach ($competition->getFields() as $field) {
                $field->setNumber($fieldNumber++);
            }
            $this->fieldRepos->save($field);

            return $response->withStatus(200);
        }
        catch( \Exception $e ){
            return new ErrorResponse( $e->getMessage(), 422);
        }
    }

    protected function getFieldFromInput(int $id, Competition $competition ): Field
    {
        $field = $this->fieldRepos->find($id);
        if ($field === null) {
            throw new \Exception("het veld kon niet gevonden worden o.b.v. de invoer", E_ERROR);
        }
        if ($field->getCompetition() !== $competition) {
            throw new \Exception("de competitie van het veld komt niet overeen met de verstuurde competitie", E_ERROR);
        }
        return $field;
    }
}