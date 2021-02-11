<?php

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;

trait TestValidations
{
    protected abstract function model();

    protected abstract function routeStore();

    protected abstract function routeUpdate();

    protected function assertInvalidationInStoreAction(array $data, string $rule, array $ruleParams = [])
    {
        $response = $this->json('post', $this->routeStore(), $data);
        $fileds = array_keys($data);
        $this->assertInvalidationFields($response, $fileds, $rule, $ruleParams);
    }

    protected function assertInvalidationInUpdateAction(array $data, string $rule, array $ruleParams = [])
    {
        $response = $this->json('put', $this->routeUpdate(), $data);
        $fileds = array_keys($data);
        $this->assertInvalidationFields($response, $fileds, $rule, $ruleParams);
    }

    protected function assertInvalidationFields(TestResponse $response, array $fields, string $rule, array $rulesParams = [])
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors($fields);

        foreach ($fields as $field) {
            $fieldName = str_replace('_', ' ', $field);
            $response->assertJsonFragment([
                \Lang::get("validation.{$rule}", ['attribute' => $fieldName] + $rulesParams),
            ]);
        }
    }
}
