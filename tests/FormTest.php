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
}