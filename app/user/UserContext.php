<?php
namespace user;

use compact\IAppContext;
use compact\Context;
use compact\routing\Router;
use compact\logging\Logger;
use compact\repository\pdo\sqlite\SQLiteDynamicModelConfiguration;
use compact\repository\pdo\sqlite\SQLiteRepository;

class UserContext implements IAppContext
{
    private static $GUID_REGEX = "([a-zA-Z0-9]{8})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{12})";
    
    /**
     * Creates a new User repository
     * 
     * @return \user\SQLiteRepository
     */
    public static function createUserRepository(){
        // Create SQlite DB when needed
        $sqliteSqlPath = Context::get()->basePath('app/user/db/user.sqlite.sql');
        $dbPath = $sqliteSqlPath->getPath() . '/user.sqlite';
        $config = new SQLiteDynamicModelConfiguration('user');
        $startQuery = "";
    
        if (! file_exists($dbPath)) {
            $startQuery = file_get_contents($sqliteSqlPath);
        }
    
        return new SQLiteRepository($config, "sqlite:" . $dbPath, $startQuery);
    }
    
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
        
        // login
        $router->add('^/user/logout/?$', function () use($ctrl)
        {
            return $ctrl->logout();
        }, 'GET');
        
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