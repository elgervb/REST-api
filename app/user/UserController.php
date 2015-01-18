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

class UserController
{

    /**
     * @var \compact\auth\IAuthService
     */
    private $auth;

    public function __construct()
    {
        $sqliteSqlPath = Context::get()->basePath('app/user/db/user.sqlite.sql');
        $dbPath = $sqliteSqlPath->getPath() . '/user.sqlite';
        $config = new SQLiteDynamicModelConfiguration('user');
        $config->setIdGeneration("guid");
        $startQuery = "";
        
        if (! file_exists($dbPath)) {
            $startQuery = file_get_contents($sqliteSqlPath);
        }
        
        \compact\logging\Logger::get()->logNormal("StartQuery: " . $startQuery);
        
        $db = new SQLiteRepository($config, "sqlite:" . $sqliteSqlPath->getPath() . '/user.sqlite', $startQuery);
        
        // TODO register the auth service as a service in the AppContext
        $this->auth = new SessionAuth(new PDOAuthProvider($db));
        
        // allow CORS
        Context::get()->http()
            ->getResponse()
            ->setCORSHeaders();
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