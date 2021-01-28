<?php

namespace Tests\Feature\Rules;

use App\Models\Category;
use App\Models\Genre;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenreHasCategoriesRuleTest extends TestCase
{
    use DatabaseMigrations;

    private Collection $categories;
    private Collection $genres;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categories = Category::factory(4)->create();
        $this->genres = Genre::factory(2)->create();

        $this->genres[0]->categories()->sync([
            $this->categories[0]->id,
            $this->categories[1]->id,
        ]);

        $this->genres[1]->categories()->sync([
            $this->categories[2]->id,
        ]);
    }

    public function test_passes_is_valid()
    {
        $rule = new GenresHasCategoriesRule([
            $this->categories[2]->id,
        ]);

        $isValid = $rule->passes('', [
            $this->genres[1]->id,
        ]);
        $this->assertTrue($isValid);

        $rule = new GenresHasCategoriesRule([
            $this->categories[0]->id,
            $this->categories[2]->id,
        ]);
        $isValid = $rule->passes('', [
            $this->genres[0]->id,
            $this->genres[1]->id,
        ]);
        $this->assertTrue($isValid);

        $rule = new GenresHasCategoriesRule([
            $this->categories[0]->id,
            $this->categories[1]->id,
            $this->categories[2]->id,
        ]);
        $isValid = $rule->passes('', [
            $this->genres[0]->id,
            $this->genres[1]->id,
        ]);
        $this->assertTrue($isValid);
    }

    public function test_passes_is_not_valid()
    {
        $rule = new GenresHasCategoriesRule([
            $this->categories[0]->id,
        ]);
        $isValid = $rule->passes('', [
            $this->genres[0]->id,
            $this->genres[1]->id,
        ]);
        $this->assertFalse($isValid);

        $rule = new GenresHasCategoriesRule([
            $this->categories[3]->id,
        ]);
        $isValid = $rule->passes('', [
            $this->genres[0]->id,
        ]);
        $this->assertFalse($isValid);
    }
}
