<?php

namespace ALS\Modules\Expense\Repositories;

use ALS\Core\Repository\BaseRepository;
use ALS\Modules\Expense\Models\Expense;

class ExpenseRepository extends BaseRepository
{
    public function model()
    {
        return Expense::class;
    }
}
