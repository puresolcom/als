<?php

namespace ALS\Core\Http;

class Request extends \Illuminate\Http\Request
{

    protected $comparisonSymbols = [':', '=', '<=', '>=', '!='];

    /**
     * Get Request Fields
     *
     * @return array|null
     */
    public function getFields()
    {
        if (!$this->has('fields')) {
            return null;
        }
        $fields = $this->get('fields');

        return is_array($parsedFields = array_filter(explode(',', $fields))) ? $parsedFields : null;
    }

    /**
     * Get Request Filters
     *
     * @return array|null
     */
    public function getFilters()
    {
        if (!$this->has('q')) {
            return null;
        }

        $filters = $this->get('q');

        return $this->hydrateFilters($filters);
    }

    /**
     * Decodes ambiguous filters strings into meaningful array
     *
     * @param $filters
     *
     * @return array|null
     */
    protected function hydrateFilters($filters)
    {
        $filters = array_filter(explode(',', $filters));

        if (!is_array($filters)) {
            return null;
        }

        $result = [];
        foreach ($filters as $filter) {
            if (str_contains($filter, $this->comparisonSymbols)) {
                $filterFragments = [];
                preg_match('/^([a-zA-Z0-9\-\_\.]+)(' . implode('|',
                        $this->comparisonSymbols) . '{1})(([a-zA-Z0-9\-\_\,]+)|\(([a-zA-Z0-9\-\_\,\|]+)\))$/', $filter,
                    $filterFragments);

                $relational   = false;
                $relationName = null;
                $field        = $filterFragments[1];
                $compare      = $filterFragments[2];
                $value        = $filterFragments[3];

                // if multiple values provided
                if (isset($filterFragments[5])) {
                    $multiValues = explode('|', $filterFragments[5]);

                    if (count($multiValues) > 1) {
                        $value = $multiValues;
                    }else {
                        $value = $filterFragments[5];
                    }
                }

                if (strpos($field, '.') !== false) {
                    $relational        = true;
                    $relationFragments = explode('.', $field);
                    $field             = array_pop($relationFragments);
                    $relationName      = implode('.', $relationFragments);
                }

                $result[] = [
                    'relational'   => $relational,
                    'relationName' => $relationName,
                    'field'        => $field,
                    'compare'      => $compare,
                    'value'        => $value
                ];
            }
        }
        return !empty($result) ? $result : null;
    }

    /**
     * Get Request sort fields
     *
     * @return array|null
     */
    public function getSort()
    {
        if (!$this->has('sort')) {
            return null;
        }
        $sort       = $this->get('sort');
        $parsedSort = array_filter(explode(',', $sort));

        if (!is_array($parsedSort)) {
            return null;
        }

        $result = array_map(function ($sort){

            $orderBy = null;
            if (strpos($sort, '!', 0) === 0) {
                $orderBy   = substr($sort, 1);
                $direction = 'DESC';
            }elseif (strpos($sort, ':') !== false) {
                list($orderBy, $direction) = explode(':', $sort);
            }else {
                $orderBy   = $sort;
                $direction = 'ASC';
            }

            return [
                'orderBy'   => $orderBy,
                'direction' => $direction
            ];
        }, $parsedSort);

        return $result;
    }

    /**
     * Get Request Relations
     *
     * @return array|null
     */
    public function getRelations()
    {
        if (!$this->has('with')) {
            return null;
        }

        $relations       = $this->get('with');
        $parsedRelations = array_filter(explode(',', $relations));

        if (!is_array($parsedRelations)) {
            return null;
        }

        $result = array_map(function ($relation){

            if (strpos($relation, '.') !== false) {
                $relationFragments = explode('.', $relation);
            }else {
                $relationFragments[] = $relation;
            }
            $targetedRelationFragment = array_pop($relationFragments);
            $matchedRelation          = [];
            preg_match('/^([a-zA-Z\.\-\_]+)\((.+)\)$/', $targetedRelationFragment, $matchedRelation);

            if (!empty($matchedRelation)) {
                array_push($relationFragments, $matchedRelation[1]);
                $relationName   = implode('.', $relationFragments);
                $relationFields = explode('|', $matchedRelation[2]);
            }else {
                $relationName   = $relation;
                $relationFields = [];
            }

            return [
                'relationName'   => $relationName,
                'relationFields' => $relationFields
            ];

        }, $parsedRelations);

        return $result;
    }

    public function getPerPage()
    {
        if ($this->has('limit')) {
            return $this->get('limit');
        }

        if ($this->has('per_page')) {
            return $this->get('per_page');
        }

        return null;
    }

}