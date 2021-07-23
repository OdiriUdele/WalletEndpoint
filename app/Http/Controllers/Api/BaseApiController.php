<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseApiController extends Controller
{
    protected $statusCode = 200;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {

    	return $this->statusCode;

    }

    /**
     *
     * @param mixed $statusCode
     * $this
     */
    public function setStatusCode($statusCode)
    {

    	$this->statusCode = $statusCode;

    	return $this;
    }

    public function respond($data, $headers = [])
    {

    	return response()->json($data, $this->getStatusCode(), $headers);

    }

    /**
     *
     * @param mixed $message
     *
     */
    public function respondCreated($data, $message = 'Created Successfully!')
    {

    	return $this->setStatusCode(201)->respond([
            'status' =>true,
            'data' => $data,
    		'message' => $message

    	]);

    }

    /**
     *
     * @param mixed $message
     *
     */
    public function respondFailedValidation($message = 'Parameters Not Valid!', $code = "422")
    {

    	return $this->setStatusCode(422)->respondWithError($message, $code);

    }

    /**
     *
     * @param mixed $message
     *
     */
    public function respondNotFound($message = 'Not Found!', $code = 200, $responseCode = "")
    {

    	return $this->setStatusCode($code)->respondWithError($message, $responseCode);

    }

    /**
     *
     * @param mixed $message
     *
     */
    public function respondInternalError($message = 'Internal Error!', $code = "500")
    {

    	return $this->setStatusCode(500)->respondWithError($message, $code);

    }

    /**
     *
     * @param mixed $message
     *
     */
    public function respondWithError($message, $code = "500")
    {

    	return $this->setStatusCode($code)->respond([
    		'response' => [
                'status'=>false,
                'responseCode' => $code,
    			'responseDescription' => $message
    		]
    	]);
    }
}
