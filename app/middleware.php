<?php
// Application middleware

// $app->add( $app->getContainer()->get('jwtauth') );

$app->add( function ($request, $response, $next) use ( $app ) {

	$response = $next($request, $response);

	return $response
		->withHeader('Access-Control-Allow-Origin', $app->getContainer()->get('settings')['www']['url'] )
		->withHeader('Access-Control-Allow-Credentials', 'true')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Origin, Content-Type, Accept, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS');
});