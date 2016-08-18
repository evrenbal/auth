<?php

namespace Baka\Models;

use Baka\Models\Sources;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class UserLinkedSources extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $user_id;

    /**
     *
     * @var integer
     */
    public $source_id;

    /**
     *
     * @var integer
     */
    public $source_user_id;

    /**
     *
     * @var string
     */
    public $source_username;

    /**
     * initialize the class
     */
    public function initialize()
    {
        $this->belongsTo("user_id", "Baka\Models\Users", "user_id", ['alias' => 'users']);
    }

    /**
     * Validations and business logic
     */
    public function validation()
    {   
        $validator = new Validation();
        $validator->add(
            'user_id',
            new Uniqueness([
                'field' => ['user_id', 'source_user_id'],
                'message' => _('You have already associated this account.'),
            ])
        );
       return $this->validate($validator);
    }

    /**
     * Funcion que registra al user de la red social, al sistema.
     * Si ya esta registrado lo logea
     *
     * @param Hybridauth\Entity\Profile $socialProfile
     * @param string $socialNetwork
     * @return Users
     */
    public function associateAccount(Users $user, \Hybridauth\Entity\Profile $socialProfile, $socialNetwork)
    {

        //si no esta asociada tu uenta
        if (!$this->existSocialProfile($socialProfile, $socialNetwork)) {
            $source = Sources::findFirst(['title = :title:', 'bind' => ['title' => $socialNetwork]]);

            $userLinkedSources = new self();
            $userLinkedSources->user_id = $user->user_id;
            $userLinkedSources->source_id = $source->source_id;
            $userLinkedSources->source_user_id = $socialProfile->getIdentifier();
            $userLinkedSources->source_username = $socialProfile->getDisplayName();

            //since the user is registration via a social network and it was sucessful we need to activate its account
            if (!$user->user_active) {
                $user->user_active = 1;
                $user->update();
            }

            return $userLinkedSources->save();
        }

        return false;
    }

    /**
     * is this profile already registrated in the system?
     * @param \Hybridauth\Entity\Profile $socialProfile
     * @param string $socialNetwork
     *
     * @return boolean
     */
    public function existSocialProfile(\Hybridauth\Entity\Profile $socialProfile, $socialNetwork)
    {
        //si existe el source que nos esta pidiendo el usuario
        if ($source = Sources::findFirst(['title = :title:', 'bind' => ['title' => $socialNetwork]])) {

            //verificamos que no tenga la cuenta ya relacionada con ese social network
            $bind = [
                'source_id' => $source->source_id,
                'source_user_id' => $socialProfile->getIdentifier(),
            ];

            //si no tienes una cuenta ya registrada con social network y no estas registrado con este correo
            if ($userSocialLinked = self::findFirst(['source_id = :source_id: and source_user_id = :source_user_id:', 'bind' => $bind])) {
                $admin = $userSocialLinked->users->isAdmin();
                $userIp = $this->getDI()->getRequest()->getClientAddress();
                $remember = 1;

                //login the user , so we just create the user session base on the user object
                $session = new \Naruhodo\Models\Sessions\Sessions();
                $userSession = $session->session_begin($userSocialLinked->users->user_id, $userIp, PAGE_INDEX, false, $remember, $admin);

                //you are logged in
                return true;
            }
        } else {
            throw new \Exception(_('We currently do not have support to connect to this social network.'));
        }

        return false;
    }

    /**
     * is the user already connecte to the social media site?
     *
     * @param  $userData Users
     * @param  $socialNetwork string
     */
    public static function alreadyConnected(Users $userData, $socialNetwork)
    {
        $source = Sources::findFirst(['title = :title:', 'bind' => ['title' => $socialNetwork]]);

        $bind = [
            'source_id' => $source->source_id,
            'user_id' => $userData->user_id,
        ];

        if ($userSocialLinked = self::findFirst(['source_id = :source_id: and user_id = :user_id:', 'bind' => $bind])) {
            return true;
        }

        return false;
    }

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'user_id' => 'user_id',
            'source_id' => 'source_id',
            'source_user_id' => 'source_user_id',
            'source_username' => 'source_username',
            'source_user_id_text' => 'source_user_id_text',
        );
    }

}
