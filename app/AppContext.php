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

/**
 *
 * @author eaboxt
 *        
 */
class AppContext implements IAppContext
{
    /*
     * (non-PHPdoc) @see \compact\IAppContext::handlers()
     */
    public function handlers(Context $ctx)
    {
        LinksContext::get()->handlers($ctx);
        
        // Add Json hander to handle JSON responses
        $ctx->addHandler(new JsonHandler());
    }
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::routes()
     */
    public function routes(Router $router)
    {
        $router->add('^/$', function(){
            return new Json(array("error"=>"No route specified."));
        });
        
        LinksContext::get()->routes($router);
    }
    
    /*
     * (non-PHPdoc) @see \compact\IAppContext::services()
     */
    public function services(Context $ctx)
    {
        LinksContext::get()->services($ctx);
    }
}