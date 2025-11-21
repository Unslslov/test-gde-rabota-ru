<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест ManticoreSearch</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        .search-form { margin-bottom: 30px; }
        .search-input { width: 300px; padding: 10px; margin-right: 10px; }
        .search-button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .result-item { border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 4px; }
        .result-title { font-weight: bold; margin-bottom: 5px; }
        .result-meta { color: #666; font-size: 14px; }
        .status { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .status-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<h1>Тестирование ManticoreSearch</h1>

<div class="search-form">
    <form method="GET" action="{{ route('manticore.test') }}">
        <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Введите поисковый запрос..." class="search-input">
        <button type="submit" class="search-button">Поиск</button>
    </form>
</div>

@if(isset($results) && is_array($results))
    <div class="status status-success">
        ✅ Успешное подключение к ManticoreSearch. Найдено результатов: {{ count($results) }}
    </div>

    @if(count($results) > 0)
        <h2>Результаты поиска:</h2>
        @foreach($results as $result)
            <div class="result-item">
                <div class="result-title">{{ $result['title'] ?? 'Без названия' }}</div>
                <div class="result-meta">
                    Зарплата: {{ $result['salary_from'] ?? '?' }} - {{ $result['salary_to'] ?? '?' }} |
                    Вес: {{ $result['weight'] ?? 'N/A' }} |
                    ID: {{ $result['id'] }}
                </div>
            </div>
        @endforeach
    @else
        <p>По запросу "{{ $searchQuery }}" ничего не найдено.</p>
    @endif
@else
    <div class="status status-error">
        ❌ Ошибка подключения к ManticoreSearch
    </div>
@endif

</body>
</html>
