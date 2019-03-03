<?php declare(strict_types=1);

namespace Zyglab\Plates\Tests;

use League\Plates\Engine;
use PHPUnit\Framework\TestCase;
use Zyglab\Plates\Form;

class FormTest extends TestCase
{
    /**
     * @var Form
     */
    protected $form;

    protected function setUp(): void
    {
        $this->form = new Form;
    }

    public function testRegister(): void
    {
        $engine = new Engine;

        $this->form->register($engine);

        $this->assertTrue($engine->doesFunctionExist('form'));
    }

    public function testGetObject(): void
    {
        $this->assertInstanceOf(Form::class, $this->form->getObject());
    }

    public function testSetTemplate(): void
    {
        $this->form->setTemplate('label', '<label>{label}</label>');

        $label = $this->form->label('Testing', 'test');

        $this->assertEquals('<label>Testing</label>', $label);
    }

    public function testLabel(): void
    {
        $label = $this->form->label('Testing', 'test');

        $this->assertEquals('<label for="Test">Testing</label>', $label);

        $label = $this->form->label('Testing', 'test', 'my-id');

        $this->assertEquals('<label for="my-id">Testing</label>', $label);
    }

    public function testError(): void
    {
        $this->form->setErrors(['test' => ['First error', 'Second error']]);

        $error = $this->form->error('test');

        $this->assertEquals(
            '<div class="errors"><ul><li>First error</li><li>Second error</li></ul></div>',
            $error
        );
    }

    public function testInput(): void
    {
        $this->form->setRequestData(['test' => 'John was here!']);
        $this->form->setDefaultData(['test' => 'Hello World!']);
        $this->form->setErrors(['test' => ['Error! Danger!']]);

        $params = [
            'type' => 'password',
            'class' => 'plates-form',
            'data-controller' => 'testing',
            'id' => 'MyTest'
        ];

        $input = $this->form->input('test', $params);

        $this->assertEquals(
            '<input type="password" name="data[test]" id="MyTest" value="John was here!" class="plates-form error" data-controller="testing">',
            $input
        );

        $this->form->resetRequestData();
        $this->form->resetErrors();

        unset($params['id'], $params['data-controller']);

        $input = $this->form->input('test', $params);

        $this->assertEquals(
            '<input type="password" name="data[test]" id="Test" value="Hello World!" class="plates-form">',
            $input
        );

        $this->form->resetDefaultData();

        $input = $this->form->input('test', $params);

        $this->assertEquals(
            '<input type="password" name="data[test]" id="Test" value="" class="plates-form">',
            $input
        );
    }

    public function testSelect(): void
    {
        $params = [
            'options' => [
                '1' => 'Option One',
                '2' => 'Option Two'
            ]
        ];

        $select = $this->form->select('test', $params);

        $this->assertEquals(
            '<select name="data[test]" id="Test" class=""><option value="1">Option One</option><option value="2">Option Two</option></select>',
            $select
        );

        $params['value'] = '1';

        $select = $this->form->select('test', $params);

        $this->assertEquals(
            '<select name="data[test]" id="Test" class=""><option value="1" selected>Option One</option><option value="2">Option Two</option></select>',
            $select
        );
    }
}