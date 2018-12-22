<?php
// DIC configuration

use \JMS\Serializer\SerializerBuilder;

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Doctrine
$container['em'] = function ($c) {
    $settings = $c->get('settings')['doctrine'];
    class CustomYamlDriver extends Doctrine\ORM\Mapping\Driver\YamlDriver
    {
        protected function loadMappingFile($file)
        {
            return Symfony\Component\Yaml\Yaml::parse(file_get_contents($file), Symfony\Component\Yaml\Yaml::PARSE_CONSTANT);
        }
    }

    $config = Doctrine\ORM\Tools\Setup::createConfiguration(
        $settings['meta']['auto_generate_proxies'],
        $settings['meta']['proxy_dir'],
        $settings['meta']['cache']
    );
    $config->setMetadataDriverImpl( new CustomYamlDriver( $settings['meta']['entity_path'] ));

    $em = Doctrine\ORM\EntityManager::create($settings['connection'], $config);
    // $em->getConnection()->setAutoCommit(false);
    return $em;
};

// symfony serializer
$container['serializer'] = function( $c ) {
    // temporary, real one is set in middleware
    return SerializerBuilder::create()->build();
};

// voetbalService
$container['voetbal'] = function( $c ) {
    return new Voetbal\Service($c->get('em'));
};

// toernooiService
//$container['toernooi'] = function( $c ) {
//    $em = $c->get('em');
//    $tournamentRepos = new VOBetting\Tournament\Repository($em,$em->getClassMetaData(VOBetting\Tournament::class));
//    $roleRepos = new VOBetting\Role\Repository($em,$em->getClassMetaData(VOBetting\Role::class));
//    $userRepos = new VOBetting\User\Repository($em,$em->getClassMetaData(VOBetting\User::class));
//    return new VOBetting\Tournament\Service(
//        $c->get('voetbal'),
//        $tournamentRepos,
//        $roleRepos,
//        $userRepos
//    );
//};

// JWT
$container["jwt"] = function ( $c ) {
    return new \StdClass;
};

// actions
$container['App\Action\Auth'] = function ($c) {
    $em = $c->get('em');
    $repos = new VOBetting\User\Repository($em,$em->getClassMetaData(VOBetting\User::class));
    return new App\Action\Auth($repos,$c->get('serializer'),$c->get('settings'));
};
$container['App\Action\User'] = function ($c) {
    $em = $c->get('em');
    $repos = new VOBetting\User\Repository($em,$em->getClassMetaData(VOBetting\User::class));
    return new App\Action\User($repos,$c->get('serializer'),$c->get('settings'));
};
$container['App\Action\BetLine'] = function ($c) {
    $em = $c->get('em');
    $repos = new VOBetting\BetLine\Repository($em,$em->getClassMetaData(VOBetting\BetLine::class));
    $gameRepository = new Voetbal\Game\Repository($em,$em->getClassMetaData(Voetbal\Game::class));
    return new App\Action\BetLine($repos,$gameRepository,$c->get('serializer'));
};
$container['App\Action\LayBack'] = function ($c) {
    $em = $c->get('em');
    $repos = new VOBetting\LayBack\Repository($em,$em->getClassMetaData(VOBetting\LayBack::class));
    $betLineRepository = new VOBetting\BetLine\Repository($em,$em->getClassMetaData(VOBetting\BetLine::class));
    return new App\Action\LayBack($repos,$betLineRepository,$c->get('serializer'));
};
$container['App\Action\Bookmaker'] = function ($c) {
    $em = $c->get('em');
    $repos = new VOBetting\Bookmaker\Repository($em,$em->getClassMetaData(VOBetting\Bookmaker::class));
    $service = new VOBetting\Bookmaker\Service($repos);
    return new App\Action\Bookmaker($repos,$service,$c->get('serializer'));
};