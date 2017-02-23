<?php

namespace Baka\Auth;

use Baka\Auth\Models\UserLinkedSources;
use Baka\Auth\Models\Users;
use Exception;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

abstract class AuthentificationManager extends \Phalcon\Mvc\Controller
{
    protected $successLoginRedirect = '/';
    protected $successLogoutRedirect = '/';
    protected $successLoginRedirectNoWelcome = '/users/welcome';
    protected $successRegistrationRedirectAction = 'welcome';
    protected $failedRegistrationRedirectAction = 'welcome';
    protected $failedActivationRedirectAction = '404';

    /**
     * Home action
     */
    public function homeAction()
    {
        $this->tag->setTitle(_('Sign up'));
        $this->view->pick("signup");

        $this->persistent->parameters = null;
    }

    /**
     * User login form
     */
    public function loginAction()
    {
        $this->tag->setTitle(_('Login'));

        //if the user submited the form and passes the security check then we go to login
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $username = $this->request->getPost('username', 'string');
                $password = $this->request->getPost('password', 'string');
                $admin = $this->request->getPost('site_baka_admin');
                $userIp = $this->request->getClientAddress();
                $remember = 1;

                //Ok let validate user password
                $validation = new Validation();
                $validation->add('username', new PresenceOf(['message' => _('The username is required.')]));
                $validation->add('password', new PresenceOf(['message' => _('The password is required.')]));

                //validate this form for password
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->flash->error($message);
                    }
                    return;
                }

                //login the user
                try
                {
                    $userData = Users::login($username, $password, $remember, $admin, $userIp);

                    //did the user complete the welcome page?
                    if ($this->userData->welcome) {
                        return $this->response->redirect($this->successLoginRedirect);
                    } else {
                        return $this->response->redirect($this->userData->getLanguageUrl() . $this->successLoginRedirectNoWelcome);
                    }

                } catch (\Exception $e) {
                    $this->flash->error($e->getMessage());
                    return;
                }
            } else {
                $this->flash->error('Token Error');
            }
        }
    }

    /**
     * user logout function
     *
     * @return  voide
     */
    public function logoutAction()
    {
        if ($this->userData->isLoggedIn()) {

            //validate the user is logingout
            //$this->validateUrlToken(false);
            $language = $this->userData->getLanguageUrl();
            $this->userData->logOut();

            try
            {
                /**
                 * for now the logout of our social connect wont work, cause they havent finish the shit
                 * so let delete all the sessions
                 */
                unset($_SESSION['HA::STORE']);
                unset($_SESSION['HA::CONFIG']);
                //$this->userData->disconnectSocialProfile();
                //$this->userData->disconnectSocialProfile('twitter');
                //$this->userData->disconnectSocialProfile('google');
            } catch (\Exception $e) {
                //do nothing if we get exceptions
            }
        }

        return $this->response->redirect($this->successLogoutRedirect);
    }

    /**
     * User registration form
     */
    public function signupAction()
    {
        //si existe ya la session de social connect significa que vienes de una cuenta de connect social
        if ($socialConnect = is_array($this->session->get('socialConnect'))) {
            $userSocial = $this->session->get('socialConnect');

            $userProfile = Users::getSocialProfile($userSocial['site']);

            //si esta cuenta ya esta linked te logeamos
            $UserLinkedSources = new UserLinkedSources();
            $UserLinkedSources->existSocialProfile($userProfile, $userSocial['site']);

            $this->view->setVar('userProfile', $userProfile);
            $this->view->setVar('socialConnect', true);
        }

        //token_name(token)
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $user = new Users();

                $user->email = $this->request->getPost('email', 'email');
                $user->password = ltrim(trim($this->request->getPost('password', 'string')));
                $user->displayname = ltrim(trim($this->request->getPost('displayname', 'string')));
                // $user->profile_image = $this->request->getPost('profile_image', 'string');

                //Ok let validate user password
                $validation = new Validation();
                $validation->add('password', new PresenceOf(['message' => _('The password is required.')]));
                $validation->add('email', new EmailValidator(['message' => _('The email is not valid.')]));

                $validation->add('password',
                    new StringLength([
                        'min' => 8,
                        'messageMinimum' => _('Password is too short. Minimum 8 characters.'),
                    ])
                );

                //validate this form for password
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->flash->error($message);
                    }

                    //por alguna razon el social connect jode la shit -_-
                    $this->view->setVar('userProfile', $user);

                    //error redirect
                    return $this->dispatcher->forward([
                        'action' => $this->failedRegistrationRedirectAction,
                    ]);
                }

                //set language
                $user->language = $this->userData->usingSpanish() ? 'ES' : 'EN';

                //user registration
                try {

                    $user->signup();

                    //si es social connect lo registramos con su red social
                    if ($socialConnect) {
                        $UserLinkedSources = new UserLinkedSources();
                        $UserLinkedSources->associateAccount($user, $userProfile, $userSocial['site']);
                    }

                } catch (Exception $e) {

                    $this->flash->error($e->getMessage());

                    //por alguna razon el social connect jode la shit -_-
                    $this->view->setVar('userProfile', $user);

                    //error redirect
                    return $this->dispatcher->forward([
                        'action' => $this->failedRegistrationRedirectAction,
                    ]);
                }

                //page confirmation
                if ($this->userData->isLoggedIn()) {
                    return $this->dispatcher->forward([
                        'action' => $this->successRegistrationRedirectAction,
                    ]);
                } else {
                    //create a session with the user activation key , to resent the user email if he didnt get it
                    $this->session->set('userRegistrationKey', $user->user_activation_key);
                    $activationUrl = $this->config->application->siteUrl . '/' . $this->router->getControllerName() . '/activate/' . $user->user_activation_key;

                    //user registration send email
                    $this->sendEmail('signup', $user);

                    return $this->response->redirect('/' . $this->router->getControllerName() . '/activate/' . $user->user_activation_key);
                }
            } else {
                $this->flash->error('Token Error');
            }
        }
    }

    /**
     * Recover user information
     *
     * @return void
     */
    public function recoverAction()
    {
        //if the user submited the form and passes the security check then we start checking
        if ($this->request->isPost() && $this->security->checkToken()) {
            $email = $this->request->getPost('email', 'email');

            $validation = new Validation();
            $validation->add('email', new PresenceOf(['message' => _('The email is required.')]));
            $validation->add('email', new EmailValidator(['message' => _('The email is invalid.')]));

            $messages = $validation->validate($this->request->getPost());
            if (count($messages)) {
                foreach ($messages as $message) {
                    $this->flash->error($message);
                }

                return;
            }

            /**
             * check if the user email exist
             * if it does creat the user activation key to send
             * send the user email
             *
             * if it doesnt existe then send the erro msg
             */
            if ($recoverUser = $this->userData->getByEmail($email)) {
                $recoverUser->user_activation_forgot = $recoverUser->generateActivationKey();
                $recoverUser->update();

                $this->flash->success(_('Please check your email inbox to complete the password recovery.'));

                $recoveryLink = $this->config->application->siteUrl . '/users/reset/' . $recoverUser->user_activation_forgot;
                $recoveryLink = '<a href="' . $recoveryLink . '">' . _('here') . '</a>';

                $this->sendEmail('recover', $recoverUser);

                return;
            } else {
                $this->flash->error(_('There is no account registered with that email.'));
                return;
            }
        }
    }

    /**
     * Reset the user password
     * @return void
     */
    public function resetAction($key)
    {
        //is the key empty or does it existe?
        if (empty($key) || !$userData = Users::findFirst(['user_activation_forgot = :key:', 'bind' => ['key' => $key]])) {
            return $this->dispatcher->forward([
                "controller" => 'index',
                "action" => $this->failedActivationRedirectAction,
            ]);
        }

        $this->view->setVar('profileData', $userData);

        //if summit to change password
        if ($this->security->checkToken() && $this->request->isPost()) {
            // Get the new password and the verify
            $newPassword = trim($this->request->getPost('new_password', 'string'));
            $verifyPassword = trim($this->request->getPost('verify_password', 'string'));

            //Ok let validate user password
            $validation = new Validation();
            $validation->add('new_password', new PresenceOf(['message' => _('The password is required.')]));
            $validation->add('new_password', new StringLength(['min' => 8, 'messageMinimum' => _('Password is too short. Minimum 8 characters.')]));

            $validation->add('new_password', new Confirmation(array(
                'message' => _('Passwords do not match.'),
                'with' => 'verify_password',
            )));

            //validate this form for password
            $messages = $validation->validate($this->request->getPost());
            if (count($messages)) {
                foreach ($messages as $message) {
                    $this->flash->error($message);
                }

                return;
            }

            // Check that they are the same
            if ($newPassword == $verifyPassword) {

                // Has the password and set it
                $userData->user_activation_forgot = '';
                $userData->user_active = 1;
                $userData->password = Users::passwordHash($newPassword);

                // Update
                if ($userData->update()) {
                    //log the user out of the site from all devices
                    $userData->cleanSession();

                    $this->view->setVar('changedPassword', true);
                    $activationUrl = $this->config->application->siteUrl . '/users/activate/' . $user->user_activation_key;

                    $this->sendEmail('reset', $userData, $activationUrl);

                    $this->flash->success(_('Congratulations! You\'ve successfully changed your password.'));
                    return;

                } else {
                    foreach ($userData->getMessages() as $message) {
                        return $this->flash->error($message);
                    }

                }
            } else {
                return $this->flash->error(_('Passwords do not match.'));
            }
        }
    }

    /**
     * User thank you page after registration telling him to wait for email activation
     */
    public function thankyouAction()
    {
        $token = $this->session->get('userRegistrationKey');
        $user = Users::findFirstByUser_activation_key($token);

        //log he in, since he came from social netowkr
        if ($user->user_active) {
            $admin = $user->isAdmin();
            $userIp = $this->request->getClientAddress();
            $remember = 1;

            //login the user , so we just create the user session base on the user object
            $session = new \Auth\Models\Sessions();
            $userSession = $session->begin($user->getId(), $userIp, PAGE_INDEX, false, $remember, $admin);

            return $this->response->redirect($this->successLoginRedirectNoWelcome);
        }

        //die('Thanks you for registration to naruho.do');
        if ($this->request->isPost() && $this->security->checkToken()) {
            //user registration send email
            //$token =  $this->session->get('userRegistrationKey');
            if ($user) // = Users::findFirstByUser_activation_key($token))
            {
                $activationUrl = $this->config->application->siteUrl . '/users/activate/' . $user->user_activation_key;

                //user registration send email
                $this->flash->success(_('Please check your email inbox to complete the password recovery.'));

                $this->sendEmail('thankyou', $user);

                return;
            }
        }
    }

    /**
     * User activation from the email signup
     * @return void
     */
    public function activateAction($key = null)
    {
        $userData = Users::findFirst(['user_activation_key = :key:', 'bind' => ['key' => $key]]);
        //is the key empty or does it existe?
        if (empty($key) || !$userData) {
            //no lo encontramos pagina de error
            return $this->dispatcher->forward([
                "controller" => 'index',
                "action" => $this->failedActivationRedirectAction,
            ]);
        }

        // ok so the key exist, now is the user is not active?
        if (!$userData->isActive()) {
            //activate it
            $userData->user_active = '1';
            $userData->user_activation_key = ' ';
            $userData->update();

            $this->flash->success(_('User has been successfully registered and activated.'));

            //login the user and send them to welcome
            $session = new \Baka\Auth\Models\Sessions();
            $userIp = $this->request->getClientAddress();
            $session->begin($userData->getId(), $userIp, getenv('PAGE_INDEX'), false, true, 0);

            //now login and go to welcome page
            return $this->response->redirect($this->successLoginRedirectNoWelcome);

        } elseif ($userData->isActive()) {
            //wtf? are you doing here and still with an activation key?
            $userData->user_activation_key = '';
            $userData->update();

            //now go to welcome
            return $this->response->redirect();
        } else {
            //no lo encontramos pagina de error
            return $this->dispatcher->forward([
                "controller" => 'index',
                "action" => $this->failedActivationRedirectAction,
            ]);
        }
    }

    /**
     * Social registration (FB, TW, Google)
     * @return void
     */
    public function socialAction($site = null)
    {
        try {
            // request user profile
            $userProfile = Users::getSocialProfile($site);

            if (is_object($userProfile)) {
                //si esta cuenta ya esta linked te logeamos
                $UserLinkedSources = new \Baka\Auth\Models\UserLinkedSources();

                //if you already are a existing social profile , if not we send you to signup
                if ($UserLinkedSources->existSocialProfile($userProfile, $site)) {
                    return $this->response->redirect();
                }

                $this->session->set('socialConnect', ['site' => $site, 'enable' => true]);
            }

            $this->flash->success(sprintf(_('You are now connected with %s. Please finish filling the form to complete the registration process.'), ucfirst($site)));

            $this->dispatcher->forward(['action' => 'signup']);
            // user profile
            //echo '<pre>' . print_r( $userProfile, true ) . '</pre>';
            //$socialRegistration = new \Naruhodo\Models\UserLinkedSources();
            //$socialRegistration->linkAccount($userProfile, $site);

            //echo $adapter->debug();

            // echo 'Logging out..';
            //$adapter->disconnect();
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
            $this->session->remove('socialConnect');
            $this->flash->error(sprintf(_('There was a communication error with %s. Please try again later or connect with another service.'), ucfirst($site)));

            return $this->response->redirect('/users/sign-up');
            //no lo encontramos pagina de error
            /* return $this->dispatcher->forward([
        'controller' => 'index',
        'action' => 'route404',
        ]); */
        }
    }

    /**
     * User welcome screen page
     *
     * En esta pantalla asociamos los animes / mangas que nos pueden gustar , trende del sistema
     *
     * @TODO wtf $limit
     */
    public function welcomeAction($section = null)
    {
        $this->flash->notice(_('Please complete the Welcome process to get you started!'));
    }

    /**
     * Set the email config array we are going to be sending
     *
     * @param String $emailAction
     * @param Users  $user
     */
    protected function sendEmail($emailAction, Users $user, $msg = null)
    {
        /* $email = [
        'subject' => 'Signup complete',
        'to' => [$user->email => $user->displayname],
        'body' => sprintf(_('Thank you for signing up in Naruho.do, use this link to activate your account: %sActivate account%s'), '<a href="' . $activationUrl . '">', '</a>'),
        'icon' => 'simley03',
        ];*/

        return [];
    }

    /**
     * social connect callback page
     * @return void
     */
    public function social_authAction()
    {
        //$config = dirname(dirname( __FILE__ )) . "/config/social_config.php";
        $hybridauth = new \Hybridauth\Hybridauth($this->config->social_config->toArray());

        $endpoint = new \Hybridauth\Endpoint();
        $endpoint->process();
    }

    /**
     * Framework function that executes befor the route, to check if the user is looged in or not, on the especify sections
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        //which section are we going to validate user authentification

        switch ($dispatcher->getActionName()) {
            case 'welcome':
            case 'logout':

                //if the user is not logged in, take them out
                if (!$this->userData->isLoggedIn()) {
                    //no lo encontramos pagina de error
                    return $this->response->redirect($this->failedActivationRedirectAction);

                }

                break;

            case 'thank-you':
            case 'thankyou':
            case 'activate':
            case 'recover':
            case 'reset':
            case 'login':
            case 'home':
            case 'signup':
            case 'sign-up':

                //if the user is logged in, take them out
                if ($this->userData->isLoggedIn()) {
                    //no lo encontramos pagina de error
                    return $this->response->redirect($this->successLoginRedirect);
                }
                break;
        }
    }
}
