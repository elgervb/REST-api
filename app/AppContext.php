<?php
namespace app;

use compact\Context;
use compact\routing\Router;
use compact\handler\AssertHandler;
use compact\logging\Logger;
use compact\handler\impl\json\Json;
use compact\handler\impl\json\JsonHandler;
use app\links\LinksContext;
use lib\MultiAppContext;
use compact\handler\impl\http\HttpStatusHandler;
use user\UserContext;
use compact\auth\provider\PDOAuthProvider;
use compact\auth\impl\SessionAuth;

/**
 *
 * @author eaboxt
 *        
 */
class AppContext extends MultiAppContext
{
    const USER_CONTEXT = "/user";    
    const LINKS_CONTEXT = "/(.*)/links";

    public function __construct()
    {
        parent::__construct();
        
        $this->add(self::LINKS_CONTEXT, new LinksContext());
        $this->add(self::USER_CONTEXT, new UserContext(), true);
    }
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::handlers()
     */
    public function handlers(Context $ctx)
    {
        parent::handlers($ctx);
        
        // Add Json hander to handle JSON responses
        $ctx->addHandler(new JsonHandler());
        
        // add HttpStatus handler
        $ctx->addHandler(new HttpStatusHandler());
    }
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::routes()
     */
    public function routes(Router $router)
    {
        parent::routes($router);
        
        $router->add('^/$', function ()
        {
            return new Json(array(
                "status-code" => 404,
                "error" => "No route specified."
            ));
        });
    }
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::services()
     */
    public function services(Context $ctx)
    {
        parent::services($ctx);
        
        $db = UserContext::createUserRepository();
        
        $ctx->addService(Context::SERVICE_AUTH, function () use ($db)
        {
            return new SessionAuth(new PDOAuthProvider($db));
        });
    }
}