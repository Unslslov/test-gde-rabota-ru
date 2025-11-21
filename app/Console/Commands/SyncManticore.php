<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\ManticoreHelper;
use Illuminate\Support\Facades\DB;

class SyncManticore extends Command
{
    protected $signature = 'manticore:sync';
    protected $description = 'Синхронизирует данные из MySQL в Manticore';

    public function handle()
    {
        $this->info('Начинаем синхронизацию Manticore...');

        try {
            // 1. Создаем индекс если его нет
            $this->createIndexIfNotExists();

            // 2. Очищаем индекс
            $this->clearIndex();

            // 3. Индексируем данные из MySQL
            $this->indexData();

            $this->info('✅ Синхронизация завершена успешно!');

        } catch (\Exception $e) {
            $this->error('❌ Ошибка синхронизации: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function createIndexIfNotExists()
    {
        $this->info('Проверяем наличие индекса vacancy...');

        $pdo = ManticoreHelper::getConnection();

        try {
            // Пытаемся выполнить запрос к индексу - если его нет, будет исключение
            $pdo->query('SELECT COUNT(*) FROM vacancy LIMIT 1');
            $this->info('✅ Индекс vacancy уже существует');
        } catch (\Exception $e) {
            // Если индекса нет - создаем его
            $this->info('Создаем индекс vacancy...');

            $sql = "CREATE TABLE vacancy (
                id BIGINT,
                title TEXT,
                salary_from INT,
                salary_to INT,
                country_id INT,
                region_id INT,
                city_id INT,
                created_at TIMESTAMP
            )";

            $pdo->exec($sql);
            $this->info('✅ RT-индекс vacancy создан');
        }
    }

    private function clearIndex()
    {
        $this->info('Очищаем индекс vacancy...');
        try {
            ManticoreHelper::statement('TRUNCATE RTINDEX vacancy');
            $this->info('✅ Индекс очищен');
        } catch (\Exception $e) {
            $this->warn('⚠️ Не удалось очистить индекс: ' . $e->getMessage());
        }
    }

    private function indexData()
    {
        $this->info('Индексируем данные из MySQL...');

        // Получаем данные из MySQL
        $vacancies = DB::table('vacancies')->get();

        $pdo = ManticoreHelper::getConnection();
        $total = 0;

        foreach ($vacancies as $vacancy) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO vacancy
                    (id, title, salary_from, salary_to, country_id, region_id, city_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $vacancy->id,
                    $vacancy->title,
                    $vacancy->salary_from ?? 0,
                    $vacancy->salary_to ?? 0,
                    $vacancy->country_id ?? 0,
                    $vacancy->region_id ?? 0,
                    $vacancy->city_id ?? 0,
                    $vacancy->created_at ?? time()
                ]);

                $total++;

                if ($total % 100 === 0) {
                    $this->info("Обработано: {$total} записей...");
                }

            } catch (\Exception $e) {
                $this->warn("⚠️ Ошибка при индексации записи {$vacancy->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Всего проиндексировано: {$total} записей");
    }
}
