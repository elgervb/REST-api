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

    private static $INSTANCE;
    
    private static $GUID_REGEX = "([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})";

    /**
     * Returns the singleton for LinksContext
     *
     * @return \app\links\LinksContext
     */
    public static function get()
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new LinksContext();
        }
        
        return self::$INSTANCE;
    }

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
        
        // return all links
        $router->add('/links/?$', function() use ($ctrl){
            return $ctrl->get();
        }, 'GET');
        
        // Add a new link
        $router->add('/links/?$', function() use ($ctrl){
            return $ctrl->post();
        }, 'POST');
        
        // return one link
        $router->add('/links/('.self::$GUID_REGEX.')/?$', function($guid) use ($ctrl){
            return $ctrl->get($guid);
        }, 'GET');
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