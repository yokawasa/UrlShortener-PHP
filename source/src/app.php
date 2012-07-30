<?php
// ---------------------------------------------------------------------------------- 
// Microsoft Developer & Platform Evangelism 
//  
// Copyright (c) Microsoft Corporation. All rights reserved. 
//  
// THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND,  
// EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES  
// OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE. 
// ---------------------------------------------------------------------------------- 
// The example companies, organizations, products, domain names, 
// e-mail addresses, logos, people, places, and events depicted 
// herein are fictitious.  No association with any real company, 
// organization, product, domain name, email address, logo, person, 
// places, or events is intended or should be inferred. 
// ---------------------------------------------------------------------------------- 


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**  Bootstraping */
require_once __DIR__.'/../vendor/Silex/silex.phar';

$app = new Silex\Application();
$app['autoloader']->registerNamespaces(array('Khepin' => __DIR__,));
$app->register(new Khepin\ShortenerExtension(), array('url_file_name'  =>  __DIR__.'/../resources/urls.ini'));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
'twig.path' => __DIR__.'/templates',
'twig.class_path' => __DIR__.'/../vendor/twig/lib'
//Uncomment these lines to turn caching on (just make sure the directory is writeable)
//,
//'twig.options' => array('cache' => __DIR__.'/../cache'),
));

$app['key'] = 'my_key';


/** Decodes JSON Requests */
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request = new ParameterBag(is_array($data) ? $data : array());
    }
});

/** App Definition */

/** Prints out all URLs from the urls.ini file */
$app->get('/fileaccess', function() use ($app){
        $url_file = \parse_ini_file(__DIR__.'/../resources/urls.ini');
        //echo $url_file;
        $url_list = array();
        foreach ($url_file as $slug => $url) {
            //url_list[$slug] = $url;
            echo $slug;
            echo "    ";
            echo $url;
        }
        echo "done";
});

//Uncomment to allow errors to only be seen in localhost
/*$app->error(function(Exception $e) use ($app){
	if (!in_array($app['request']->server->get('REMOTE_ADDR'), array('127.0.0.1', '::1'))) {
		return $app->redirect('/');
	}
});*/

/** Shows the home page */
$app->get('/', function() use ($app){
    return $app['twig']->render('index.html.twig');
}); 

/** API Method to fetch all URLs */
$app->match('/api-getall', function () use ($app){

    $response = array();// $app['shortener']->getAll();    
    $response['Urls'] = $app['shortener']->getAll();    
    $response['Status'] = "SUCCESS";
    return $app->json($response, 200);
});

/** Echos out the full URL for a SLUG */
$app->get('/{url_slug}',function($url_slug) use($app){
    
    //NOTE:  switch the commenting on these lines and instead of printing out the URL, users will get redirected
    echo $app['shortener']->get($url_slug);
	//return $app->redirect($app['url_service']->get($url_slug));
});

/** Shows a view of all the URLs and their Slugs */
$app->get('/view/list', function() use($app){
    return $app['twig']->render('list.html.twig', array('list'  =>  $app['shortener']->getAll()));
});

/** Adds a URL via query string parameters alone */
$app->get('/add/{key}/{url_slug}', function($url_slug, $key) use ($app){
    //Check that the key sent over is valid
    if($app['key'] != $key){
        throw new Exception('Invalid key');
    }
    $app['shortener']->add($url_slug, $app['request']->get('url'));
    return $app['twig']->render('add.html.twig', array(
        'url_slug'  =>  $url_slug,
        'url'  =>  $app['request']->get('url')));
});

/** API method to add a new URL */
$app->match('/api-add', function (Request $request) use ($app){
    $key = $request->get('key');
    $url = $request->get('url');
    $url_slug = $request->get('url_slug');
    if($app['key'] != $key){
        throw new Exception('Invalid key');
    }

    if ($app['shortener']->exists($url_slug)) {
        $response = array('Status' => "Already Exists");
    }
    else {
        try {
            $app['shortener']->add($url_slug, $url);
            $response = array('Status' => "SUCCESS");
        } catch (Exception $e) {
            $response = array('Status' => "FAILURE");        
        }
    }    
    return $app->json($response, 201);
});




/**  Gets the details for a single URL */
$app->match('/api-get', function (Request $request) use ($app){
    $url_slug = $request->get('url_slug');

    if ($app['shortener']->exists($url_slug)) {
        $response = array('Status' => "SUCCESS", 'Url_Slug' => $url_slug, 'Url' => $app['shortener']->get($url_slug));
    } else {
        $response = array('Status' => "Does not exist");
    }
    return $app->json($response, 201);
});



return $app;
