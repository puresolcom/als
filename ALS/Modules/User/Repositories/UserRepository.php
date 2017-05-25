<?php

namespace ALS\Modules\User\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\User\Models\User;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }
}