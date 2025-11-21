<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск адреса в Москве</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .search-form {
            margin-bottom: 30px;
        }
        .search-input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .search-button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .result-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .result-field {
            margin-bottom: 5px;
        }
        .no-results {
            color: #666;
            font-style: italic;
        }
        .type-badge {
            background: #6c757d;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<h1>Поиск адреса в Москве</h1>

<form method="POST" action="{{ route('address.search') }}" class="search-form">
    @csrf
    <input type="text"
           name="address"
           value="{{ $searchQuery ?? '' }}"
           placeholder="Введите адрес в Москве (например: Москва, Тверская ул., 1)..."
           class="search-input"
           required>
    <button type="submit" class="search-button">Найти</button>
</form>

@if(isset($results))
    <h2>Результаты поиска (найдено: {{ count($results) }}):</h2>

    @if(count($results) > 0)
        @foreach($results as $index => $result)
            <div class="result-item">
                <div class="result-field">
                    <strong>Результат #{{ $index + 1 }}</strong>
                    <span class="type-badge">{{ $result['type'] }}</span>
                </div>
                <div class="result-field"><strong>Адрес:</strong> {{ $result['address'] }}</div>
                <div class="result-field"><strong>Полный адрес:</strong> {{ $result['full_address'] }}</div>
                <div class="result-field"><strong>Район:</strong> {{ $result['district'] ?: 'Не указан' }}</div>
                <div class="result-field"><strong>Метро:</strong> {{ $result['metro'] ?: 'Не указано' }}</div>
                <div class="result-field"><strong>Улица:</strong> {{ $result['street'] ?: 'Не указана' }}</div>
                <div class="result-field"><strong>Дом:</strong> {{ $result['house'] ?: 'Не указан' }}</div>
            </div>
        @endforeach
    @else
        <p class="no-results">По вашему запросу ничего не найдено. Попробуйте уточнить адрес.</p>
    @endif
@endif
</body>
</html>
