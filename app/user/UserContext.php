<?php
namespace user;

use compact\IAppContext;
use compact\Context;
use compact\routing\Router;
use compact\logging\Logger;

class UserContext implements IAppContext
{
    private static $GUID_REGEX = "([a-zA-Z0-9]{8})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{12})";
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::handlers()
     */
    public function handlers(Context $ctx)
    {
        // start session
        Context::get()->http()->getSession()->start();
    }
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::routes()
     */
    public function routes(Router $router)
    {
        $ctrl = new UserController();
        
        // Enable CORS preflight request
        $router->add('.*', function ()
        {
            return " ";
        }, 'OPTIONS');
        
        // login
        $router->add('^/user/login/?$', function () use($ctrl)
        {
            return $ctrl->login();
        }, 'POST');
        
        // register a new user
        $router->add('^/user/register/?$', function () use($ctrl)
        {
            return $ctrl->register();
        }, 'POST');
        
        // register a new user
        $router->add('^/user/activate/('.self::$GUID_REGEX.')/?$', function ($guid) use($ctrl)
        {
            return $ctrl->activate($guid);
        }, 'GET');
        
        // For debugging purposes only... Exposes all users 
//         // return all users
//         $router->add('^/user/?$', function() use ($ctrl){
//             return $ctrl->get();
//         }, 'GET');
    }
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::services()
     */
    public function services(Context $ctx)
    {
        //
    }
}