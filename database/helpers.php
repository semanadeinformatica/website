<?php

namespace DatabaseHelpers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates a Many-to-Many relationship.
 *
 * @param  \Illuminate\Database\Eloquent\Model|string  $model1
 * @param  \Illuminate\Database\Eloquent\Model|string  $model2
 */
function createManyToManyRelation($model1, $model2)
{

    // We need to ensure lexicographical order because Laravel
    $models = [$model1, $model2];
    sort($models, SORT_STRING);
    [$model1, $model2] = $models;

    if (is_string($model1)) {
        $model1 = new $model1;
    }

    if (is_string($model2)) {
        $model2 = new $model2;
    }

    $table_name = rtrim($model1->getTable(), 's').'_'.rtrim($model2->getTable(), 's');

    Schema::create($table_name, function (Blueprint $table) use ($model1, $model2) {
        $table->foreignIdFor($model1)->constrained()->cascadeOnDelete();
        $table->foreignIdFor($model2)->constrained()->cascadeOnDelete();
        $table->primary([$model1->getForeignKey(), $model2->getForeignKey()]);
    });
}
