<?php

namespace App\Core\Form;

use App\Core\Model;

abstract class BaseField
{
    public function __construct(
        public Model $model,
        public string $attribute
    )
    {
    }
    abstract public function renderInput(): string;
}