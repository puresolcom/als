<?php

namespace ALS\Http\Controllers;

use ALS\Core\Support\RestfulResponseTrait;
use ALS\Core\Support\RestfulValidateTrait;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use RestfulResponseTrait, RestfulValidateTrait;
}
