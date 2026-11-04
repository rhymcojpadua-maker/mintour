<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'        => fake()->sentence(5),
            'category'     => fake()->randomElement(['Beaches','Transport','Hotels','Events','Food','Safety']),
            'content'      => fake()->paragraphs(2, true),
            'source'       => 'Tourism Office verified',
            'tags'         => fake()->randomElements(['beach','silonay','tricycle','hotel','pahiyas','mangrove'], 2),
            'is_published' => true,
        ];
    }
}
