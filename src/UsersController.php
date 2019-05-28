<?php

namespace Baka\Auth;

use Baka\Auth\Models\Users;
use Baka\Auth\Models\Companies;
use Phalcon\Http\Response;
use Exception;
use Baka\Http\Api\BaseController;
use Baka\Http\Converter\RequestUriToSql;
use Baka\Http\Contracts\Api\CrudBehaviorTrait;

/**
 * Base controller.
 *
 */
abstract class UsersController extends BaseController
{
    use CrudBehaviorTrait;
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'firstname', 'lastname',  'displayname', 'email', 'password', 'created_at', 'updated_at', 'default_company', 'family', 'sex', 'timezone'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'firstname', 'lastname',  'displayname', 'email', 'password', 'created_at', 'updated_at', 'default_company', 'sex', 'timezone'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Users();

        $this->additionalSearchFields = [
            ['id', ':', $this->userData->getId()],
        ];
    }

    /**
     * Get Uer.
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
            $user = RequestUriToSql::parseRelationShips($relationships, $user);
        }

        if ($user) {
            return $this->response($user);
        } else {
            throw new Exception('Record not found');
        }
    }

    /**
     * Update a User Info.
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

            //clean pass
            if (array_key_exists('password', $request) && !empty($request['password'])) {
                $user->password = Users::passwordHash($request['password']);
                unset($request['password']);
            }

            //clean default company
            if (array_key_exists('default_company', $request)) {
                //@todo check if I belong to this company
                if ($company = Companies::findFirst($request['default_company'])) {
                    $user->default_company = $company->getId();
                    unset($request['default_company']);
                }
            }

            //update
            if ($user->update($request, $this->updateFields)) {
                $user->password = null;
                return $this->response($user);
            } else {
                //didnt work
                throw new Exception($user->getMessages()[0]);
            }
        } else {
            throw new Exception('Record not found');
        }
    }

    /**
     * Add a new user.
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
        return $this->response('Route not found');
    }
}
