<?php

namespace ALS\Core\Repository;

use ALS\Core\Eloquent\Builder;

abstract class BaseRepository extends \Prettus\Repository\Eloquent\BaseRepository
{

    /**
     * Maps API symbols to SQL-like symbols
     *
     * @var array
     */
    protected $symbolMap = [
        ':' => '='
    ];

    /**
     * Retrieve all data of repository, paginated
     *
     * @param null   $limit
     * @param array  $columns
     * @param string $method
     * @param string $dataKey
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'], $method = "paginate", $dataKey = 'data')
    {
        $this->applyCriteria();
        $this->applyScope();
        $limit   = is_null($limit) ? config('repository.pagination.limit', 20) : $limit;
        $results = $this->model->{$method}($limit, $columns, $pageName = 'page', $page = null, $dataKey);
        $results->appends(app('request')->query());
        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * Prepares a restful query
     *
     * @param null   $fields
     * @param null   $filters
     * @param null   $sort
     * @param null   $relations
     * @param int    $limit
     * @param string $dataKey
     *
     * @return mixed
     */
    public function restQueryBuilder(
        $fields = null,
        $filters = null,
        $sort = null,
        $relations = null,
        $limit = null,
        $dataKey = 'data'
    ){
        $model  = $this->model;
        $select = $this->prepareColumns($fields, $model);
        $with   = $this->prepareRelations($relations, $select);
        $filter = $this->prepareFilters($filters, $with);
        $sort   = $this->prepareSorting($sort, $filter);
        $this->resetModel();
        return $this->paginateResult($limit, $dataKey, $sort);


    }

    /**
     * Convert url comparison symbols to mysql symbols and maps relational filtering
     *
     * @param Builder $model
     * @param         $filter
     *
     * @return bool|\Illuminate\Database\Eloquent\Builder
     */
    protected function interpretFilterSymbols(Builder &$model, $filter)
    {
        if (!isset($filter['compare'])) {
            return false;
        }

        if (array_key_exists($filter['compare'], $this->symbolMap)) {
            // Convert symbols
            $filter['compare'] = $this->symbolMap[$filter['compare']];
        }

        if ($filter['relational']) {
            return $model->whereHas($filter['relationName'], function ($query) use ($filter){
                return $this->appendClauses($query, $filter);
            }
            );
        }else {
            return $this->appendClauses($model, $filter);
        }
    }

    /**
     * Applies clauses to the current query context/scope
     *
     * @param $model
     * @param $filter
     *
     * @return mixed
     */
    protected function appendClauses(&$model, $filter)
    {
        // Case null check
        if (is_string($filter['value']) && strtolower($filter['value']) == 'NULL') {
            if ($filter['compare'] == '=') {
                return $model->whereNull($filter['field']);
            }else {
                return $model->whereNotNull($filter['field']);
            }
        }else {
            // Case of multiple values
            if (is_array($filter['value'])) {
                return $model->whereIn($filter['field'], $filter['value']);
            }// Any other case
            else {
                return $model->where($filter['field'], $filter['compare'], $filter['value']);
            }
        }
    }

    /**
     * @param $fields
     * @param $results
     *
     * @return mixed
     */
    protected function prepareColumns($fields, $results)
    {
        // Preparing select columns
        $fields = !empty($fields) ? $fields : ['*'];
        if (is_array($fields)) {
            $results = $results->select($fields);
            return $results;
        }
        return $results;
    }

    /**
     * @param $relations
     * @param $results
     *
     * @return mixed
     */
    protected function prepareRelations($relations, $results)
    {
        // Preparing relations
        if (is_array($relations)) {
            foreach ($relations as $relation) {
                $results = $results->with([
                    $relation['relationName'] => function ($query) use ($relation){
                        if (count($relation['relationFields']) > 0) {
                            $fields = array_merge(['id'], $relation['relationFields']);
                        }else {
                            $fields = ['*'];
                        }
                        return $query->select($fields);
                    }
                ]);
            }
            return $results;
        }
        return $results;
    }

    /**
     * @param $filters
     * @param $results
     *
     * @return bool|\Illuminate\Database\Eloquent\Builder
     */
    protected function prepareFilters($filters, $results)
    {
        // Preparing filters
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $results = $this->interpretFilterSymbols($results, $filter);
            }
            return $results;
        }
        return $results;
    }

    /**
     * @param $sort
     * @param $results
     *
     * @return mixed;
     */
    protected function prepareSorting($sort, $results)
    {
        // Preparing Sorting
        if (is_array($sort)) {
            foreach ($sort as $order) {
                $results->orderBy($order['orderBy'], $order['direction']);
            }
        }
        return $results;
    }

    /**
     * @param $limit
     * @param $dataKey
     * @param $results
     *
     * @return mixed
     */
    protected function paginateResult($limit, $dataKey, $results)
    {
        // Paginate
        $limit = is_null($limit) ? config('repository.pagination.limit', 20) : $limit;
        return $this->parserResult($results->paginate($limit, ['*'], $pageName = 'page', $page = null, $dataKey));
    }
}