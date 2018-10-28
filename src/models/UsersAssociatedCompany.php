<?php

namespace Baka\Auth\Models;

use Baka\Database\Model;

class UsersAssociatedCompany extends Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $users_id;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    public $company_id;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $identify_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $user_active;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    public $user_role;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'users_associated_company';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersAssociatedCompany[]|UsersAssociatedCompany
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersAssociatedCompany
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
