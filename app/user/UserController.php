<?php
namespace user;

use compact\utils\ModelUtils;
use compact\auth\impl\SessionAuth;
use compact\auth\provider\PDOAuthProvider;
use compact\Context;
use compact\auth\user\UserModel;
use compact\handler\impl\http\HttpStatus;
use compact\repository\pdo\sqlite\SQLiteRepository;
use compact\repository\pdo\sqlite\SQLiteDynamicModelConfiguration;
use compact\handler\impl\json\Json;

class UserController
{

    /**
     * @var \compact\auth\IAuthService
     */
    private $auth;
    
    private $db;

    public function __construct()
    {
        // Create SQlite DB when needed
        $sqliteSqlPath = Context::get()->basePath('app/user/db/user.sqlite.sql');
        $dbPath = $sqliteSqlPath->getPath() . '/user.sqlite';
        $config = new SQLiteDynamicModelConfiguration('user');
        $config->setIdGeneration("guid");
        $startQuery = "";
        
        if (! file_exists($dbPath)) {
            $startQuery = file_get_contents($sqliteSqlPath);
        }
        
        $this->db = new SQLiteRepository($config, "sqlite:" . $sqliteSqlPath->getPath() . '/user.sqlite', $startQuery);
        
        // TODO register the auth service as a service in the AppContext
        $this->auth = new SessionAuth(new PDOAuthProvider($this->db));
        
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
        $sc = $this->db->createSearchCriteria();
        if ($guid) {
            $sc->where(UserModel::GUID, $guid);
        }
    
        $result = $this->db->search($sc);
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
        \compact\logging\Logger::get()->logNormal(__METHOD__);
        $request = Context::get()->http()->getRequest();
        $username = $request->getPost(UserModel::USERNAME);
        $password = sha1($request->getPost(UserModel::PASSWORD));
        
        $user = $this->auth->login($username, $password);
        
        if ($user) {
            return new HttpStatus(HttpStatus::STATUS_200_OK, $user);
        }
        
        return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
    }
}