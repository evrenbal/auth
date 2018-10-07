<?php

/**
 * phpbb Session Mnager
 *
 * Manejador de session de phpbbb.
 * Con esta clase integras cualquier aplicacion con tu phpbb foro
 *
 * Ejemplo:
 *
 *      $phpbbSession->start();
 *
 * La variable phpbbusers entonces te da la informacion del usuario (un arreglo de la tabla phpbb_users)
 *
 * @author Kaioken / phpbbTeam
 * @package phpbb
 *
 */

namespace Baka\Auth\Models;

use Baka\Database\Model;
use \Exception;
use \Phalcon\DI;

class Sessions extends Model
{
    /**
     * @var string
     */
    public $sessionId;

    /**
     * @var integer
     */
    public $users_id;

    /**
     * @var string
     */
    public $start;

    /**
     * @var integer
     */
    public $time;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $page;

    /**
     * @var string
     */
    public $logged_in;

    /**
     * @var string
     */
    public $is_admin;

    /**
     * almecenamos la info del usuario par ahacer singlation
     *
     * @var phpbbSession
     */
    public static $userData = null;

    public $config;

    /**
     *
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'userData']);
    }

    //
    // Adds/updates a new session to the database for the given userid.
    // Returns the new session ID on success.
    //
    public function begin($users_id, $userIp, $page_id, $auto_create = 0, $enable_autologin = 0, $admin = 0)
    {
        $cookieName = $this->config->cookie_name;
        $cookiePath = $this->config->cookie_path;
        $cookieDomain = $this->config->cookie_domain;
        $cookieSecure = $this->config->cookie_secure;

        if (isset($_COOKIE[$cookieName . '_sid']) || isset($_COOKIE[$cookieName . '_data'])) {
            $sessionId = isset($_COOKIE[$cookieName . '_sid']) ? $_COOKIE[$cookieName . '_sid'] : '';
            $sessionData = isset($_COOKIE[$cookieName . '_data']) ? @unserialize(stripslashes($_COOKIE[$cookieName . '_data'])) : array();
            $sessionmethod = getenv('SESSION_METHOD_COOKIE');
        } else {
            $sessionData = array();
            $sessionId = (isset($_GET['sid'])) ? $_GET['sid'] : '';
            $sessionmethod = getenv('SESSION_METHOD_GET');
        }

        //
        if (!preg_match('/^[A-Za-z0-9]*$/', $sessionId)) {
            $sessionId = '';
        }

        $page_id = (int) $page_id;

        $last_visit = 0;
        $currentTime = time();

        //
        // Are auto-logins allowed?
        // If allow_autologin is not set or is true then they are
        // (same behaviour as old 2.0.x session code)
        //
        if (isset($this->config->allow_autologin) && !$this->config->allow_autologin) {
            $enable_autologin = $sessionData['autologinid'] = false;
        }

        //
        // First off attempt to join with the autologin value if we have one
        // If not, just use the users_id value
        //
        $userData = array();

        if ($users_id != getenv('ANONYMOUS')) {
            if (isset($sessionData['autologinid']) && (string) $sessionData['autologinid'] != '' && $users_id) {
                $sql = "SELECT u.*
                    FROM Baka\Auth\Models\Users u, Baka\Auth\Models\SessionKeys k
                    WHERE u.id =  :users_id:
                        AND u.user_active = 1
                        AND k.users_id = u.id
                        AND k.session_id = :session_id: ";

                $sessionId = $sessionData['autologinid'];

                $result = $this->getModelsManager()->createQuery($sql);
                $result = $result->execute([
                    'users_id' => $users_id,
                    'session_id' => $sessionId,
                ]);

                $userData = $result->toArray()[0];
                $userInfo = Users::getById($users_id, true);
                $enable_autologin = $login = 1;
            } else if (!$auto_create) {
                $sessionData['autologinid'] = '';
                $sessionData['userid'] = $users_id;

                //the user information
                $userInfo = Users::getById($users_id, true);

                //this is stupid, but bare with me, this use to be phpbb -_-
                $userData = $userInfo->isActive() ? $userInfo->toArray() : null;

                $login = 1;
            }
        }

        //removemos variables que no se necesitan
        unset($userData['password']);
        unset($userData['user_level']);
        unset($userData['user_active']);

        $userIsActive = isset($userInfo) && is_object($userInfo) ? $userInfo->isActive() : false;

        //
        // At this point either $userData should be populated or
        // one of the below is true
        // * Key didn't match one in the DB
        // * User does not exist
        // * User is inactive
        //
        //if (!sizeof($userData) || !is_array($userData) || !$userData)
        if (!$userIsActive || !is_array($userData) || !$userData) {
            $sessionData['autologinid'] = '';
            $sessionData['userid'] = $users_id = getenv('ANONYMOUS');
            $enable_autologin = $login = 0;

            $userInfo = Users::getById($users_id, true);
            $userData = $userInfo->toArray();

        }

        //
        // Initial ban check against user id, IP and email address
        //
        preg_match('/(..)(..)(..)(..)/', $userIp, $userIp_parts);

        $sql = "SELECT ip, users_id, email
            FROM  Baka\Auth\Models\Banlist
            WHERE ip IN ('" . $userIp_parts[1] . $userIp_parts[2] . $userIp_parts[3] . $userIp_parts[4] . "', '" . $userIp_parts[1] . $userIp_parts[2] . $userIp_parts[3] . "ff', '" . $userIp_parts[1] . $userIp_parts[2] . "ffff', '" . $userIp_parts[1] . "ffffff')
                OR users_id = $users_id";

        if ($users_id != getenv('ANONYMOUS')) {
            $sql .= " OR email LIKE '" . str_replace("\'", "''", $userInfo->email) . "'
                OR email LIKE '" . substr(str_replace("\'", "''", $userInfo->email), strpos(str_replace("\'", "''", $userInfo->email), "@")) . "'";
        }

        $result = $this->getModelsManager()->createQuery($sql);
        $result = $result->execute();

        //user ban info
        $banData = $result->toArray();
        $banInfo = count($banData) > 0 ? $banData[0] : null;

        if ($banInfo) {
            if ($banInfo['ip'] || $banInfo['users_id'] || $banInfo['email']) {
                throw new Exception(_('This account has been banned. Please contact the administrators.'));
            }
        }

        /**
         * Create or update the session
         * @todo we dont need a new session for every getenv('ANONYMOUS') user, use less , right now 27.7.15 90% of the sessions are for that type of users
         */
        $session = new self();
        $session->users_id = $users_id;
        $session->start = $currentTime;
        $session->time = $currentTime;
        $session->page = $page_id;
        $session->logged_in = $login;
        $session->is_admin = $admin;
        $session->session_id = $sessionId;
        $session->ip = $userIp;

        //if it didnt update then we create the session
        if (!$session->update()) {
            $sessionId = \Phalcon\Text::random(\Phalcon\Text::RANDOM_ALNUM, 45);
            $session->session_id = $sessionId;

            //create
            $session->save();
        }

        if ($users_id != getenv('ANONYMOUS')) {
            $last_visit = ($userInfo->session_time > 0) ? $userInfo->session_time : $currentTime;

            if (!$admin) {
                //update user info
                $userInfo->session_time = $currentTime;
                $userInfo->session_page = $page_id;
                $userInfo->lastvisit = date('Y-m-d H:i:s', $last_visit);
                $userInfo->update();
            }

            $userData['user_lastvisit'] = $last_visit;

            //
            // Regenerate the auto-login key
            //
            if ($enable_autologin) {
                $auto_login_key = \Phalcon\Text::random(\Phalcon\Text::RANDOM_ALNUM, 45);

                if (isset($sessionData['autologinid']) && (string) $sessionData['autologinid'] != '') {
                    $sessionId2 = $sessionData['autologinid'];

                    //problems with the update =_= dont know why, so we are right now deleting and saving.
                    //fucking sucks balzz

                    //borramos la vieja
                    $sessionKey = new SessionKeys();
                    $session->session_id = $sessionId2;
                    $session->users_id = $users_id;
                    $session->delete();

                    //creamos la nueva
                    $session = new SessionKeys();
                    $session->session_id = $auto_login_key;
                    $session->users_id = $users_id;
                    $session->last_ip = $userIp;
                    $session->last_login = $currentTime;
                    $session->save();
                } else {
                    $session = new SessionKeys();
                    $session->session_id = $auto_login_key;
                    $session->users_id = $users_id;
                    $session->last_ip = $userIp;
                    $session->last_login = $currentTime;
                    $session->save();
                }

                $sessionData['autologinid'] = $auto_login_key;
                unset($auto_login_key);
            } else {
                $sessionData['autologinid'] = '';
            }

            //$sessionData['autologinid'] = (!$admin) ? (( $enable_autologin && $sessionmethod == getenv('SESSION_METHOD_COOKIE') ) ? $auto_login_key : '') : $sessionData['autologinid'];
            $sessionData['userid'] = $users_id;
        }

        $userData['session_id'] = $sessionId;
        $userData['session_ip'] = $userIp;
        $userData['session_users_id'] = $users_id;
        $userData['session_logged_in'] = $login;
        $userData['session_page'] = $page_id;
        $userData['session_start'] = $currentTime;
        $userData['session_time'] = $currentTime;
        $userData['session_admin'] = $admin;
        $userData['session_key'] = $sessionData['autologinid'];

        $cookieExpire = $currentTime + (($this->config->max_autologin_time) ? 86400 * (int) $this->config->max_autologin_time : 31536000);

        //le agregmaos el cookie domain
        setcookie($cookieName . '_data', serialize($sessionData), $currentTime + 31536000, (string) $cookiePath, (string) $cookieDomain, (int) $cookieSecure, 1);
        setcookie($cookieName . '_sid', $sessionId, $cookieExpire, (string) $cookiePath, (string) $cookieDomain, (int) $cookieSecure, 1);

        // Add the session_key to the userdata array if it is set
        if (isset($sessionData['autologinid']) && !empty($sessionData['autologinid'])) {
            $userInfo->session_key = $sessionData['autologinid'];
            $userInfo->session_id = !isset($sessionData['session_id']) ? $userData['session_id'] : $sessionData['session_id'];
        }

        //is user online?
        if ($users_id != getenv('ANONYMOUS')) {
            $userInfo->loggedIn = true;
        }

        //$SID = 'sid=' . $sessionId;
        return $userInfo;
    }

    //
    // Checks for a given user session, tidies session table and updates user
    // sessions at each page refresh
    //
    public function start(Users $user, string $sessionId, string $userIp, int $pageId)
    {
        $currentTime = time();

        $pageId = (int) $pageId;

        //
        // Does a session exist?
        //
        if (!empty($sessionId)) {
            //
            // session_id exists so go ahead and attempt to grab all
            // data in preparation
            //
            $sql = "SELECT user.*, session.*
                FROM Baka\Auth\Models\Sessions session, Baka\Auth\Models\Users user
                WHERE session.session_id = :session_id:
                    AND user.id = session.users_id";

            $result = $this->getModelsManager()->createQuery($sql);
            $result = $result->execute([
                'session_id' => $sessionId,
            ]);

            //session data
            $userData = $result->getFirst();

            //
            // Did the session exist in the DB?
            //
            if ($userData->user) {
                //
                // Do not check IP assuming equivalence, if IPv4 we'll check only first 24
                // bits ... I've been told (by vHiker) this should alleviate problems with
                // load balanced et al proxies while retaining some reliance on IP security.
                /**
                 * @todo reviar esto del chekeo de las ips
                 */
                $ip_check_s = substr($userData->session->ip, 0, 6);
                $ip_check_u = substr($userIp, 0, 6);

                if ($ip_check_s == $ip_check_u) {
                    //$SID = ($sessionmethod == getenv('SESSION_METHOD_GET') || defined('IN_ADMIN')) ? 'sid=' . $sessionId : '';

                    //
                    // Only update session DB a minute or so after last update
                    //
                    if ($currentTime - $userData->session->time > 60) {

                        //update the user session
                        $session = new self();
                        $session->session_time = $currentTime;
                        $session->session_page = $pageId;
                        $session->session_id = $userData->session->session_id;

                        // A little trick to reset session_admin on session re-usage
                        if (!defined('IN_ADMIN') && $currentTime - $userData->session->time > ($this->config->session_length + 60)) {
                            $session->session_admin = 0;
                        }

                        $session->update();

                        //if it not getenv('ANONYMOUS')
                        if ($userData->user->getId() != getenv('ANONYMOUS')) {

                            //update the user info of current session
                            $user = new Users();
                            $user->users_id = $userData->user->getId();
                            $user->session_time = $currentTime;
                            $user->session_page = $pageId;
                            $user->update();
                        }

                        $this->clean($userData->session->session_id);
                      
                    }

                    $userInfo = $userInfo->getById($userData->user->getId());

                    $userInfo->session_id = $userData->session->session_id;

                    return $userInfo;
                }
            }
        }

        //
        // If we reach here then no (valid) session exists. So we'll create a new one,
        // using the cookie users_id if available to pull basic user prefs.
        //
        $users_id = (isset($sessionData['userid'])) ? intval($sessionData['userid']) : getenv('ANONYMOUS');

        if (!($userData = $this->begin($users_id, $userIp, $pageId, true))) {
            throw new \Exception(_('Error while creating session.'));
        }

        //son lo mismo -_-
        $userInfo = $userData; //$userInfo->getById($userData->users_id);

        //if not getenv('ANONYMOUS') user online
        if ($userInfo->getId() != getenv('ANONYMOUS')) {
            $userInfo->loggedIn = true;
        }

        //$userInfo->session_id = $userInfo->session->session_id;

        return $userInfo;

    }

    /**
     * Removes expired sessions and auto-login keys from the database
     */
    public function clean($sessionId, $daemon = false)
    {
        //we sent the session id to the seassion daemon cleaner
        if (!$daemon) {
            $queue = $this->getDI()->getQueue();
            $queue->putInTube(getenv('SESSION_QUEUE'), $sessionId);

            return true;
        }

        $cache = $this->getDI()->getRedis();
        //hacemos un gb para las session, implementacio n de la version 3 para eliminar tantas sessiones
        //esto se debe cambiar luego por su propia funcion, es un simple workaround a nuestro problema por ahora
        if (time() - 3600 > (int) $cache->get("last_session_gc") && !$cache->get('session_gc_running')) {
            //estamos corriendo este preoceso y solo 1 usuario lo puede correr
            //apc_store("session_gc_running", true, 0);
            $cache->set("session_gc_running", true);

            $sql = 'DELETE FROM Baka\Auth\Models\Sessions
                        WHERE users_id = ' . getenv('ANONYMOUS') . '
                            AND time < ' . (int) (time() - 3600);

            $result = $this->getModelsManager()->executeQuery($sql);

            $cache->set("last_session_gc", time());
            $cache->set("session_gc_running", false);
        }

        //
        // Delete expired sessions
        //
        $sql = "DELETE FROM  Baka\Auth\Models\Sessions
            WHERE time < :session_time:
                AND session_id <> :session_id: ";

        $session_time = time() - (int) $this->config->session_length;

        $params = [
            'session_time' => $session_time,
            'session_id' => $sessionId,
        ];

        $result = $this->getModelsManager()->executeQuery($sql, $params);

        //
        // Delete expired auto-login keys
        // If max_autologin_time is not set then keys will never be deleted
        // (same behaviour as old 2.0.x session code)
        //
        if ($this->config->max_autologin_time && $this->config->max_autologin_time > 0) {
            $sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
                WHERE last_login < :last_login: ';

            $last_login = time() - (2 * (int) $this->config->max_autologin_time);

            $params = ['last_login' => $last_login];

            $result = $this->getModelsManager()->executeQuery($sql, $params);
        }

        return true;
    }

    /**
     * Terminates the specified session
     * It will delete the entry in the sessions table for this session,
     * remove the corresponding auto-login key and reset the cookies
     */
    public function end(Users $userData)
    {
        $cookieName = $this->config->cookie_name;
        $cookiePath = $this->config->cookie_path;
        $cookieDomain = $this->config->cookie_domain;
        $cookieSecure = $this->config->cookie_secure;

        $currentTime = time();

        if (!preg_match('/^[A-Za-z0-9]*$/', $userData->session_id)) {
            return;
        }

        //
        // Delete existing session
        //
        $session = new self();
        $session->session_id = $userData->session_id;
        $session->users_id = $userData->getId();
        $session->delete();

        //
        // Remove this auto-login entry (if applicable)
        //
        if (isset($userData->session_key) && $userData->session_key != '') {
            $sessionKey = new SessionKeys();
            $sessionKey->users_id = $userData->getId();
            $sessionKey->session_id = $userData->session_key;
            $sessionKey->delete();
        }

        //
        // We expect that message_die will be called after this function,
        // but just in case it isn't, reset $userData to the details for a guest
        //
        $userData = $userData->getById(getenv('ANONYMOUS'));

        setcookie($cookieName . '_data', '', $currentTime - 31536000, (string) $cookiePath, (string) $cookieDomain, (int) $cookieSecure, 1);
        setcookie($cookieName . '_sid', '', $currentTime - 31536000, (string) $cookiePath, (string) $cookieDomain, (int) $cookieSecure, 1);

        return true;
    }
}
