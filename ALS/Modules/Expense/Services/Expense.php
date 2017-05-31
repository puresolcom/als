<?php

namespace ALS\Modules\Expense\Services;

use ALS\Modules\Expense\Repositories\ExpenseRepository;

/**
 * Expense Service
 *
 * @package ALS\Modules\Expense\Services
 */
class Expense
{
    /**
     * @var \ALS\Modules\Expense\Repositories\ExpenseRepository
     */
    protected $expenseRepo;

    public function __construct(ExpenseRepository $expenseRepo)
    {
        $this->expenseRepo = $expenseRepo;
    }

    public function getByReportID($id)
    {
        return $this->expenseRepo->findWhere(['report_id' => $id]);
    }
}