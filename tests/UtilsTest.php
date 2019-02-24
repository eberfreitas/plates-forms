<?php declare(strict_types=1);

namespace Zyglab\Plates\Tests;

use PHPUnit\Framework\TestCase;
use Zyglab\Plates\Utils;

class UtilsTest extends TestCase
{
    public function testCamelize(): void
    {
        $this->assertEquals('MyTest', Utils::camelize('my test'));
        $this->assertEquals('MyTest', Utils::camelize('my-test'));
        $this->assertEquals('MyTest', Utils::camelize('my.test'));
        $this->assertEquals('MyTest', Utils::camelize('my_test'));
    }

    public function testFormat(): void
    {
        $template = 'My name is {name} and I\'m {age} years old.';
        $vars = [
            'name' => 'John Doe',
            'age' => 34
        ];

        $this->assertEquals(
            'My name is John Doe and I\'m 34 years old.',
            Utils::format($template, $vars)
        );

        $template = 'Hi! My name is {name}. Sorry, my name is {name}!';
        $vars = ['name' => 'Jane Moe'];

        $this->assertEquals(
            'Hi! My name is Jane Moe. Sorry, my name is Jane Moe!',
            Utils::format($template, $vars)
        );
    }
}