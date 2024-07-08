<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Filterable {
    public $per_page = 0;
    public $page_number = 0;
    public $total_records;

    public function scopeFilter($query, $request) {
        $filters = is_array($request) ? $request : $request->all();
        $isPaginate = isset($filters['is_paginate']) ? (int) $filters['is_paginate'] : 1;
        unset($filters['is_paginate']);

        $page = isset($filters['page']) && (int) $filters['page'] ? (int) $filters['page'] : 1;
        $perPage = $filters['per_page'] ? (int) $filters['per_page'] : 0;
        unset($filters['page']);
        unset($filters['per_page']);
        $this->per_page = $perPage;
        $this->page_number = $page;

        // sorting
        $orderField = isset($filters['order_by']) && $filters['order_by'] ? $this->getTable() .'.' .$filters['order_by'] : $this->getTable() . '.id'; 
        $orderType = isset($filters['order_type']) && strtolower($filters['order_type']) == 'asc' ? 'asc' : 'desc';
        unset($filters['order_by']);
        unset($filters['order_type']);
        
        foreach ($filters as $key => $value) {
            if ($value === '') continue;
            
            if ($key == 'keyword') {
                $textFields = $this->filterKeywords;
                $value = trim(strtolower($value));

                if (count($textFields)) {
                    $query = $query->where(function ($q) use ($textFields, $value) {
                        $q = $q->where($textFields[0], 'LIKE', '%' . $value . '%');
                        unset($textFields[0]);

                        foreach ($textFields as $field) {
                            $q = $q->orWhere($field, 'LIKE', '%' . $value . '%');
                        }
                    });
                }
            } else if ($this->filterTextFields && in_array($key,$this->filterTextFields)) {
                $query = $query->where($key, 'LIKE', "%{$value}%");
            }else if (Str::endsWith($key, '_min')) {
                $key = Str::replaceLast('_min', '', $key);
                $query = $query->where($key, '>=', $value);
            } else if (Str::endsWith($key, '_max')) {
                $key = Str::replaceLast('_max', '', $key);
                $query = $query->where($key, '<=', $value);
            } else if (Str::contains($value, ',')) {
                $query = $query->whereIn($key, explode(',', $value));
            } else {
                $fieldList = $this->filterFields;
                if (is_array($fieldList) && in_array($key, $fieldList)) {
                    $query = $query->where($key, '=', $value);
                } 
            }
        }

        if ($this->per_page) {
            $query = $query->orderBy($orderField, $orderType)->skip(($page - 1) * $perPage)->take($perPage);
        } else {
            $query = $query->orderBy($orderField, $orderType);
        }
      
        return $query;
    }

    public function scopeGetPerPage() {
        return $this->per_page;
    }

    public function scopeGetPageNumber() {
        return $this->page_number;
    }

    public function scopeGetTotal($query) {
        return $query->count();
    }
}
