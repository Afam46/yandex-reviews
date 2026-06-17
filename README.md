# Yandex Reviews Parser

Парсер отзывов с организаций Яндекс карт


## Demo

[Yandex Reviews](http://155.212.147.12:8093/)

Email:
test@test.com<br>

Password:
12345678

## Примечание

Демо размещено на VPS с ограниченными ресурсами (1 vCPU, 2 GB RAM)

Скорость парсинга зависит от количества отзывов и производительности сервера<br>
На локальной машине парсинг выполняется заметно быстрее

## Стек

| Категория | Технологии |
|------------|------------|
| Frontend | Vue 3, Vue Router, Axios, TailwindCSS, Pinia |
| Backend | Laravel, Sanctum, MySQL, Redis, Queue, PHPUnit |
| Парсинг | Node.js, Playwright, Chromium Headless |

---

# Возможности

## Работа с организациями

- добавление ссылки на организацию Яндекс карт
- валидация ссылки
- сохранение организации в БД
- удаление организации

После добавления организации запускается polling каждые 3 секунды.
Интерфейс обновляет статус организации до completed или failed без перезагрузки страницы

## Работа с отзывами

Для каждой организации отображаются:

- средний рейтинг
- количество отзывов
- количество оценок
- последние отзывы (лимит 600)

Каждый отзыв содержит:

- автора
- дату
- оценку
- текст

Отзывы хранятся в MySQL, а результаты пагинации кэшируются в Redis

---

# Архитектура

### Services

Вся логика взаимодействия с парсером вынесена в сервис (App\Services\YandexReviewParser)

Сервис запускает Node.js-скрипт и получает данные в JSON

### Jobs

Парсинг запускается асинхронно (ParseOrganizationJob)

Использование очередей позволяет:

- не блокировать HTTP-запрос
- избежать таймаутов
- выполнять тяжёлый парсинг в фоне

Queue + Redis + Supervisor

### БД

```
organizations
- user_id
- url
- title
- rating
- ratings_count
- reviews_count
- status

reviews
- organization_id
- author
- text
- rating
- review_date
```

---

# Подход к парсингу

Используется Playwright + Headless Chromium

Алгоритм:

1. открывается карточка организации
2. переход на вкладку отзывов
3. отслеживаются внутренние запросы fetchReviews
4. отзывы собираются из JSON-ответов
5. выполняется прокрутка контейнера отзывов
6. процесс завершается, если:

   - получены все страницы
   - собрано 600 отзывов
   - превышен лимит времени
   - новые отзывы перестали приходить

Из ответа дополнительно извлекаются:

- рейтинг
- количество отзывов
- количество оценок

## Статусы парсинга

Организация может находиться в одном из состояний:

- pending — создана запись в БД
- parsing — выполняется парсинг
- completed — парсинг успешно завершён
- failed — произошла ошибка

# Защита от ошибок

Обрабатываются следующие сценарии:

- некорректная ссылка
- организация не найдена
- пустой ответ парсера
- превышение времени парсинга
- ошибки запуска Chromium
- частичная недоступность данных Яндекс Карт

При ошибке организация получает статус failed

---

# Почему выбран именно такой подход

Отзывы парсятся один раз и сохраняются в БД

Постраничная навигация работает по локально сохранённым данным и частично кэшируется в Redis

Такой подход позволяет:

- избежать повторного запуска тяжёлого парсинга
- не обращаться к Яндекс Картам при каждой смене страницы
- снизить нагрузку на внешний источник
- обеспечить быстрый отклик интерфейса

---

# Тестирование

Покрыты feature-тестами:

- создание организации
- получение организаций
- удаление организаций
- получение отзывов
- ограничение доступа к чужим организациям
- успешный сценарий ParseOrganizationJob
- сценарий ошибки ParseOrganizationJob

```
php artisan test
```

---

# Запуск проекта

```
## Требования

- PHP 8.4+
- Node.js 22+
- MySQL
- Redis

git clone https://github.com/Afam46/yandex-reviews.git

cd yandex-reviews

```
## Backend

```
composer install

cp .env.example .env

# Настройка env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yandex_reviews
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=predis

php artisan key:generate

php artisan migrate

php artisan db:seed --class=UserSeeder

php artisan serve

```
## Frontend

```
npm install

# Установка Chromium
npx playwright install chromium

npm run dev

```
## Убедитесь, что Redis запущен
```
# Проверка
redis-cli ping  # должен вернуть PONG

# Если нет:

# Установка (Ubuntu)
sudo apt install redis-server

# Запуск
sudo systemctl start redis
sudo systemctl enable redis

```
## Queue Worker

```
php artisan queue:work --timeout=180

```

---

## Диаграмма процесса

```
User
 ↓
POST /organization
 ↓
ParseOrganizationJob
 ↓
Playwright
 ↓
reviews table
 ↓
status = completed
 ↓
Polling → UI update
```

## Что бы улучшил при наличии большего времени

- WebSocket-обновление статусов вместо polling
- Laravel Horizon для мониторинга очередей
- повторные попытки парсинга для нестабильных карточек
- кэширование популярных организаций
- расширенное покрытие тестами
