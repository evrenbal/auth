<?php

/**
 * JWT Session Token Manager
 */

namespace Baka\Auth\Models;

use Baka\Database\Model;
use Exception;

class Sessions extends Model
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var integer
     */
    public $users_id;

    /**
     * @var string
     */
    public $token;

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
     * Initialize
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
        $this->hasMany('id', 'Baka\Auth\Models\Users', 'sessions_id', ['alias' => 'sessionKeys']);
    }

    /**
     * Create a new session token for the given users, to track on the db
     *
     * @param Users $user
     * @param string $sessionId
     * @param string $token
     * @param string $userIp
     * @param integer $pageId
     * @return Users
     */
    public function start(Users $user, string $sessionId, string $token, string $userIp, int $pageId): Users
    {
        $last_visit = 0;
        $currentTime = time();

        //
        // Initial ban check against user id, IP and email address
        //
        preg_match('/(..)(..)(..)(..)/', $userIp, $userIp_parts);

        $sql = "SELECT ip, users_id, email
            FROM  Baka\Auth\Models\Banlist
            WHERE ip IN ('" . $userIp_parts[1] . $userIp_parts[2] . $userIp_parts[3] . $userIp_parts[4] . "', '" . $userIp_parts[1] . $userIp_parts[2] . $userIp_parts[3] . "ff', '" . $userIp_parts[1] . $userIp_parts[2] . "ffff', '" . $userIp_parts[1] . "ffffff')
                OR users_id = :user_id:";

        $sql .= " OR email LIKE '" . str_replace("\'", "''", $user->email) . "'
                OR email LIKE '" . substr(str_replace("\'", "''", $user->email), strpos(str_replace("\'", "''", $user->email), '@')) . "'";

        $params = [
            'users_id' => $user->getId(),
        ];

        $result = $this->getModelsManager()->executeQuery($sql, $params);

        print_R($result);
        die();

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
        $session->users_id = $user->getId();
        $session->start = $currentTime;
        $session->time = $currentTime;
        $session->page = $pageId;
        $session->logged_in = 1;
        $session->is_admin = $user->isAdmin();
        $session->id = $sessionId;
        $session->token = $token;
        $session->ip = $userIp;

        if (!$session->save()) {
            throw new Exception(current($session->getMessages()));
        }

        $lastVisit = ($user->session_time > 0) ? $user->session_time : $currentTime;

        //update user info
        $user->session_time = $currentTime;
        $user->session_page = $pageId;
        $user->lastvisit = date('Y-m-d H:i:s', $lastVisit);
        $user->update();

        //create a new one
        $session = new SessionKeys();
        $session->sessions_id = $sessionId;
        $session->users_id = $user->getId();
        $session->last_ip = $userIp;
        $session->last_login = $currentTime;
        $session->save();

        if (!$session->save()) {
            throw new Exception(current($session->getMessages()));
        }

        //you are looged in, no?
        $user->loggedIn = true;

        return $user;
    }

    /**
     * Checks for a given user session, tidies session table and updates user
     * sessions at each page refresh
     *
     * @param Users $user
     * @param string $sessionId
     * @param string $userIp
     * @param integer $pageId
     * @return Users
     */
    public function check(Users $user, string $sessionId, string $userIp, int $pageId): Users
    {
        $currentTime = time();

        $pageId = (int) $pageId;

        //
        // session_id exists so go ahead and attempt to grab all
        // data in preparation
        //
        $sql = "SELECT user.*, session.*
                FROM Baka\Auth\Models\Sessions session, Baka\Auth\Models\Users user
                WHERE session.id = :session_id:
                    AND user.id = session.users_id";

        $result = $this->getModelsManager()->createQuery($sql);
        $result = $result->execute([
                'session_id' => $sessionId,
            ]);

        //session data
        $userData = $result->getFirst();

        //wtf? how did you get this token to mimic another user?
        if ($userData->getId() != $user->getId()) {
            throw new Exception('Invalid Token');
        }

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
                //
                // Only update session DB a minute or so after last update
                //
                if ($currentTime - $userData->session->time > 60) {
                    //update the user session
                    $session = new self();
                    $session->session_time = $currentTime;
                    $session->session_page = $pageId;
                    $session->id = $sessionId;

                    $session->update();

                    //update user
                    $user->users_id = $userData->user->getId();
                    $user->session_time = $currentTime;
                    $user->session_page = $pageId;
                    $user->update();

                    $this->clean($sessionId);
                }

                $user->session_id = $sessionId;

                return $user;
            }
        }

        throw new Exception(_('No Session Token Found'));
    }

    /**
     * Removes expired sessions and auto-login keys from the database
     *
     * @param string $sessionId
     * @param boolean $daemon
     * @return void
     */
    public function clean(string $sessionId, $daemon = false): bool
    {
        //we sent the session id to the seassion daemon cleaner
        if (!$daemon) {
            $queue = $this->getDI()->getQueue();
            $queue->putInTube(getenv('SESSION_QUEUE'), $sessionId);

            return true;
        }

        $cache = $this->getDI()->getRedis();

        //
        // Delete expired sessions
        //
        $sql = "DELETE FROM  Baka\Auth\Models\Sessions
            WHERE time < :session_time:
                AND id <> :session_id: ";

        $session_time = time() - (int) $this->config->session_length;

        $params = [
            'session_time' => $session_time,
            'id' => $sessionId,
        ];

        $result = $this->getModelsManager()->executeQuery($sql, $params);

        //
        // Delete expired keys
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
    public function end(Users $user): bool
    {
        $currentTime = time();

        //
        // Delete existing session
        //
        $session = new self();
        $session->session_id = $users->session_id;
        $session->users_id = $users->getId();
        $session->delete();

        return true;
    }
}
