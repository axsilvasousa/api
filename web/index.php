<?php
 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Src\JWTWrapper;
use App\Users\Lista;

$app = new Silex\Application();

$app['debug'] = true;

$app->get('/teste', function() use ($app) {
 
    $jwt = JWTWrapper::encode([
        'expiration_sec' => 3600,
        'iss' => 'douglaspasqua.com',
        'userdata' => [
            'id' => 1,
            'name' => 'Douglas Pasqua'
        ]
    ]);
 
    return $jwt;
});
 
// Autenticacao
$app->post('/auth', function (Request $request) use ($app) {
    $user = $request->get('user');
    $pass = $request->get('pass');
    $dados = json_decode($request->getContent(), true);
    
    if($user  == 'foo' && $pass == 'bar') {
        // autenticacao valida, gerar token
        $jwt = JWTWrapper::encode([
            'expiration_sec' => 3600,
            'iss' => 'douglaspasqua.com',        
            'userdata' => [
                'id' => 1,
                'name' => 'Douglas Pasqua'
            ]
        ]);
 
        return $app->json([
            'login' => 'true',
            'access_token' => $jwt
        ]);
    }
 
    return $app->json([
        'login' => 'false',
        'message' => 'Login Inválido',
    ]);

});
 
// verificar autenticacao
$app->before(function(Request $request, Application $app) {
    $route = $request->get('_route');
    $routesNoBlock = [
        'GET_teste',
        'POST_auth'
    ];
 
    if(!in_array($route,$routesNoBlock)) {
        $authorization = $request->headers->get("Authorization");
        list($jwt) = sscanf($authorization, 'Bearer %s');
 
        if($jwt) {
            try {
                $app['jwt'] = JWTWrapper::decode($jwt);
            } catch(Exception $ex) {
                // nao foi possivel decodificar o token jwt
                return new Response('Acesso nao autorizado', 400);
            }
                 
        } else {
            // nao foi possivel extrair token do header Authorization
            return new Response('Token nao informado', 400);
        }
    }
});

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'silex',
        'user'      => 'root',
        'password'  => '',
    ),
));

// rota deve ser acessada somente por usuario autorizado com jwt
$app->get('/home', function(Application $app) {   
    return new Response ('Olá '. $app['jwt']->data->name. ' - '.$user->get());
});

$app->get('/users',function(Application $app) {
    $users = $app['db']->fetchAll('SELECT * FROM users'); 
    $response = new \Symfony\Component\HttpFoundation\JsonResponse();
    $response->setContent(json_encode(array('users' => $users), JSON_NUMERIC_CHECK));
    return $response;
});
 
$app->run();