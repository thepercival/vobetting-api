<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-11-17
 * Time: 14:02
 */

namespace App\Actions\Voetbal\Sport;

use App\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\Structure\Repository as StructureRepository;
use Voetbal\Sport\Config\Repository as SportConfigRepository;
use Voetbal\Sport\Config\Service as SportConfigService;
use Voetbal\Sport\Repository as SportRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use Voetbal\Competition;
use Voetbal\Sport\Config as SportConfig;

final class ConfigAction extends Action
{
    /**
     * @var SportRepository
     */
    protected $sportRepos;
    /**
     * @var StructureRepository
     */
    protected $structureRepos;
    /**
     * @var SportConfigRepository
     */
    protected $sportConfigRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        SportRepository $sportRepos,
        StructureRepository $structureRepos,
        SportConfigRepository $sportConfigRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->sportRepos = $sportRepos;
        $this->structureRepos = $structureRepos;
        $this->sportConfigRepos = $sportConfigRepos;
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            /** @var \Voetbal\Sport\Config $sportConfig */
            $sportConfig = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Sport\Config', 'json');

            $sport = $this->sportRepos->find( $sportConfig->getSportIdSer() );
            if ( $sport === null ) {
                throw new \Exception("de sport van de configuratie kan niet gevonden worden", E_ERROR);
            }
            if ( $competition->getSportConfig( $sport ) !== null ) {
                throw new \Exception("de sport wordt al gebruikt binnen de competitie", E_ERROR);
            }

            $sportConfigService = new SportConfigService();
            $structure = $this->structureRepos->getStructure($competition);
            $newSportConfig = $sportConfigService->createDefault( $sport, $competition, $structure );
            $newSportConfig->setWinPoints( $sportConfig->getWinPoints() );
            $newSportConfig->setDrawPoints( $sportConfig->getDrawPoints() );
            $newSportConfig->setWinPointsExt( $sportConfig->getWinPointsExt() );
            $newSportConfig->setDrawPointsExt( $sportConfig->getDrawPointsExt() );
            $newSportConfig->setPointsCalculation( $sportConfig->getPointsCalculation() );
            $newSportConfig->setNrOfGamePlaces( $sportConfig->getNrOfGamePlaces() );
            $this->sportConfigRepos->customAdd($newSportConfig, $structure->getFirstRoundNumber());

            $json = $this->serializer->serialize( $newSportConfig, 'json');
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

            /** @var \Voetbal\Sport\Config|false $sportConfigSer */
            $sportConfigSer = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Sport\Config', 'json');

            $sport = $this->sportRepos->find( $sportConfigSer->getSportIdSer() );
            if ( $sport === null ) {
                throw new \Exception("de sport van de configuratie kan niet gevonden worden", E_ERROR);
            }
            $sportConfig = $competition->getSportConfig( $sport );
            if( $sportConfig === null ) {
                throw new \Exception("de sportconfig is niet gevonden bij de competitie", E_ERROR);
            }
            $sportConfig->setWinPoints( $sportConfigSer->getWinPoints() );
            $sportConfig->setDrawPoints( $sportConfigSer->getDrawPoints() );
            $sportConfig->setWinPointsExt( $sportConfigSer->getWinPointsExt() );
            $sportConfig->setDrawPointsExt( $sportConfigSer->getDrawPointsExt() );
            $sportConfig->setPointsCalculation( $sportConfigSer->getPointsCalculation() );
            $sportConfig->setNrOfGamePlaces( $sportConfigSer->getNrOfGamePlaces() );
            $this->sportConfigRepos->save($sportConfig);

            $json = $this->serializer->serialize( $sportConfig, 'json');
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

            $sportConfig = $this->getSportConfigFromInput((int)$args["sportconfigId"], $competition);

            $this->sportConfigRepos->remove( $sportConfig );

            return $response->withStatus(200);
        }
        catch( \Exception $e ){
            return new ErrorResponse( $e->getMessage(), 422);
        }
    }

    protected function getSportConfigFromInput(int $id, Competition $competition ): SportConfig
    {
        $sportConfig = $this->sportConfigRepos->find($id);
        if ($sportConfig === null) {
            throw new \Exception("de sportconfiguratie kon niet gevonden worden o.b.v. de invoer", E_ERROR);
        }
        if ($sportConfig->getCompetition() !== $competition) {
            throw new \Exception("de competitie van de sportconfiguratie komt niet overeen met de verstuurde competitie", E_ERROR);
        }
        return $sportConfig;
    }

}