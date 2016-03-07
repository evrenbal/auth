<?php

namespace Baka\Auth;

use Naruhodo\Models\Users\UserLinkedSources;
use Naruhodo\Models\Users\Users;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

class AuthentificationManager extends \Phalcon\Mvc\Controller
{
    use \Naruhodo\Traits\HttpBehavior;

    /**
     * User login form
     */
    public function loginAction()
    {
        $this->tag->setTitle(_('Login'));

        //if the user submited the form and passes the security check then we go to login
        if ($this->request->isPost() && $this->security->checkToken()) {
            $username = $this->request->getPost('username', 'string');
            $password = $this->request->getPost('password', 'string');
            $admin = $this->request->getPost('site_naruhodo_admin');
            $userIp = $this->request->getClientAddress();
            $remember = 1;

            //Ok let validate user password
            $validation = new Validation();
            $validation->add('username', new PresenceOf(['message' => _('The username is required.')]));
            $validation->add('password', new PresenceOf(['message' => _('The password is required.')]));

            //validate this form for password
            $messages = $validation->validate($_POST);
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
                    return $this->response->redirect();
                } else {
                    return $this->response->redirect($this->userData->getLanguageUrl() . '/user/welcome');
                }

            } catch (\Exception $e) {
                $this->flash->error($e->getMessage());
                return;
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
            $this->validateUrlToken(false);
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

        return $this->response->redirect($language);
    }

    /**
     * User registration form
     */
    public function signupAction()
    {
        $this->tag->setTitle(_('Sign up'));
        //change user template
        $this->view->pick('user/home');

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
        if ($this->security->checkToken()) {
            if ($this->request->isPost()) {
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
                $messages = $validation->validate($_POST);
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->flash->error($message);
                    }

                    //por alguna razon el social connect jode la shit -_-
                    $this->view->setVar('userProfile', $user);

                    //error redirect
                    return $this->dispatcher->forward([
                        'action' => 'home',
                    ]);
                }

                //set language
                $user->language = $this->userData->usingSpanish() ? 'ES' : 'EN';

                //user registration
                if (!$user->signup()) {
                    //messages
                    foreach ($user->getMessages() as $message) {
                        $this->flash->error($message);
                    }

                    //por alguna razon el social connect jode la shit -_-
                    $this->view->setVar('userProfile', $user);

                    //error redirect
                    return $this->dispatcher->forward([
                        'action' => 'home',
                    ]);
                }

                //si es social connect lo registramos con su red social
                if ($socialConnect) {
                    $UserLinkedSources = new UserLinkedSources();
                    $UserLinkedSources->associateAccount($user, $userProfile, $userSocial['site']);
                }

                //user email

                //page confirmation
                if ($this->userData->isLoggedIn()) {
                    return $this->dispatcher->forward([
                        'action' => 'welcome',
                    ]);
                } else {
                    //create a session with the user activation key , to resent the user email if he didnt get it
                    $this->session->set('userRegistrationKey', $user->user_activation_key);
                    $activationUrl = $this->config->application->siteUrl . '/user/activate/' . $user->user_activation_key;

                    //user registration send email
                    $email = [
                        'subject' => 'Signup complete',
                        'to' => [$user->email => $user->displayname],
                        'body' => sprintf(_('Thank you for signing up in Naruho.do, use this link to activate your account: %sActivate account%s'), '<a href="' . $activationUrl . '">', '</a>'),
                        'icon' => 'simley03',
                    ];

                    $this->queue->putInTube(EMAIL_QUEUE, $email);

                    return $this->response->redirect($this->userData->getLanguageUrl() . '/user/activate/' . $user->user_activation_key);
                }
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

            $messages = $validation->validate($_POST);
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

                $recoveryLink = $this->config->application->siteUrl . '/user/reset/' . $recoverUser->user_activation_forgot;
                $recoveryLink = '<a href="' . $recoveryLink . '>' . _('here') . '</a>';

                $email = [
                    'subject' => _('Password Recovery'),
                    'to' => [$recoverUser->email => $recoverUser->displayname],
                    'body' => sprintf(_('Click %shere%s to set a new password for your account.'), '<a href="' . $recoveryLink . '" target="_blank">', '</a>'),
                    'icon' => 'simley03',

                ];

                $this->queue->putInTube(EMAIL_QUEUE, $email);

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
                "action" => "route401",
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
            $messages = $validation->validate($_POST);
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
                $userData->password = Users::passwordHash($newPassword);

                // Update
                if ($userData->update()) {
                    //log the user out of the site from all devices
                    $userData->cleanSession();

                    $this->view->setVar('changedPassword', true);
                    $activationUrl = $this->config->application->siteUrl . '/user/activate/' . $user->user_activation_key;

                    // Send email
                    $email = [
                        'subject' => _('Signup complete!'),
                        'to' => [$user->email => $user->displayname],
                        'body' => sprintf(_('Thank you for signing up in Naruho.do, use this link to activate your account: %sActivate account%s'), '<a href="' . $activationUrl . '">', '</a>'),
                        'icon' => 'simley03',
                    ];

                    $this->queue->putInTube(EMAIL_QUEUE, $emailMessage);

                    return;
                    //$this->flash->success(_('Congratulations! You\'ve successfully changed your password.'));
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
            $session = new \Naruhodo\Models\Sessions\Sessions();
            $userSession = $session->session_begin($user->user_id, $userIp, PAGE_INDEX, false, $remember, $admin);

            return $this->response->redirect($this->userData->getLanguageUrl() . '/user/welcome');
        }

        //die('Thanks you for registration to naruho.do');
        if ($this->request->isPost() && $this->security->checkToken()) {
            //user registration send email
            //$token =  $this->session->get('userRegistrationKey');
            if ($user) // = Users::findFirstByUser_activation_key($token))
            {
                $activationUrl = $this->config->application->siteUrl . '/user/activate/' . $user->user_activation_key;
                //user registration send email
                $email = [
                    'subject' => _('Signup complete!'),
                    'to' => [$user->email => $user->displayname],
                    'body' => sprintf(_('Thank you for signing up in Naruho.do, use this link to activate your account: %sActivate account%s'), '<a href="' . $activationUrl . '">', '</a>'),
                    'icon' => 'simley03',
                ];

                $this->flash->success(_('Please check your email inbox to complete the password recovery.'));
                $this->queue->putInTube(EMAIL_QUEUE, $email);

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
                "action" => "route401",
            ]);
        }

        // ok so the key exist, now is the user is not active?
        if (!$userData->isActive()) {
            //activate it
            $userData->user_active = '1';
            $userData->user_activation_key = ' ';
            $userData->update();

            $this->flash->success(_('User has been successfully registered and activated.'));
            $this->flash->notice(_('Please complete the Welcome process to get you started!'));

            //login the user and send them to welcome
            $session = new \Naruhodo\Models\Sessions\Sessions();
            $userIp = $this->request->getClientAddress();
            $session->session_begin($userData->user_id, $userIp, PAGE_INDEX, false, true, false);

            //now login and go to welcome page
            return $this->response->redirect($this->userData->getLanguageUrl() . '/user/welcome');

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
                "action" => "route401",
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
                $UserLinkedSources = new \Naruhodo\Models\Users\UserLinkedSources();

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

            return $this->response->redirect('/user/sign-up');
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
            case 'add_rss':
            case 'add_serie':
            case 'list_import':
            case 'logout':

                //if the user is not logged in, take them out
                if (!$this->userData->isLoggedIn()) {
                    //no lo encontramos pagina de error
                    return $this->dispatcher->forward([
                        "controller" => 'index',
                        "action" => "route401",
                    ]);
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
                    return $this->dispatcher->forward([
                        "controller" => 'index',
                        "action" => "route401",
                    ]);
                }
                break;

            case 'addFriend':
            case 'acceptFriend':
            case 'removeFriend':
            case 'cancelFriend':
            case 'followUser':
            case 'removeFollower':
                $this->validateUrlToken();
                break;
        }
    }
}
