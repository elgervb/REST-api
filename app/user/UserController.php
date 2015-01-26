<?php
namespace user;

use compact\Context;
use compact\auth\user\UserModel;
use compact\handler\impl\http\HttpStatus;
use compact\handler\impl\json\Json;
use app\AppContext;
use compact\auth\IAuthService;

class UserController
{

    private $auth;

    private $db;

    public function __construct()
    {
        
        $this->auth = Context::get()->getService(Context::SERVICE_AUTH);
        assert ('$this->auth instanceof \compact\auth\IAuthService');
        
        // allow CORS
        Context::get()->http()
            ->getResponse()
            ->setCORSHeaders();
    }

    /**
     * Returns all models or just one when the GUID has been set
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
        /* @var $appCtx \app\AppContext */
        $appCtx = Context::get()->getAppContext();
        $db = $appCtx->createUserRepository();
        
        $sc = $db->createSearchCriteria();
        if ($guid) {
            $sc->where(UserModel::GUID, $guid);
        }
        
        $result = $db->search($sc);
        if ($result->count() > 0) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($result));
        }
        return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
    }

    /**
     * Logs in the user
     *
     * @return \compact\handler\impl\http\HttpStatus
     */
    public function login()
    {
        $request = Context::get()->http()->getRequest();
        $username = $request->getPost(UserModel::USERNAME);
        $password = sha1($request->getPost(UserModel::PASSWORD));
        
        $user = $this->auth->login($username, $password);
        
        if ($user) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($user));
        }
        
        return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
    }
}