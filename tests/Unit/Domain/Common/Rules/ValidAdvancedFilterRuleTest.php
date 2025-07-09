<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Common\Rules;

use App\Domain\Common\Rules\ValidAdvancedFilterRule;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidatorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \App\Domain\Common\Rules\ValidAdvancedFilterRule
 */
#[CoversClass(ValidAdvancedFilterRule::class)]
class ValidAdvancedFilterRuleTest extends TestCase
{
    protected function makeValidator(array $data, array $rules)
    {
        $translator = new Translator(new ArrayLoader(), 'en');
        $factory    = new ValidatorFactory($translator);

        return $factory->make($data, $rules);
    }

    public function testValidSingleValuePasses()
    {
        $rule      = new ValidAdvancedFilterRule('string');
        $data      = ['filter' => 'abc'];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertTrue($validator->passes());
    }

    public function testValidOperatorArrayPasses()
    {
        $rule      = new ValidAdvancedFilterRule('int');
        $data      = ['filter' => ['gt' => 5]];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertTrue($validator->passes());
    }

    public function testInvalidOperatorFails()
    {
        $rule      = new ValidAdvancedFilterRule('int');
        $data      = ['filter' => ['foo' => 5]];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertFalse($validator->passes());
    }

    public function testBetweenOperatorArrayPasses()
    {
        $rule      = new ValidAdvancedFilterRule('int');
        $data      = ['filter' => ['between' => [1, 10]]];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertTrue($validator->passes());
    }

    public function testBetweenOperatorStringPasses()
    {
        $rule      = new ValidAdvancedFilterRule('int');
        $data      = ['filter' => ['between' => '1,10']];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertTrue($validator->passes());
    }

    public function testInOperatorArrayPasses()
    {
        $rule      = new ValidAdvancedFilterRule('int');
        $data      = ['filter' => ['in' => [1, 2, 3]]];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertTrue($validator->passes());
    }

    public function testNullOperatorPasses()
    {
        $rule      = new ValidAdvancedFilterRule('int');
        $data      = ['filter' => ['null' => true]];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertTrue($validator->passes());
    }

    public function testStringOperatorFailsOnNonStringType()
    {
        $rule      = new ValidAdvancedFilterRule('int');
        $data      = ['filter' => ['like' => 'foo']];
        $validator = $this->makeValidator($data, ['filter' => [$rule]]);
        $this->assertFalse($validator->passes());
    }
}
