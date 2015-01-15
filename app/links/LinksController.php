<?php
namespace links;

use compact\repository\json\JsonRepository;
use compact\repository\DefaultModelConfiguration;
use compact\handler\impl\json\Json;
use app\links\db\LinkModel;
use compact\Context;
use compact\utils\ModelUtils;

/**
 *
 * @author eaboxt
 *        
 */
class LinksController
{

    private $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $file = new \SplFileInfo(__DIR__ . '/db/links.json');
        $this->db = new JsonRepository(new DefaultModelConfiguration('app\links\db\LinkModel'), $file);
        
        // allow CORS
        $this->setCORSHeaders();
    }

    /**
     * Returns all links or just one when the GUID has been set
     *
     * @param $guid [optional]
     *            The guid of the link
     *            
     * @return \compact\handler\impl\json\Json The JSON response
     */
    public function get($guid = false)
    {
        $sc = $this->db->createSearchCriteria();
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        return new Json($this->db->search($sc));
    }

    /**
     * Add a new link
     */
    public function post()
    {
        $model = ModelUtils::getPost($this->db->getModelConfiguration());
        $this->db->save($model);
    }

    /**
     * Set the CORS headers
     *
     * @see http://www.w3.org/TR/cors/
     * @see http://www.nczonline.net/blog/2010/05/25/cross-domain-ajax-with-cross-origin-resource-sharing/
     * @see http://enable-cors.org/
     * @see http://www.html5rocks.com/en/tutorials/cors/
     */
    private function setCORSHeaders()
    {
        $httpContext = Context::get()->http();
        $request = $httpContext->getRequest();
        $response = $httpContext->getResponse();
        
        $response->addHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, HEAD, PATCH, OPTIONS');
        
        // origin
        $origin = $request->getHeader('Origin');
        if ($origin)
            $response->addHeader('Access-Control-Allow-Origin', $origin); // or use '*' to allow all
                                                                              
        // custom headers
        $xHeaders = $request->getHeader('Access-Control-Request-Headers');
        if ($xHeaders)
            $response->addHeader('Access-Control-Allow-Headers', $xHeaders);
        
        $response->addHeader('Access-Control-Allow-Credentials', 'true');
        
        // change preflight request
        $response->addHeader('Access-Control-Max-Age', 1800);
    }
}