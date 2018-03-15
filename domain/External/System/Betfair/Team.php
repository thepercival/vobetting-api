<?php

/**
 * Created by PhpStorm.
 * User: coen
 * Date: 14-3-18
 * Time: 12:02
 */

namespace VOBetting\External\System\Betfair;

use Voetbal\External\System as ExternalSystemBase;
use Voetbal\External\System\Importer\Team as TeamImporter;
use Voetbal\External\Importable as ImportableObject;
use Voetbal\Team\Service as TeamService;
use Voetbal\Team\Repository as TeamRepos;
use Voetbal\External\Object\Service as ExternalObjectService;
use Voetbal\External\Team\Repository as ExternalTeamRepos;
use Voetbal\Association;
use Voetbal\Team as TeamBase;
use Voetbal\External\Competition as ExternalCompetition;

class Team implements TeamImporter
{
    /**
     * @var ExternalSystemBase
     */
    private $externalSystemBase;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * @var TeamService
     */
    private $service;

    /**
     * @var TeamRepos
     */
    private $repos;

    /**
     * @var ExternalObjectService
     */
    private $externalObjectService;

    /**
     * @var ExternalTeamRepos
     */
    private $externalObjectRepos;

    public function __construct(
        ExternalSystemBase $externalSystemBase,
        ApiHelper $apiHelper,
        TeamService $service,
        TeamRepos $repos,
        ExternalTeamRepos $externalRepos
    ) {
        $this->externalSystemBase = $externalSystemBase;
        $this->apiHelper = $apiHelper;
        $this->service = $service;
        $this->repos = $repos;
        $this->externalObjectRepos = $externalRepos;
        $this->externalObjectService = new ExternalObjectService(
            $this->externalObjectRepos
        );
    }

    public function get(ExternalCompetition $externalCompetition)
    {
        $retVal = $this->apiHelper->getData("competitions/" . $externalCompetition->getExternalId() . "/teams");
        return $retVal->teams;
    }

    public function getId($externalSystemTeam): int
    {
        $url = $externalSystemTeam->_links->self->href;
        $strPos = strrpos($url, '/');
        if ($strPos === false) {
            throw new \Exception("could not get id of fd-team", E_ERROR);
        }
        $id = substr($url, $strPos + 1);
        if (strlen($id) == 0 || !is_numeric($id)) {
            throw new \Exception("could not get id of fd-team", E_ERROR);
        }
        return (int)$id;
    }

    public function create(Association $association, $externalSystemTeam)
    {
        $id = $this->getId($externalSystemTeam);
        $team = $this->repos->findOneBy(["association" => $association, "name" => $externalSystemTeam->name]);
        if ($team === null) {
            throw new \Exception("for " . $this->externalSystemBase->getName() ."-team ".$id." no team found", E_ERROR);
        }
        $externalTeam = $this->createExternal($team, $this->getId($externalSystemTeam));
        return $team;
    }

    public function update(TeamBase $team, $externalSystemObject)
    {
        // is no source
        return true;
    }

    protected function createExternal(ImportableObject $importable, $externalId)
    {
        $externalTeam = $this->externalObjectRepos->findOneByExternalId(
            $this->externalSystemBase,
            $externalId
        );
        if ($externalTeam === null) {
            $externalTeam = $this->externalObjectService->create(
                $importable,
                $this->externalSystemBase,
                $externalId
            );
        }
        return $externalTeam;
    }
}
