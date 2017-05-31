<?php

namespace ALS\Core\Support;

use Illuminate\Http\Request;

trait RestfulValidateTrait
{
    function validate(
        Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ) {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $this->jsonResponse(null, 'Error Occurred while validating your input', 400, $validator->getMessageBag()->all())->send();
            die;
        }
    }
}