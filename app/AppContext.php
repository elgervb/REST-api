<?php
namespace app;

use compact\IAppContext;
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

/**
 *
 * @author eaboxt
 *        
 */
class AppContext extends MultiAppContext
{

    public function __construct()
    {
        parent::__construct();
        
        $this->add('/links', new LinksContext());
        $this->add('/user', new UserContext());
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
    }
}