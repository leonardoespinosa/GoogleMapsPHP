<?php

// Ejemplo usando Silex+Twig+Geocoder
// ==================================

// uso del autoload generado por composer
require_once __DIR__.'/vendor/autoload.php';

// uso de las clases Request y Response de Symfony
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Crea la Aplicación
// ==================

$app = new Silex\Application();

// configurar el generador de URLs
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// configurar Twig en la Aplicación
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

// si la ruta termina en "/", cambia la ruta a "/index.php"
if ( substr($_SERVER["REQUEST_URI"], -1) == '/' ) {
  header("Location: index.php");
  die();
}

// Rutas
// =====

// la ruta "/" muestra la pantalla de inicio
$app->get('/', function() use($app) {

  // muestra la plantilla views/index.html.twig
  return $app['twig']->render('index.html.twig');

  // al usar bind define el nombre 'formulario'
  // en las plantillas es posible incluir un link usando url('formulario')
  // es posible hacer un redirect usando $app['url_generator']->generate('formulario')
})->bind('formulario');


// la ruta "/doGeocode" recibe los datos del formulario
// note que se recibe $request como parámetro
$app->post('/doGeocode', function(Request $request) use($app) {

  // toma los datos de la petición web
  $lugar = $request->get('lugar');

  // usa el Geocoder
  $geocoderAdapter  = new \Ivory\HttpAdapter\CurlHttpAdapter();
  $geocoder = new \Geocoder\ProviderAggregator();
  $geocoder->registerProvider(new \Geocoder\Provider\GoogleMaps($geocoderAdapter));

  // datos del primer resultado
  $datos    = $geocoder->geocode($lugar);
  $latitud = $datos->first()->getLatitude();
  $longitud  = $datos->first()->getLongitude();

  // muestra la plantilla views/datos.html.twig
  // envia los datos a la plantilla
  return $app['twig']->render('datos.html.twig', array(
      'datos'   => $datos
    ));
    
})->bind('doGeocode');


// Corre la Aplicación
// ===================

$app['debug'] = true;
$app->run();