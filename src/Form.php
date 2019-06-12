<?php declare(strict_types=1);

namespace Zyglab\Plates;

use DOMDocument;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

use const ARRAY_FILTER_USE_BOTH;

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
        'error' => '<div class="errors"><ul>{errors}</ul></div>',
        'error_item' => '<li>{error}</li>',
        'input' => '<input type="{type}" name="data[{name}]" id="{id}" value="{value}" class="{class}"{extra}>',
        'label' => '<label for="{id}">{label}</label>',
        'select' => '<select name="data[{name}]" id="{id}" class="{class}"{extra}>{options}</select>',
        'select_option' => '<option value="{value}">{option}</option>',
        'textarea' => '<textarea name="data[{name}]" id="{id}" class="{class}"{extra}>{value}</textarea>'
    ];

    /**
     * @var array
     */
    protected $userDefinedTemplates = [];

    /**
     * Form constructor.
     * @param array $requestData
     * @return void
     */
    public function __construct(array $requestData = [])
    {
        $this->requestData = $requestData;
    }

    /**
     * @param Engine $engine
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
     * @return string
     */
    public function label(string $label, string $name = null, ?string $id = null): string
    {
        if ($name && !$id) {
            $id = Utils::camelize($name);
        }

        return Utils::format($this->getTemplate('label'), compact('label', 'id'));
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @param string $name
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
     * @param array $defaultParams
     * @param array $params
     * @return array
     */
    protected function makeParams(array $defaultParams, array $params): array
    {
        $extra = array_filter($params, function($v, $k) use ($defaultParams): bool {
            return !in_array($k, array_keys($defaultParams));
        }, ARRAY_FILTER_USE_BOTH);

        if (!empty($extra)) {
            $extraAttributes = [];

            foreach ($extra as $k => $v) {
                if (is_bool($v) && $v === true) {
                    $extraAttributes[] = sprintf('%s', $k);
                } else {
                    $extraAttributes[] = sprintf('%s="%s"', $k, $v);
                }

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
     * @param array $params
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
     * @param string $name
     * @param array $params
     * @return string
     */
    public function textarea(string $name, array $params = []): string
    {
        $defaultParams = [
            'class' => '',
            'extra' => '',
            'id' => null,
            'name' => $name,
            'value' => null,
        ];

        $params = $this->makeParams($defaultParams, $params);

        return Utils::format($this->getTemplate('textarea'), $params);
    }

    /**
     * @param string $name
     * @param array $params
     * @return string
     */
    public function select(string $name, array $params = []): string
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
                /** @noinspection PhpUndefinedClassInspection */
                $dom = new DOMDocument();

                $dom->loadHTML($opt);

                $option = $dom->getElementsByTagName('option')->item(0);

                if (!$option) {
                    continue;
                }

                $attr = $dom->createAttribute('selected');

                $attr->value = 'selected';

                $option->appendChild($attr);

                $html = $dom->saveHTML($option);

                if (!$html) {
                    continue;
                }

                $opt = str_replace("\n", '', $html);
            }

            $options[] = $opt;
        }

        $params['options'] = join('', $options);

        return Utils::format($this->getTemplate('select'), $params);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getTemplate(string $name): string
    {
        return $this->userDefinedTemplates[$name] ?? $this->defaultTemplates[$name] ?? '';
    }

    /**
     * @param string $name
     * @param string $template
     */
    public function setTemplate(string $name, string $template): void
    {
        $this->userDefinedTemplates[$name] = $template;
    }

    /**
     * @param array $array
     */
    public function setRequestData(array $array): void
    {
        $this->requestData = $array;
    }

    /**
     * @param array $data
     */
    public function setDefaultData(array $data): void
    {
        $this->defaultData = $data;
    }

    /**
     * @return void
     */
    public function resetRequestData(): void
    {
        $this->requestData = [];
    }

    /**
     * @return void
     */
    public function resetDefaultData(): void
    {
        $this->defaultData = [];
    }

    /**
     * @return void
     */
    public function resetErrors(): void
    {
        $this->errors = [];
    }

    /**
     * @return void
     */
    public function resetTemplates(): void
    {
        $this->userDefinedTemplates = [];
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->resetRequestData();
        $this->resetDefaultData();
        $this->resetErrors();
        $this->resetTemplates();
    }
}
