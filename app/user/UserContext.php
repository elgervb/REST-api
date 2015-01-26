<?php
namespace user;

use compact\IAppContext;
use compact\Context;
use compact\routing\Router;
use compact\logging\Logger;

class UserContext implements IAppContext
{
    
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
        
        $router->add('^/user/login/?$', function () use($ctrl)
        {
            return $ctrl->login();
        }, 'POST');
        
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