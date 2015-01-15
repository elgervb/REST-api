<?php
namespace lib;

use compact\IAppContext;
use compact\Context;
use compact\routing\Router;

/**
 * Application Context for multiple applications.
 * When overriding this class, make sure you call the parent methods.
 * 
 * @author elger
 */
class MultiAppContext implements IAppContext
{

    /**
     * The contexts for other apps
     * 
     * @var \ArrayObject
     */
    private $contexts;

    public function __construct()
    {
        $this->contexts = new \ArrayObject();
    }

    /**
     * Add a new AppContext for a path
     * 
     * @param string $startPath The start path of the application
     * 
     * @param IAppContext $appCtx
     */
    public function add($startPath, IAppContext $appCtx)
    {
        $this->contexts->offsetSet($startPath, $appCtx);
    }

    /**
     * (non-PHPdoc)
     * @see \compact\IAppContext::handlers()
     */
    public function services(Context $ctx)
    {
        $path = Context::get()->http()
            ->getRequest()
            ->getPathInfo();
        
        /* @var compact\IAppContext $appCtx */
        foreach ($this->contexts as $start => $appCtx) {
            if (substr($path, 0, strlen($start)) === $start){
                $appCtx->services($ctx);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \compact\IAppContext::handlers()
     */
    public function routes(Router $router)
    {
        $path = Context::get()->http()
        ->getRequest()
        ->getPathInfo();
        
        /* @var compact\IAppContext $appCtx */
        foreach ($this->contexts as $start => $appCtx) {
            if (substr($path, 0, strlen($start)) === $start){
                $appCtx->routes($router);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \compact\IAppContext::handlers()
     */
    public function handlers(Context $ctx)
    {
        $path = Context::get()->http()
        ->getRequest()
        ->getPathInfo();
        
        /* @var compact\IAppContext $appCtx */
        foreach ($this->contexts as $start => $appCtx) {
            if (substr($path, 0, strlen($start)) === $start){
                $appCtx->handlers($ctx);
            }
        }
    }
}