## 🚀 Установка
### Клонируйте репозиторий:
```bash
git clone https://github.com/Unslslov/StorageTask.git 
cd storagetask
```
### Настройте .env файл:
```bash
cp .env.example .env
php artisan key:generate
```

### Добавьте в .env ключ:
```bash
YANDEX_GEOCODER_API_KEY=
```

### Либо добавьте в .env следующий ключ:
```bash
YANDEX_GEOCODER_API_KEY=e2b4fe29-429b-4c14-9b59-4f0052c6c3d0
```
### Запуск проекта:
```bash
docker-compose up -d --build
```
### Выполните миграцию и заполните данные таблицы :
```bash
docker-compose up -d --build
docker-compose exec app php artisan migrate:fresh --seed
```
### Выполните миграцию и заполните данные таблицы :
```bash
docker-compose up -d --build
docker-compose exec app php artisan migrate:fresh --seed
```

### Для синхронизации таблицы mysql с ManticoreSearch используюйте команду :
```bash
docker-compose exec app php artisan manticore:sync
```

## 🌐 Страницы для опрования функционала
### Поиск по улице, району, метро:
```bash
http://localhost:8080/api/
```
### Manticore:
```bash
http://localhost:8080/api/test-manticore
```
## 🌐 Доступные API-маршруты
### Поиск по улице, району, метро:
```bash
GET api/
POST api/search 
```
### Manticore:
```bash
GET api/test-manticore
```
