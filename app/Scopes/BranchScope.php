<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && auth()->user()->role !== 'Admin' && auth()->user()->role !== 'Management_View') {
            $column = $model->branchColumn ?? 'branch_id';
            $branchId = auth()->user()->branch_id;
            $builder->where(function ($q) use ($column, $branchId, $model) {
                $q->where($column, $branchId);
                if (property_exists($model, 'destinationBranchColumn')) {
                    $q->orWhere($model->destinationBranchColumn, $branchId);
                }
            });
        }
    }
}
