<?php declare(strict_types=1);

namespace Zyglab\Plates;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class Form implements ExtensionInterface
{
    /**
     * @var array
     */
    protected $requestData = [];

    /**
     * @var array
     */
    protected $defaultData = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $defaultTemplates = [
        'label' => '<label for="{id}">{label}</label>',
        'error' => '<div class="errors"><ul>{errors}</ul></div>',
        'error_item' => '<li>{error}</li>',
        'input' => '<input type="{type}" name="data[{name}]" id="{id}" value="{value}" class="{class}"{extra}>',
        'select' => '<div class="select"><select name="data[{name}]" id="{id}" class="{class}"{extra}>{options}</select></div>',
        'select_option' => '<option value="{value}">{option}</option>',
    ];

    /**
     * @var array
     */
    protected $userDefinedTemplates = [];

    /**
     * @param array $requestData
     *
     * @return void
     */
    public function __construct(array $requestData = [])
    {
        $this->requestData = $requestData;
    }

    /**
     * @param Engine $engine
     *
     * @return void
     */
    public function register(Engine $engine): void
    {
        $engine->registerFunction('form', [$this, 'getObject']);
    }

    /**
     * @return Form
     */
    public function getObject(): Form
    {
        return $this;
    }

    /**
     * @param string $label
     * @param string|null $name
     * @param string|null $id
     *
     * @return string
     */
    public function label(string $label, ?string $name = null, ?string $id = null): string
    {
        if ($name && !$id) {
            $id = Utils::camelize($name);
        }

        return Utils::format($this->getTemplate('label'), compact('label', 'id'));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function error(string $name): string
    {
        if (!isset($this->errors[$name])) {
            return '';
        }

        $params = ['errors' => []];

        foreach ($this->errors[$name] as $error) {
            $params['errors'][] = Utils::format($this->getTemplate('error_item'), compact('error'));
        }

        $params['errors'] = join('', $params['errors']);

        return Utils::format($this->getTemplate('error'), $params);
    }

    /**
     * @param string $name
     * @param array $params
     *
     * @return string
     */
    public function input(string $name, array $params = []): string
    {
        $defaultParams = [
            'class' => '',
            'extra' => '',
            'id' => null,
            'name' => $name,
            'type' => 'text',
            'value' => null,
        ];

        $params = $this->makeParams($defaultParams, $params);

        return Utils::format($this->getTemplate('input'), $params);
    }

    /**
     * @param string $label
     * @param string $name
     * @param array $params
     *
     * @return string
     */
    public function select(string $label, string $name, array $params = []): string
    {
        $defaultParams = [
            'class' => '',
            'extra' => '',
            'id' => null,
            'name' => $name,
            'options' => [],
            'value' => null,
        ];

        $params = $this->makeParams($defaultParams, $params);
        $options = [];

        foreach ($params['options'] as $k => $option) {
            $opt = Utils::format($this->getTemplate('select_option'), ['value' => $k, 'option' => $option]);

            if ($params['value'] == $k) {
                $elm = simplexml_load_string($opt);
                $elm->addAttribute('selected', 'selected');

                $opt = $elm->asXML();
            }

            $options[] = $opt;
        }

        $params['options'] = join('', $options);

        return Utils::format($this->getTemplate('select'), $params);
    }

    /**
     * @param array $defaultParams
     * @param array $params
     *
     * @return array
     */
    protected function makeParams(array $defaultParams, array $params): array
    {
        $extra = array_filter($params, function($v, $k) use ($defaultParams): bool {
            return !in_array($k, array_keys($defaultParams));
        }, \ARRAY_FILTER_USE_BOTH);

        if (!empty($extra)) {
            $extraAttributes = [];

            foreach ($extra as $k => $v) {
                $extraAttributes[] = sprintf('%s="%s"', $k, $v);
                unset($params[$k]);
            }

            $params['extra'] = ' ' . join(' ', $extraAttributes);
        }

        $params = $params + $defaultParams;
        $params['id'] = $params['id'] ?? Utils::camelize($params['name']);
        $params['value'] = $this->requestData[$params['name']]
            ?? $this->defaultData[$params['name']]
            ?? $params['value'];

        if (!empty($this->errors[$params['name']])) {
            $class = explode(' ', $params['class']);
            $class[] = 'error';
            $params['class'] = join(' ', $class);
        }

        return $params;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getTemplate(string $name): string
    {
        return $this->userDefinedTemplates[$name] ?? $this->defaultTemplates[$name] ?? '';
    }

    /**
     * @return void
     */
    public function resetTemplates(): void
    {
        $this->userDefinedTemplates = [];
    }

    /**
     * @param string $name
     * @param string $template
     *
     * @return void
     */
    public function setTemplate(string $name, string $template): void
    {
        $this->userDefinedTemplates[$name] = $template;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function setDefaultData(array $data): void
    {
        $this->defaultData = $data;
    }

    /**
     * @return void
     */
    public function resetDefaultData(): void
    {
        $this->defaultData = [];
    }

    /**
     * @param array $errors
     *
     * @return void
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return void
     */
    public function resetErrors(): void
    {
        $this->errors = [];
    }
}
