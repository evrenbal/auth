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
     * url /v1/leads
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
        $objectInfo = $this->model->findFirst([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$this->userData->getId()],
        ]);

        $objectInfo->password = null;

        //get relationship
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');

            $objectInfo = QueryParser::parseRelationShips($relationships, $objectInfo);
        }

        if ($objectInfo) {
            return $this->response($objectInfo);
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
        if ($objectInfo = $this->model->findFirst($this->userData->getId())) {
            $request = $this->request->getPut();

            if (empty($request)) {
                $request = $this->request->getJsonRawBody(true);
            }

            if (array_key_exists('password', $request) && !empty($request['password'])) {
                $objectInfo->password = Users::passwordHash($request['password']);
            }

            //update
            if ($objectInfo->update($request, $this->updateFields)) {
                return $this->response($objectInfo->toArray());
            } else {
                //didnt work
                throw new Exception($objectInfo->getMessages()[0]);
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
