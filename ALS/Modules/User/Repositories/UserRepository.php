<?php

namespace ALS\Modules\User\Repositories;

use ALS\Modules\User\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }
}