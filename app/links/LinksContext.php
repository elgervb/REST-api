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
        $router->add('/links', function() use ($ctrl){
            return $ctrl->get();
        });
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