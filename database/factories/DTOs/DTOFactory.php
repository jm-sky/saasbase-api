<?php

namespace Database\Factories\DTOs;

use Faker\Generator as Faker;

abstract class DTOFactory
{
    protected Faker $faker;

    public function __construct()
    {
        $this->faker = fake();
    }

    abstract public function make(?array $attributes = []): object;
}
