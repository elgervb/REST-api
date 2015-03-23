<?php
namespace app\links;

use compact\IAppContext;
use compact\Context;
use compact\routing\Router;
use links\LinksController;
use compact\repository\json\JsonRepository;
use compact\repository\DefaultModelConfiguration;
use compact\logging\Logger;

/**
 *
 * @author eaboxt
 *        
 */
class LinksContext implements IAppContext
{

    private static $GUID_REGEX = "([a-zA-Z0-9]{8})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{12})";

    
    /**
     * Creates a new Links repository
     * @param string $username
     */
    public static function createLinksRepository($username){
        $file = new \SplFileInfo(__DIR__ . '/db/'.$username.'.json');
        
        Logger::get()->logFine($file->getPathname());
        return new JsonRepository(new DefaultModelConfiguration('app\links\db\LinkModel'), $file);
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
        Logger::get()->logFinest("Include LinksContext routes");
        
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
        $router->add('^/(.*)/links/?$', function($username) use ($ctrl){
            return $ctrl->post($username);
        }, 'POST');
        
        // PATCH /username/links/A19A5424-E93B-3513-F8D0-9314A12CF182
        $router->add('^/(.*)/links/('.self::$GUID_REGEX.')/?$', function($username, $guid) use ($ctrl){
            return $ctrl->patch($username, $guid);
        }, 'PATCH');
        
        // PUT /username/links/A19A5424-E93B-3513-F8D0-9314A12CF182
        $router->add('^/(.*)/links/('.self::$GUID_REGEX.')/?$', function($username, $guid) use ($ctrl){
            return $ctrl->patch($username, $guid);
        }, 'PUT');
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