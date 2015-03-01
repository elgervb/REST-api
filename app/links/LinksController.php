<?php
namespace links;

use compact\handler\impl\json\Json;
use app\links\db\LinkModel;
use compact\Context;
use compact\utils\ModelUtils;
use compact\http\HttpSession;
use compact\handler\impl\http\HttpStatus;
use compact\logging\Logger;
use user\UserContext;
use compact\auth\user\UserModel;
use app\links\LinksContext;
use compact\mvvm\impl\Model;

/**
 *
 * @author eaboxt
 *        
 */
class LinksController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // allow CORS
        Context::get()->http()
            ->getResponse()
            ->setCORSHeaders();
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
        if (! $aGuid){
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        $db = LinksContext::createLinksRepository($username);
        
        $sc = $db->createSearchCriteria();
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        $result = $db->search($sc);
        if ($result->count() <= 0) {
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        
        $model = $result->offsetGet(0);
        
        if ($db->delete($model)) {
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
    public function get($username, $guid = false)
    {
        $db = LinksContext::createLinksRepository($username);
        $sc = $db->createSearchCriteria();
        
        // when the user is not logged in, then only show the public links
        /* @var $auth auth \compact\auth\IAuthService */
        $auth = Context::get()->getService(Context::SERVICE_AUTH);
        if (!$auth->isLoggedIn() || $auth->getUser()->get(UserModel::USERNAME) !== $username){
            $sc->where(LinkModel::ISPUBLIC, true);
        }
        
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        $result = $db->search($sc);
        if ($result->count() > 0) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($result));
        }
        return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
    }

    /**
     * Head request: not yet implemented
     *
     * @param string $guid            
     *
     * @return \compact\handler\impl\http\HttpStatus
     */
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
     * @param string $username the username of the user adding the link
     *
     * @return HttpStatus 201 | 204 | 409 | 422 //
     *         201: created with a location header to the new /model/{id} containing the new ID,
     *         204 no content: when no post data available,
     *         401 not authorized when the user is not logged in
     *         409 conflict on double entry
     *         422 Unprocessable Entity on validation errors
     */
    public function post($username)
    {
        // when the user is not logged in, then only show the public links
        /* @var $auth auth \compact\auth\IAuthService */
        $auth = Context::get()->getService(Context::SERVICE_AUTH);
        if (!$auth->isLoggedIn() || $auth->getUser()->get(UserModel::USERNAME) !== $username){
            return new HttpStatus(HttpStatus::STATUS_401_UNAUTHORIZED);   
        }
        
        $db = LinksContext::createLinksRepository($username);
        
        // TODO implement 409
        $model = ModelUtils::getPost($db->getModelConfiguration());
        
        if (ModelUtils::isEmpty($model, $db->getModelConfiguration()->getFieldNames($model))) {
            return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
        }

        try {
            if ($db->save($model)) {
                // TODO add location header
                return new HttpStatus(HttpStatus::STATUS_201_CREATED, new Json($model));
            }
        } catch (ValidationException $e) {
            return new HttpStatus(HttpStatus::STATUS_422_UNPROCESSABLE_ENTITY, array(
                "message" => $e->getMessage()
            ));
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
    public function put($guid)
    {
        if (! $guid){
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        
        $db = LinksContext::createLinksRepository($username);
        $sc = $db->createSearchCriteria();
        
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        // check if model exists
        $result = $db->search($sc);
        if ($result->count() <= 0) {
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        
        $model = ModelUtils::getPost($db->getModelConfiguration(), new Model());
        
        if (ModelUtils::isEmpty($model, $db->getModelConfiguration()->getFieldNames($model))) {
            return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
        }
        
        if ($db->save($model)) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($model));
        }
        
        Logger::get()->logWarning("Could not update model " . get_class($model) . ' with GUID ' . $aGuid);
        return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
    }

    /**
     * Updates part of a model
     *
     * @param $username the username
     * @param $guid string the guid of the link
     *        
     * @return HttpError 200 | 204 | 404 //
     *         404 when $aId was not found.
     *         200 when update was successfull with in the body the saved model
     *         204 no content when no post data available
     */
    public function patch($username, $guid)
    {
        if (! $guid){
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        
        $db = LinksContext::createLinksRepository($username);
        $sc = $db->createSearchCriteria();
        if ($guid) {
            $sc->where(LinkModel::GUID, $guid);
        }
        
        // check if model exists
        $result = $db->search($sc);
        if ($result->count() <= 0) {
            return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
        }
        $model = $result->offsetGet(0);
        
        $postModel = ModelUtils::getPost($db->getModelConfiguration(), new LinkModel());
        
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
        
        // add GUID, so we know it's an update instead of an insert
        $model->set(LinkModel::GUID, $guid);
        
        if ($db->save($model)) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($model));
        }
        
        Logger::get()->logWarning("Could not patch model " . get_class($model) . ' with GUID ' . $aGuid);
        return new HttpStatus(HttpStatus::STATUS_404_NOT_FOUND);
    }
}