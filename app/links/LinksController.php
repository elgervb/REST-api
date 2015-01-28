<?php
namespace links;

use compact\repository\json\JsonRepository;
use compact\repository\DefaultModelConfiguration;
use compact\handler\impl\json\Json;
use app\links\db\LinkModel;
use compact\Context;
use compact\utils\ModelUtils;
use compact\http\HttpSession;
use compact\handler\impl\http\HttpStatus;
use compact\logging\Logger;

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
        Context::get()->http()->getResponse()->setCORSHeaders();
    }

    /**
     * Delete a single model
     *
     * @param $aId mixed            
     *
     * @return HttpError 200 | 404 //
     *         404 when the model could not be found,
     *         200 when the model could be deleted
     */
    public function delete($aGuid)
    {
        if (! $aGuid)
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        
        $sc = $this->db->createSearchCriteria();
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        $result = $this->db->search($sc);
        if ($result->count() <= 0) {
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        
        $model = $result->offsetGet(0);
        
        if ($this->db->delete($model)) {
            return new HttpStatus(HttpStatus::STATUS_200_OK);
        } else {
            Logger::get()->logWarning("Could not delete model " . get_class($model) . ' with GUID ' . $aGuid);
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
    }

    /**
     * Returns all links or just one when the GUID has been set
     *
     * @param $guid [optional]
     *            The guid of the link
     *            
     * @return HttpStatus 200 | 204 //
     *         200 with JSON of one model when $guid not is null else it will return a resultset with models
     *         204 no content when there are no models in the database or the id is not known
     */
    public function get($guid = false)
    {
        $sc = $this->db->createSearchCriteria();
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        $result = $this->db->search($sc);
        if ($result->count() > 0) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($result));
        }
        return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
    }

    
    public function head($guid = null)
    {
        return new HttpStatus(HttpStatus::STATUS_501_NOT_IMPLEMENTED); // not yet implemented
    }

    /**
     * always returns 200, as the CORS headers are set
     *
     * @return HttpStatus
     */
    public function options()
    {
        return new HttpStatus(HttpStatus::STATUS_200_OK); // 200 ok
    }

    /**
     * Create a new model
     *
     * @return HttpStatus 201 | 204 | 409 | 422 //
     *         201: created with a location header to the new /model/{id} containing the new ID,
     *         204 no content: when no post data available,
     *         409 conflict on double entry
     *         422 Unprocessable Entity on validation errors
     */
    public function post()
    {
        // TODO implement 409 && 422
        $model = ModelUtils::getPost($this->db->getModelConfiguration());
        
        if (ModelUtils::isEmpty($model, $this->db->getModelConfiguration()->getFieldNames($model))) {
            return new HttpStatus(204);
        }
        
        if ($this->db->save($model)) {
            // TODO add location header
            return new HttpStatus(HttpStatus::STATUS_201_CREATED, new Json($model));
        }
        
        Logger::get()->logWarning("Could not save model " . get_class($model));
        return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
    }

    /**
     * Updates a single model
     *
     * @param $aId mixed            
     * @return HttpStatus 200 | 204 | 404 //
     *         404 when guid was not found.
     *         200 when update was successfull with in the body the saved model
     *         204 no content when no post data available
     */
    public function putAction($guid)
    {
        if (! $guid)
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        
        $sc = $this->db->createSearchCriteria();
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        // check if model exists
        $result = $this->db->search($sc);
        if ($result->count() <= 0) {
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        
        $model = ModelUtils::getPost($this->db->getModelConfiguration(), new Model());
        
        if (ModelUtils::isEmpty($model, $this->db->getModelConfiguration()->getFieldNames($model))) {
            return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
        }
        
        if ($this->db->save($model)) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($model));
        }
        
        Logger::get()->logWarning("Could not update model " . get_class($model) . ' with GUID ' . $aGuid);
        return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
    }

    /**
     * Updates part of a model
     *
     * @param $aId mixed            
     * @return HttpError 200 | 204 | 404 //
     *         404 when $aId was not found.
     *         200 when update was successfull with in the body the saved model
     *         204 no content when no post data available
     */
    public function patchAction($guid)
    {
        if (! $guid)
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        
        $sc = $this->db->createSearchCriteria();
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        // check if model exists
        $result = $this->db->search($sc);
        if ($result->count() <= 0) {
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        $model = $result->offsetGet(0);
        
        $postModel = ModelUtils::getPost($this->db->getModelConfiguration(), new Model());
        
        $fields = null;
        $vars = get_object_vars($postModel);
        if ($vars) {
            $fields = array_keys($vars);
        }
        try {
            ModelUtils::mergeInto($model, $postModel, $fields);
        } catch (MergeException $e) {
            return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT); // no content
        }
        
        if ($this->db->save($dbModel)){
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($dbModel));
        }
        
        Logger::get()->logWarning("Could not patch model " . get_class($model) . ' with GUID ' . $aGuid);
        return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
    }

    
}