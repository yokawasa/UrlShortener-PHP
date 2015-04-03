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

namespace Khepin;

use Silex\ServiceProviderInterface;
use Silex\Application;
class ShortenerExtension implements ServiceProviderInterface {

    public function register(Application $app){
        $app['shortener'] = $app->share(function() use($app){
            return new UrlShortener($app['url_file_name']);
        });
    }
    
    //[note]
    // Added implementation of function boot(Application $app) because
    // Class Khepin\\ShortenerExtension contains 1 abstract method and must
    // therefore be declared abstract or implement the remaining methods. 
    public function boot(Application $app){}
}
?>
