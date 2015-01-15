<?php
namespace links;

use compact\repository\json\JsonRepository;
use compact\repository\DefaultModelConfiguration;
use compact\handler\impl\json\Json;

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
        $file = new  \SplFileInfo(__DIR__ . '/db/links.json');
        $this->db = new JsonRepository(new DefaultModelConfiguration('app\links\db\LinkModel'), $file);
    }
    
    /**
     * Returns all links
     * 
     * @return \compact\handler\impl\json\Json
     */
    public function get(){
        return new Json($this->db->search());
    }
}