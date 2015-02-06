<?php
namespace app\links;

use compact\IAppContext;
use compact\Context;
use compact\routing\Router;
use links\LinksController;

/**
 *
 * @author eaboxt
 *        
 */
class LinksContext implements IAppContext
{

    private static $GUID_REGEX = "([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})";

    /**
     * (non-PHPdoc)
     *
     * @see \compact\IAppContext::services()
     */
    public function services(Context $ctx)
    {
        //
    }

    /**
     * (non-PHPdoc)
     *
     * @see \compact\IAppContext::routes()
     */
    public function routes(Router $router)
    {
        $ctrl = new LinksController();
        
        // Enable CORS preflight request
        $router->add('.*', function() {
            return " ";
        }, 'OPTIONS');
        
        // return all links
        $router->add('^/(.*)/links/?$', function($username) use ($ctrl){
            return $ctrl->get($username);
        }, 'GET');
        
        // return one link
        $router->add('^/(.*)/links/('.self::$GUID_REGEX.')/?$', function($username, $guid) use ($ctrl){
            return $ctrl->get($username, $guid);
        }, 'GET');
        
        // Add a new link
        $router->add('^/links/?$', function() use ($ctrl){
            return $ctrl->post();
        }, 'POST');
        
        
    }

    /**
     * (non-PHPdoc)
     *
     * @see \compact\IAppContext::handlers()
     */
    public function handlers(Context $ctx)
    {
        //
    }
}