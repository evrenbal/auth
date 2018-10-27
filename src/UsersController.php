<?php

namespace Baka\Auth;

use Baka\Auth\Models\Users;
use Phalcon\Http\Response;
use Exception;

/**
 * Base controller
 *
 */
abstract class UsersController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'firstname', 'lastname',  'displayname', 'email', 'password', 'created_at', 'updated_at', 'default_company', 'family'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'firstname', 'lastname',  'displayname', 'email', 'password', 'created_at', 'updated_at', 'default_company'];

    /**
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Users();
    }

    /**
     * List of business
     *
     * @method GET
     * url /v1/users
     *
     * @param int $id
     * @return \Phalcon\Http\Response
     */
    public function index($id = null) : Response
    {
        $this->additionalSearchFields = [
            ['id', ':', $this->userData->getId()],
        ];

        return parent::index();
    }

    /**
     * get item
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/users/{id}
     *
     * @return Phalcon\Http\Response
     */
    public function getById($id) : Response
    {
        //find the info
        $user = $this->model->findFirst([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$this->userData->getId()],
        ]);

        $user->password = null;

        //get relationship
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');

            $user = QueryParser::parseRelationShips($relationships, $user);
        }

        if ($user) {
            return $this->response($user);
        } else {
            throw new Exception('Record not found');
        }
    }

    /**
     * Update a new Entry
     *
     * @method PUT
     * @url /v1/users/{id}
     *
     * @return Phalcon\Http\Response
     */
    public function edit($id) : Response
    {
        if ($user = $this->model->findFirst($this->userData->getId())) {
            $request = $this->request->getPut();

            if (empty($request)) {
                $request = $this->request->getJsonRawBody(true);
            }

            if (array_key_exists('password', $request) && !empty($request['password'])) {
                $user->password = Users::passwordHash($request['password']);
            }

            //update
            if ($user->update($request, $this->updateFields)) {
                return $this->response($user->toArray());
            } else {
                //didnt work
                throw new Exception($user->getMessages()[0]);
            }
        } else {
            throw new Exception('Record not found');
        }
    }

    /**
     * Add a new user
     *
     * @method POST
     * @url /v1/users
     * @overwrite
     *
     * @return Phalcon\Http\Response
     */
    public function create() : Response
    {
        throw new Exception('Route not found');
    }
}
