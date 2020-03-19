<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 22-5-18
 * Time: 12:23
 */

namespace App\Actions;

use App\Response\ErrorResponse;
use App\Response\ForbiddenResponse as ForbiddenResponse;
use Selective\Config\Configuration;
use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use Voetbal\ExternalSource;
use Voetbal\ExternalSource\Repository as ExternalSourceRepository;
use Voetbal\Attacher\Repository as AttacherRepos;
use Voetbal\Association\Repository as AssociationRepository;
use Voetbal\Attacher\Association\Repository as AssociationAttacherRepository;
use Voetbal\Attacher\Association as AssociationAttacher;
use Voetbal\Attacher\Sport\Repository as SportAttacherRepository;
use Voetbal\Attacher\Sport as SportAttacher;
use Voetbal\Import\Idable as Importable;
use Voetbal\Repository as VoetbalRepository;
use Voetbal\Attacher\Factory as AttacherFactory;

final class AttacherAction extends Action
{
    /**
     * @var ExternalSourceRepository
     */
    private $externalSourceRepos;
    /**
     * @var SportAttacherRepository
     */
    private $sportAttacherRepos;
    /**
     * @var AssociationAttacherRepository
     */
    private $associationAttacherRepos;
    /**
     * @var AssociationRepository
     */
    private $associationRepos;
    /**
     * @var AttacherFactory
     */
    private $attacherFactory;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        ExternalSourceRepository $externalSourceRepos,
        SportAttacherRepository $sportAttacherRepos,
        AssociationAttacherRepository $associationAttacherRepos,
        AssociationRepository $associationRepos
    ) {
        parent::__construct($logger, $serializer);
        $this->externalSourceRepos = $externalSourceRepos;
        $this->sportAttacherRepos = $sportAttacherRepos;
        $this->associationAttacherRepos = $associationAttacherRepos;
        $this->associationRepos = $associationRepos;
        $this->attacherFactory = new AttacherFactory();
    }

    public function fetchSports(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->sportAttacherRepos, $request, $response, $args);
    }

    public function fetchAssociations(Request $request, Response $response, $args): Response
    {
        return $this->fetch($this->associationAttacherRepos, $request, $response, $args);
    }

    protected function fetch(AttacherRepos $attacherRepos, Request $request, Response $response, $args): Response
    {
        try {
            $externalSource = $this->externalSourceRepos->find((int)$args['externalSourceId']);
            if ($externalSource === null) {
                throw new \Exception("er is geen externe bron meegegeven", E_ERROR);
            }
            $associations = $attacherRepos->findBy(["externalSource" => $externalSource]);

            $json = $this->serializer->serialize($associations, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }


//    public function fetchOne( Request $request, Response $response, $args ): Response
//    {
//        try {
//            $association = $this->associationRepos->find((int) $args['id']);
//            if ( $association === null ) {
//                throw new \Exception("geen bonden met het opgegeven id gevonden", E_ERROR);
//            }
//            $json = $this->serializer->serialize( $association, 'json');
//            return $this->respondWithJson( $response, $json );
//        }
//        catch( \Exception $e ){
//            return new ErrorResponse($e->getMessage(), 400);
//        }
//    }
//
    public function addAssociation(Request $request, Response $response, $args): Response
    {
        return $this->add($this->associationRepos, $this->associationAttacherRepos, $request, $response, $args);
    }

    protected function add(
        VoetbalRepository $importableRepos,
        AttacherRepos $attacherRepos,
        Request $request,
        Response $response,
        $args
    ): Response {
        try {
            $externalSource = $this->externalSourceRepos->find((int)$args['externalSourceId']);
            if ($externalSource === null) {
                throw new \Exception("er is geen externe bron meegegeven", E_ERROR);
            }

            /** @var \Voetbal\Attacher $attacherSer */
            $attacherSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Attacher', 'json');

            $importable = $importableRepos->find($attacherSer->getImportableIdForSer());
            if ($importable === null) {
                throw new \Exception("er kan geen importable worden gevonden", E_ERROR);
            }
            $newAttacher = $this->attacherFactory->createAssociation(
                $importable,
                $externalSource,
                $attacherSer->getExternalId()
            );
            $attacherRepos->save($newAttacher);

            $json = $this->serializer->serialize($newAttacher, 'json');
            return $this->respondWithJson($response, $json);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }


//
//    public function edit( Request $request, Response $response, $args ): Response
//    {
//        try {
//            /** @var \Voetbal\Association $associationSer */
//            $associationSer = $this->serializer->deserialize($this->getRawData(), 'Voetbal\Association', 'json');
//
//            $association = $this->associationRepos->find($args['id']);
//            if ( $association === null ) {
//                throw new \Exception("de bond kon niet gevonden worden o.b.v. de invoer", E_ERROR);
//            }
//            $parentAssociation = null;
//            if( $associationSer->getParent() !== null ) {
//                $parentAssociation = $this->associationRepos->find($associationSer->getParent()->getId());
//            }
//
//            $associationWithSameName = $this->associationRepos->findOneBy( array('name' => $associationSer->getName() ) );
//            if ( $associationWithSameName !== null and $associationWithSameName !== $association ){
//                throw new \Exception("de bond met de naam ".$associationSer->getName()." bestaat al", E_ERROR );
//            }
//
//            $association->setName($associationSer->getName());
//            $association->setDescription($associationSer->getDescription());
//            $associationService = new Association\Service();
//            $association = $associationService->changeParent( $association, $parentAssociation );
//            $this->associationRepos->save( $association );
//
//            $json = $this->serializer->serialize( $association, 'json');
//            return $this->respondWithJson( $response, $json );
//        }
//        catch( \Exception $e ){
//            return new ErrorResponse($e->getMessage(), 422);
//        }
//    }
//


    public function removeAssociation(Request $request, Response $response, $args): Response
    {
        return $this->remove($this->associationAttacherRepos, $request, $response, $args);
    }

    protected function remove(AttacherRepos $attacherRepos, Request $request, Response $response, $args): Response
    {
        try {
            $attacher = $attacherRepos->find((int)$args['id']);
            if ($attacher === null) {
                throw new \Exception("geen koppeling met het opgegeven id gevonden", E_ERROR);
            }
            $attacherRepos->remove($attacher);
            return $response->withStatus(200);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 422);
        }
    }
}