<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => [
                'nullable', // Поле может быть пустым
                'string',   // Должно быть строкой
                'max:255',  // Максимальная длина
                'regex:/^[a-zA-Z0-9\s\-\_\.\@\+]*$/', // Безопасные символы
            ],
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100', // Ограничиваем лимит для безопасности
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'filters' => [
                'nullable',
                'array',
            ],
            'filters.*' => [
                'string',
            ],
            'sort_by' => [
                'nullable',
                'string',
                'in:relevance,date,title,salary', // Только разрешенные поля
            ],
            'sort_order' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'q.string' => 'Поисковый запрос должен быть строкой',
            'q.max' => 'Поисковый запрос не должен превышать 255 символов',
            'q.regex' => 'Поисковый запрос содержит недопустимые символы',
            'limit.integer' => 'Лимит должен быть числом',
            'limit.min' => 'Лимит должен быть не менее 1',
            'limit.max' => 'Лимит не должен превышать 100',
            'page.integer' => 'Страница должна быть числом',
            'page.min' => 'Страница должна быть не менее 1',
            'filters.array' => 'Фильтры должны быть массивом',
            'sort_by.in' => 'Сортировка возможна только по: relevance, date, title, salary',
            'sort_order.in' => 'Порядок сортировки может быть только asc или desc',
        ];
    }

    /**
     * Prepare the data for validation.
     * Можно обработать данные перед валидацией
     */
    protected function prepareForValidation(): void
    {
        // Убираем лишние пробелы
        if ($this->has('q')) {
            $this->merge([
                'q' => trim($this->input('q'))
            ]);
        }

        // Устанавливаем значения по умолчанию
        $this->merge([
            'limit' => $this->input('limit', 10),
            'page' => $this->input('page', 1),
            'sort_by' => $this->input('sort_by', 'relevance'),
            'sort_order' => $this->input('sort_order', 'desc'),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     * Возвращаем JSON ошибки вместо редиректа
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Get the validated data with defaults.
     * Можно добавить свои методы для удобства
     */
    public function getSearchParams(): array
    {
        $validated = $this->validated();

        return [
            'query' => $validated['q'] ?? '',
            'limit' => $validated['limit'] ?? 10,
            'page' => $validated['page'] ?? 1,
            'filters' => $validated['filters'] ?? [],
            'sort_by' => $validated['sort_by'] ?? 'relevance',
            'sort_order' => $validated['sort_order'] ?? 'desc',
        ];
    }
}
