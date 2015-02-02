<?php
namespace user;

use compact\Context;
use compact\auth\user\UserModel;
use compact\handler\impl\http\HttpStatus;
use compact\handler\impl\json\Json;
use app\AppContext;
use compact\auth\IAuthService;
use compact\utils\ModelUtils;
use compact\validation\ValidationException;
use compact\utils\Random;
use compact\mail\Sendmail;
use compact\mvvm\impl\ViewModel;
use compact\http\HttpSession;
use compact\logging\Logger;

class UserController
{

    /**
     *
     * @var \compact\auth\IAuthService
     */
    private $auth;

    private $db;

    public function __construct()
    {
        $this->auth = Context::get()->getService(Context::SERVICE_AUTH);
        assert('$this->auth instanceof \compact\auth\IAuthService');
        
        /* @var $appCtx \app\AppContext */
        $appCtx = Context::get()->getAppContext();
        $this->db = $appCtx->createUserRepository();
        
        // allow CORS
        Context::get()->http()
            ->getResponse()
            ->setCORSHeaders();
    }

    /**
     * Activates a user based on his activation code.
     *
     * @param string $activationCode            
     *
     * @return HttpStatus 200 | 204 //
     *         200 ok when the user has been found and has been activated with a extra location header with the login url
     *         204 no content when no valid user could be found
     */
    public function activate($activationCode)
    {
        $sc = $this->db->createSearchCriteria();
        $sc->where(UserModel::ACTIVATION, $activationCode);
        $sc->where(UserModel::ACTIVE, false);
        
        // see if we can find a user which is not active and has the activation code
        $result = $this->db->search($sc);
        
        if ($result->count() > 0) {
            // get the first user
            $user = $result->offsetGet(0);
            
            // ok, found a user. Now activate...
            $user->{UserModel::ACTIVATION} = "";
            $user->{UserModel::ACTIVE} = true;
            
            $this->db->save($user);
            
            return new HttpStatus(200, null, array('location' => Context::siteUrl() . '/user/login'));
        }
        
        return new HttpStatus(204);
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
     * @return \compact\handler\impl\http\HttpStatus 200 or 204
     */
    public function login()
    {
        $request = Context::get()->http()->getRequest();
        $username = $request->getPost(UserModel::USERNAME);
        $password = sha1($request->getPost(UserModel::PASSWORD));
        
        $user = $this->auth->login($username, $password);
        
        if ($user) {
            $user->{UserModel::PASSWORD} = "";
            return new HttpStatus(HttpStatus::STATUS_200_OK, new Json($user));
        }
        
        return new HttpStatus(HttpStatus::STATUS_204_NO_CONTENT);
    }

    /**
     * Logs the user out of the system
     *
     * @return \compact\handler\impl\http\HttpStatus 200 ok
     */
    public function logout()
    {
        $this->auth->logout();
        return new HttpStatus(200);
    }

    /**
     * Register a new user.
     * This user will be created with active=false and a activation mail will be send to the user.
     * When the activation link has been visited, the user will be activated
     *
     * @return HttpStatus 201 | 204 | 409 | 422 //
     *         201: created with a location header to the new /model/{id} containing the new ID,
     *         204 no content: when no post data available,
     *         409 conflict on double entry
     *         422 Unprocessable Entity on validation errors or on error saving
     */
    public function register()
    {
        $request = Context::get()->http()->getRequest();
        
        $user = ModelUtils::getPost($this->db->getModelConfiguration());
        $user->set(UserModel::IP, $request->getUserIP());
        $user->set(UserModel::ACTIVATION, Random::guid());
        $user->set(UserModel::ACTIVE, 0);
        $user->set(UserModel::ADMIN, 0);
        $user->{UserModel::PASSWORD} = sha1($user->{UserModel::PASSWORD});
        
        // check if user already exists
        $sc = $this->db->createSearchCriteria();
        $sc->where(UserModel::USERNAME, $user->{UserModel::USERNAME});
        $result = $this->db->search($sc);
        
        if ($result->count() > 0){
            return new HttpStatus(409, new Json(array("message"=>"Username already exists")));
        }
        
        // save new user and do some error handling
        try {
            $this->db->save($user);
        } catch (ValidationException $e) {
            return new HttpStatus(422, new Json(array(
                "message" => $e->getMessage()
            )));
        } catch (\PDOException $e) {
            Logger::get()->logWarning("Could not save resource" . $e->getMessage());
            
            return new HttpStatus(422, new Json(array(
                "message" => "Could not save resource"
            )));
        }
        
        Logger::get()->logNormal("Registered new user " . $user->{UserModel::USERNAME} . " with activation code " . $user->{UserModel::ACTIVATION});
        
        // send the user a activation mail
        // TODO enable in production mode
//         $mail = new Sendmail();
//         $mail->to($user->get(UserModel::EMAIL));
//         $mail->from("elgervb@gmail.com", "Links");
//         $mail->subject("Activation link for your links account");
//         $tpl = new ViewModel('activationlink.html');
//         $tpl->{"activationlink"} = Context::siteUrl() . "/user/activate/" . $user->get(UserModel::ACTIVATION);
//         $mail->text($tpl->render());
//         $mail->send();
        $user->{UserModel::PASSWORD} = "";
        return new HttpStatus(200, new Json($user));
    }
}