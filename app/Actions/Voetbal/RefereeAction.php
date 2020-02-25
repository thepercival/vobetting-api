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
use Voetbal\Referee as RefereeBase;
use Voetbal\Referee\Repository as RefereeRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\DeserializationContext;
use Voetbal\Sport\Repository as SportRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use Voetbal\Referee;
use Voetbal\Competition;

final class RefereeAction extends Action
{
    /**
     * @var RefereeRepository
     */
    protected $refereeRepos;
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
        RefereeRepository $refereeRepos,
        SportRepository $sportRepos,
        CompetitionRepos $competitionRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->refereeRepos = $refereeRepos;
        $this->sportRepos = $sportRepos;
        $this->competitionRepos = $competitionRepos;
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            $serGroups = ['Default','privacy'];
            $deserializationContext = DeserializationContext::create();
            $deserializationContext->setGroups($serGroups);

            /** @var \Voetbal\Referee $referee */
            $referee = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Referee', 'json', $deserializationContext);
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $refereesWithSameInitials = $competition->getReferees()->filter( function( $refereeIt ) use ( $referee ) {
                return $refereeIt->getInitials() === $referee->getInitials();
            });
            if( !$refereesWithSameInitials->isEmpty() ) {
                throw new \Exception("de scheidsrechter met de initialen ".$referee->getInitials()." bestaat al", E_ERROR );
            }

            $newReferee = new RefereeBase( $competition, $referee->getRank() );
            $newReferee->setInitials($referee->getInitials());
            $newReferee->setName($referee->getName());
            $newReferee->setEmailaddress($referee->getEmailaddress());
            $newReferee->setInfo($referee->getInfo());

            $this->refereeRepos->save( $newReferee );

            $serializationContext = SerializationContext::create();
            $serializationContext->setGroups($serGroups);

            $json = $this->serializer->serialize( $newReferee, 'json', $serializationContext);
            return $this->respondWithJson($response, $json);
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit($request, $response, $args)
    {
        try {
            $serGroups = ['Default','privacy'];
            $deserializationContext = DeserializationContext::create();
            $deserializationContext->setGroups($serGroups);

            /** @var \Voetbal\Referee $refereeSer */
            $refereeSer = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Referee', 'json', $deserializationContext);
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $referee = $this->getRefereeFromInput((int)$args["refereeId"], $competition);

            $refereesWithSameInitials = $competition->getReferees()->filter( function( $refereeIt ) use ( $refereeSer, $referee ) {
                return $refereeIt->getInitials() === $refereeSer->getInitials() && $referee !== $refereeIt;
            });
            if( !$refereesWithSameInitials->isEmpty() ) {
                throw new \Exception("de scheidsrechter met de initialen ".$refereeSer->getInitials()." bestaat al", E_ERROR );
            }

            $referee->setRank( $refereeSer->getRank() );
            $referee->setInitials( $refereeSer->getInitials() );
            $referee->setName( $refereeSer->getName() );
            $referee->setEmailaddress($refereeSer->getEmailaddress());
            $referee->setInfo( $refereeSer->getInfo() );

            $this->refereeRepos->save( $referee );

            $serializationContext = SerializationContext::create();
            $serializationContext->setGroups($serGroups);

            $json = $this->serializer->serialize( $referee, 'json', $serializationContext);
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

            $referee = $this->getRefereeFromInput((int)$args["refereeId"], $competition);

            $competition->getReferees()->removeElement($referee);
            $this->refereeRepos->remove($referee);

            $rank = 1;
            foreach ($competition->getReferees() as $referee) {
                $referee->setRank($rank++);
            }
            $this->refereeRepos->save($referee);

            return $response->withStatus(200);
        }
        catch( \Exception $e ){
            return new ErrorResponse( $e->getMessage(), 422);
        }
    }

    protected function getRefereeFromInput(int $id, Competition $competition ): Referee
    {
        $referee = $this->refereeRepos->find($id);
        if ($referee === null) {
            throw new \Exception("de scheidsrechter kon niet gevonden worden o.b.v. de invoer", E_ERROR);
        }
        if ($referee->getCompetition() !== $competition) {
            throw new \Exception("de competitie van de scheidsrechter komt niet overeen met de verstuurde competitie", E_ERROR);
        }
        return $referee;
    }
}