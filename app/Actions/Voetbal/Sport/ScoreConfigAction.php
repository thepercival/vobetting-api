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
use Voetbal\Round\Number as RoundNumber;
use Voetbal\Sport;
use Voetbal\Structure\Repository as StructureRepository;
use Voetbal\Sport\ScoreConfig\Repository as SportScoreConfigRepository;
use Voetbal\Sport\ScoreConfig\Service as SportScoreConfigService;
use Voetbal\Sport\Repository as SportRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Actions\Action;
use Voetbal\Competition;
use Voetbal\Sport\ScoreConfig as SportScoreConfig;

final class ScoreConfigAction extends Action
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
     * @var SportScoreConfigRepository
     */
    protected $sportScoreConfigRepos;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        SportRepository $sportRepos,
        StructureRepository $structureRepos,
        SportScoreConfigRepository $sportScoreConfigRepos
    )
    {
        parent::__construct($logger,$serializer);
        $this->sportRepos = $sportRepos;
        $this->structureRepos = $structureRepos;
        $this->sportScoreConfigRepos = $sportScoreConfigRepos;
    }

    public function add( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            /** @var \Voetbal\Sport\ScoreConfig $sportScoreConfigSer */
            $sportScoreConfigSer = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Sport\ScoreConfig', 'json');

            $queryParams = $request->getQueryParams();

            $roundNumberAsValue = 0;
            if (array_key_exists("roundnumber", $queryParams) && strlen($queryParams["roundnumber"]) > 0) {
                $roundNumberAsValue = (int)$queryParams["roundnumber"];
            }
            if ( $roundNumberAsValue === 0 ) {
                throw new \Exception("geen rondenummer opgegeven", E_ERROR);
            }
            $structure = $this->structureRepos->getStructure( $competition );
            $roundNumber = $structure->getRoundNumber( $roundNumberAsValue );

            $sport = $competition->getSportBySportId( (int) $queryParams["sportid"] );
            if ( $sport === null ) {
                throw new \Exception("de sport kon niet gevonden worden", E_ERROR);
            }
            if ( $roundNumber->getSportScoreConfig( $sport ) !== null ) {
                throw new \Exception("er zijn al score-instellingen aanwezig", E_ERROR);
            }

            $sportScoreConfig = new \Voetbal\Sport\ScoreConfig( $sport, $roundNumber, null );
            $sportScoreConfig->setDirection( SportScoreConfig::UPWARDS );
            $sportScoreConfig->setMaximum($sportScoreConfigSer->getMaximum());
            $sportScoreConfig->setEnabled($sportScoreConfigSer->getEnabled());
            if( $sportScoreConfigSer->hasNext() ) {
                $nextScoreConfig = new SportScoreConfig( $sport, $roundNumber, $sportScoreConfig );
                $nextScoreConfig->setDirection( SportScoreConfig::UPWARDS );
                $nextScoreConfig->setMaximum($sportScoreConfigSer->getNext()->getMaximum());
                $nextScoreConfig->setEnabled($sportScoreConfigSer->getNext()->getEnabled());
            }

            $this->sportScoreConfigRepos->save($sportScoreConfig);

            $this->removeNext($roundNumber, $sport);

            $json = $this->serializer->serialize( $sportScoreConfig, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    public function edit( Request $request, Response $response, $args ): Response
    {
        try {
            /** @var \Voetbal\Competition $competition */
            $competition = $request->getAttribute("tournament")->getCompetition();

            $structure = $this->structureRepos->getStructure( $competition ); // to init next/previous

            $queryParams = $request->getQueryParams();
            $roundNumberAsValue = 0;
            if (array_key_exists("roundnumber", $queryParams) && strlen($queryParams["roundnumber"]) > 0) {
                $roundNumberAsValue = (int)$queryParams["roundnumber"];
            }
            if ( $roundNumberAsValue === 0 ) {
                throw new \Exception("geen rondenummer opgegeven", E_ERROR);
            }

            $roundNumber = $structure->getRoundNumber( $roundNumberAsValue );
            if ( $roundNumber === null ) {
                throw new \Exception("het rondenummer kan niet gevonden worden", E_ERROR);
            }

            /** @var \Voetbal\Sport\ScoreConfig $sportScoreConfigSer */
            $sportScoreConfigSer = $this->serializer->deserialize( $this->getRawData(), 'Voetbal\Sport\ScoreConfig', 'json');

            /** @var \Voetbal\Sport\ScoreConfig|null $sportScoreConfig */
            $sportScoreConfig = $this->sportScoreConfigRepos->find( (int)$args['sportscoreconfigId'] );
            if ( $sportScoreConfig === null ) {
                throw new \Exception("er zijn geen score-instellingen gevonden om te wijzigen", E_ERROR);
            }

            $sportScoreConfig->setMaximum($sportScoreConfigSer->getMaximum());
            $sportScoreConfig->setEnabled($sportScoreConfigSer->getEnabled());
            $this->sportScoreConfigRepos->save($sportScoreConfig);
            if( $sportScoreConfig->hasNext() && $sportScoreConfigSer->hasNext() ) {
                $nextScoreConfig = $sportScoreConfig->getNext();
                $nextScoreConfig->setMaximum($sportScoreConfigSer->getNext()->getMaximum());
                $nextScoreConfig->setEnabled($sportScoreConfigSer->getNext()->getEnabled());
                $this->sportScoreConfigRepos->save($nextScoreConfig);
            }

            $this->removeNext($roundNumber, $sportScoreConfig->getSport() );

            $json = $this->serializer->serialize( $sportScoreConfig, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }

    protected function removeNext( RoundNumber $roundNumber, Sport $sport) {
        while( $roundNumber->hasNext() ) {
            $roundNumber = $roundNumber->getNext();
            $scoreConfig = $roundNumber->getSportScoreConfig( $sport );
            if( $scoreConfig === null ) {
                continue;
            }
            $roundNumber->getSportScoreConfigs()->removeElement( $scoreConfig );
            $this->sportScoreConfigRepos->remove($scoreConfig);
        }
    }

}