<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-11-17
 * Time: 14:02
 */

namespace App\Actions\Voetbal;

use App\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\Competitor\Repository as CompetitorRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use Voetbal\Competitor;
use Voetbal\Association;

final class CompetitorAction extends Action
{
    /**
     * @var CompetitorRepository
     */
    protected $competitorRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        CompetitorRepository $competitorRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->competitorRepos = $competitorRepos;
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var Competitor $competitor */
            $competitor = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Competitor', 'json');
            /** @var \Voetbal\Association $association */
            $association = $request->getAttribute("tournament")->getCompetition()->getLeague()->getAssociation();

            $newCompetitor = new Competitor( $association, $competitor->getName() );
            $newCompetitor->setAbbreviation($competitor->getAbbreviation());
            $newCompetitor->setRegistered($competitor->getRegistered());
            $newCompetitor->setImageUrl($competitor->getImageUrl());
            $newCompetitor->setInfo($competitor->getInfo());

            $this->competitorRepos->save( $newCompetitor );

            $json = $this->serializer->serialize( $newCompetitor, 'json');
            return $this->respondWithJson($response, $json);
        }
        catch( \Exception $e ){
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit($request, $response, $args)
    {
        try {
            /** @var \Voetbal\Competitor $competitorSer */
            $competitorSer = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Competitor', 'json');
            /** @var \Voetbal\Association $association */
            $association = $request->getAttribute("tournament")->getCompetition()->getLeague()->getAssociation();

            $competitor = $this->getCompetitorFromInput((int)$args["competitorId"], $association);

            $competitor->setName($competitorSer->getName());
            $competitor->setAbbreviation($competitorSer->getAbbreviation());
            $competitor->setRegistered($competitorSer->getRegistered());
            $competitor->setImageUrl($competitorSer->getImageUrl());
            $competitor->setInfo($competitorSer->getInfo());
            $this->competitorRepos->save($competitor);

            $json = $this->serializer->serialize( $competitor, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    protected function getCompetitorFromInput(int $id, Association $association ): Competitor
    {
        $competitor = $this->competitorRepos->find($id);
        if ($competitor === null) {
            throw new \Exception("de deelnemer kon niet gevonden worden o.b.v. de invoer", E_ERROR);
        }
        if ($competitor->getAssociation() !== $association) {
            throw new \Exception("de bond van de deelnemer komt niet overeen met de verstuurde bond", E_ERROR);
        }
        return $competitor;
    }
}