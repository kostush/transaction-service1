<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory;
use Faker\Generator;

trait Faker
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * Faker initialization
     * @return void
     */
    public function configFaker()
    {
        $this->faker = Factory::create(Factory::DEFAULT_LOCALE);
    }
}
