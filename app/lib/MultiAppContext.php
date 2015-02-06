<?php
namespace lib;

use compact\IAppContext;
use compact\Context;
use compact\routing\Router;
use compact\logging\Logger;

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
     * @param string $regex Include the appcontext when the regex matches the request path
     * @param IAppContext $appCtx The app context to add
     * 
     * @param IAppContext $appCtx
     */
    public function add($regex, IAppContext $appCtx)
    {
        // add / around the regex for regex PHP syntax and add .* at the end
        $regex = '/' . preg_replace("/\//", "\\\/", $regex) . '.*/i';
        $this->contexts->offsetSet($regex , $appCtx);
    }
    
    /**
     * Returns a context registered at a path
     * 
     * @param string $contextpath
     * @return IAppContext|NULL
     */
    public function getContext($contextpath){
        if ($this->contexts->offsetExists($contextpath)){
            return $this->contexts->offsetGet($contextpath);
        }
        return null;
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
        foreach ($this->contexts as $regex => $appCtx) {
            if (preg_match($regex, $path)){
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
        foreach ($this->contexts as $regex => $appCtx) {
            if (preg_match($regex, $path)){
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
        foreach ($this->contexts as $regex => $appCtx) {
            if (preg_match($regex, $path)){
                $appCtx->handlers($ctx);
            }
        }
    }
}