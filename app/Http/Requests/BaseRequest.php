<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

abstract class BaseRequest extends FormRequest
{
    protected array $rules = [];

    /**
     * The current request.
     */
    protected Request $currentRequest;

    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    abstract public function rules(): array;

    /**
     * Returns the default rules array.
     *
     * @return array
     */
    protected function init(): void
    {
        $this->currentRequest = request();

        if ($this->currentRequest->has('direction') && ! is_null($this->currentRequest->direction)) {
            $this->currentRequest->merge(['direction' => strtolower($this->currentRequest->direction)]);
        }
    }

    /**
     * Merge additional rules with the default rules.
     */
    public function mergeRules(array $rulesToMerge): array
    {
        $defaultRules = $this->getHttpMethod() === 'GET' ? $this->getDefaultGetRules() : [];
        $this->rules = array_merge($this->rules, $defaultRules, $rulesToMerge);

        return $this->rules;
    }

    /**
     * Get the HTTP method of the request.
     */
    public function getHttpMethod(): string
    {
        return $this->currentRequest->getMethod();
    }

    /**
     * Returns the default rules array.
     */
    protected function getDefaultGetRules(): array
    {
        return [
            'sort' => ['string', 'min:2', 'max:250'],
            'direction' => ['string', 'in:asc,desc'],
            'limit' => ['integer'],
            'page' => ['integer'],
        ];
    }
}
