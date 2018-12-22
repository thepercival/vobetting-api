<?php
/**
 * Created by PhpStorm.
 * User: coen
 * Date: 28-3-18
 * Time: 20:31
 */

namespace App\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use VOBetting\User\Repository as UserRepos;
use VOBetting\Tournament\Repository as TournamentRepos;
use VOBetting\Tournament\Service as TournamentService;
use VOBetting\Token;
use VOBetting\User;
use App\Response\Forbidden as ForbiddenResponse;
use Voetbal\Service as VoetbalService;
use VOBetting\Role;

class Authentication
{
    /**
     * @var Token
     */
    protected $token;
    /**
     * @var UserRepos
     */
    protected $userRepos;
    /**
     * @var TournamentRepos
     */
    // protected $tournamentRepos;
    /**
     * @var TournamentService
     */
    // protected $tournamentService;
    /**
     * @var VoetbalService
     */
    protected $voetbalService;

    public function __construct(
        Token $token,
        UserRepos $userRepos,
        /*TournamentRepos $tournamentRepos,
        TournamentService $tournamentService,*/
        VoetbalService $voetbalService
    ) {
        $this->token = $token;
        $this->userRepos = $userRepos;
        // $this->tournamentRepos = $tournamentRepos;
        // $this->tournamentService = $tournamentService;
        $this->voetbalService = $voetbalService;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        // user is not known, no need to check authorization
        if ($this->token->isPopulated() !== true) {
            return $next($request, $response);
        }
//        if (substr($request->getUri()->getPath(), 0, 12) === "/tournaments"
//        || substr($request->getUri()->getPath(), 0, 9) === "/sponsors" ) {
//            return $next($request, $response);
//        }
//
//        $args = $request->getAttribute('routeInfo')[2];
//        if (array_key_exists('resourceType', $args) === false) {
//            return new ForbiddenResponse("niet geautoriseerd, het pad kan niet bepaalt worden", 401);
//        }
//        $resourceType = $args['resourceType'];

        $user = $this->getUser();
        if ($user === null) {
            return new ForbiddenResponse("gebruiker kan niet gevonden worden", 401);
        }
//        $id = (array_key_exists('id', $args) && ctype_digit($args['id'])) ? (int)$args['id'] : null;
//        if (!$this->authorized($user, $resourceType, $request->getMethod(), $request->getQueryParams(), $id)) {
//            return new ForbiddenResponse("geen autorisatie voor actie gevonden", 401);
//        }

        return $next($request, $response);
    }

    protected function getUser()
    {
        if ($this->token->getUserId() === null) {
            return null;
        }
        return $this->userRepos->find($this->token->getUserId());
    }

    protected function authorized(User $user, string $resourceType, string $method, array $queryParams, int $id = null)
    {
        // for $resourceType === 'structures' ->add/edit need to check in the action if round->competition === competitionSend
        if ($resourceType === 'teams') {
            return $this->teamActionAuthorized($user, $method, $queryParams);
        } elseif ($resourceType === 'pouleplaces') {
            return $this->pouleplaceActionAuthorized($user, $method, $queryParams, $id);
        } elseif ($resourceType === 'games') {
            return $this->gameActionAuthorized($user, $method, $queryParams, $id);
        } elseif ($resourceType === 'fields' || $resourceType === 'planning' || $resourceType === 'referees'
            || $resourceType === 'structures' || $resourceType === 'roundconfigs'
        ) {
            return $this->otherActionAuthorized($user, $method, $queryParams, $id);
        }
        return false;
    }

    protected function teamActionAuthorized(User $user, string $method, array $queryParams)
    {
        if (array_key_exists("associationid", $queryParams) !== true) {
            return false;
        }
        if ($method !== 'POST' && $method !== 'PUT') {
            return false;
        }
        $assRepos = $this->voetbalService->getRepository(\Voetbal\Association::class);
        $association = $assRepos->find($queryParams["associationid"]);
        if ($association === null) {
            return false;
        }
        if( $this->tournamentService->mayUserChangeTeam( $user, $association ) === false ) {
            return false;
        }
        
        return true;
    }

    protected function pouleplaceActionAuthorized(User $user, string $method, array $queryParams, int $id = null)
    {
        if ($method !== 'PUT') {
            return false;
        }
        return $this->otherActionAuthorized($user, $method, $queryParams);
    }

    protected function gameActionAuthorized(User $user, string $method, array $queryParams, int $id = null)
    {
        if ($method !== 'PUT') {
            return false;
        }
        if (array_key_exists("competitionid", $queryParams) !== true) {
            return false;
        }
        $tournament = $this->tournamentRepos->findOneBy(["competition" => $queryParams["competitionid"]]);
        if ($tournament === null) {
            return false;
        }
        if ($tournament->hasRole($user, Role::GAMERESULTADMIN)) {
            return true;
        }
        if (!$tournament->hasRole($user, Role::REFEREE)) {
            return false;
        }
        $gameRepos = $this->voetbalService->getRepository(\Voetbal\Game::class);
        $game = $gameRepos->find($id);
        if ($game === null || $game->getReferee() === null ) {
            return false;
        }
        if( $game->getReferee()->getEmailaddress() === $user->getEmailaddress() ) {
            return true;
        }
        return false;
    }

    protected function otherActionAuthorized(User $user, string $method, array $queryParams)
    {
        if (array_key_exists("competitionid", $queryParams) !== true) {
            return false;
        }
        $tournament = $this->tournamentRepos->findOneBy(["competition" => $queryParams["competitionid"]]);
        if ($tournament === null) {
            return false;
        }
        if (!$tournament->hasRole($user, Role::ADMIN)) {
            return false;
        }
        return true;
    }
}