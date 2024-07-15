
# 環境建置

```
$ git clone https://github.com/yuh926323/asiayo-pre-test
$ cd asiayo-pre-test
$ docker-composer up -d
```

# 執行測試

```
$ docker exec -it asiayo-pre-test-chester-yang bash  // 進入容器
/works/asiayo-pre-test# php artisan test --coverage
```

# 題目一

```sql
SELECT
    `bnbs`.`id` as "旅宿 ID (bnb_id)",
    `bnbs`.`name` as "旅宿名稱(bnb_name)",
    SUM(`amount`) as "5月總金額(may_amount)"
FROM `orders`
INNER JOIN `bnbs` ON `orders`.`bnb_id` = `bnbs`.`id`
WHERE `created_at` LIKE '2023-05%'
AND `currency` LIKE 'TWD'
GROUP BY `bnb_id`
ORDER BY SUM(`amount`) DESC
LIMIT 10;
```

# 題目二

1. 針對 created_at 及 currency 進行複合索引。
2. 在日期篩選改用 `BETWEEN` 或 `>=`, `<` 進而利用索引進行篩選。
3. 進行 EXPLAIN 後發現 orders 的 type 是 ALL 代表進行全表掃描，效率上來說是相對慢的。
```
id|select_type|table |partitions|type  |possible_keys                 |key    |key_len|ref              |rows|filtered|Extra                                       |
--+-----------+------+----------+------+------------------------------+-------+-------+-----------------+----+--------+--------------------------------------------+
 1|SIMPLE     |orders|          |ALL   |idx_orders_created_at_currency|       |       |                 |1414|    1.23|Using where; Using temporary; Using filesort|
 1|SIMPLE     |bnbs  |          |eq_ref|PRIMARY,idx_bnbs_id           |PRIMARY|4      |pks.orders.bnb_id|   1|   100.0|                                            |
```
即使打上複合索引後再進行 EXPLAIN 但發現並沒有實際的效果，因此改用覆蓋索引的方式進行索引(created_at, currency, bnb_id, amount)：

```sql
CREATE INDEX idx_orders_created_at_currency_bnb_id_amount ON orders(created_at, currency, bnb_id, amount);
```

觀察 EXPLAIN 結果成功生效，而 type 也變為 range。

```
id|select_type|table |partitions|type  |possible_keys                               |key                                         |key_len|ref              |rows|filtered|Extra                                                    |
--+-----------+------+----------+------+--------------------------------------------+--------------------------------------------+-------+-----------------+----+--------+---------------------------------------------------------+
 1|SIMPLE     |orders|          |range |idx_orders_created_at_currency_bnb_id_amount|idx_orders_created_at_currency_bnb_id_amount|16     |                 |1395|   11.11|Using where; Using index; Using temporary; Using filesort|
 1|SIMPLE     |bnbs  |          |eq_ref|PRIMARY,idx_bnbs_id                         |PRIMARY                                     |4      |pks.orders.bnb_id|   1|   100.0|                                                         |
```

修改後的語句調整為：
```sql
SELECT
    `bnbs`.`id` as "旅宿 ID (bnb_id)",
    `bnbs`.`name` as "旅宿名稱(bnb_name)",
    SUM(`amount`) as "5月總金額(may_amount)"
FROM `orders`
INNER JOIN `bnbs` ON `orders`.`bnb_id` = `bnbs`.`id`
WHERE `created_at` BETWEEN '2023-05-01' AND '2023-05-31 23:59:59' -- 如使用 BETWEEN，需特別注意時間的部分，未填寫視為 00:00:00
AND `currency` LIKE 'TWD'
GROUP BY `bnb_id`
ORDER BY SUM(`amount`) DESC
LIMIT 10;
```

如果要繼續改善，可能要直接新增一個總表，將需要的數據進行整理，並存進這個總表並做好索引，查詢的時候改成直接查詢總表，不過這樣其實只是將 query 整理數據的時間變為事先執行好，實際上並沒有減少執行時間。好處是當使用者使用時可以最快速的獲得資訊，壞處是當有資訊變動的時候，就需要重新進行一次數據整理。

# SOLID 原則說明
1. 單一職責原則 (SRP): 每個類別只有一個職責，如：
   * `OrderValidator` : 負責驗證請求。
   * `OrderService` : 負責業務邏輯 (轉換貨幣)。
2. 開放封閉原則 (OCP): 軟體實體應對擴展是開放的，但對修改則是封閉的。如果未來有其他需要新增的功能，像是將地址統一轉換成中文或英文地址，可以在 `OrderService` 繼續新增業務邏輯，減少修改原有代碼的情況，以避免未預期的錯誤產生。
3. 里氏替換原則 (LSP): 使用介面或抽象類別來確保可以替換具體實作。比如說今天有某些訂單不需要轉換貨幣的功能，而是需要轉換中文或英文地址，那可以考慮新增一個新的 `GreatOrderService` 並一樣繼承 `OrderServiceInterface`，接著替換 `AppServiceProvider` 中的 `OrderService`，便可輕鬆做到功能擴充。
```php
    $this->app->bind(OrderServiceInterface::class, GreatOrderService::class);
```
4. 介面隔離原則 (ISP): 避免類別集中變成一個龐大的類別，像是如果今天把 `OrderServiceInterface` 跟 `OrderValidatorInterface` 都寫在一起，那任何繼承這個大類別的類別，都需要實作與他不相干的功能。又或是今天有其他更為相似的功能各自匯集成一個大類別，當其中有一個類別需要調整，便會牽一髮而動全身，導致修改困難。
5. 依賴反轉原則 (DIP): `OrderController` 依賴於 `OrderServiceInterface` 而不是實體的 `OrderService`，這種做法使得 Controller 可以使用任何實作了 `OrderServiceInterface` 的 class，且高層模組（如 Controller）不應該依賴低層模組（如 service），而應該依賴於抽象。

# 設計模式
1. 策略模式 (Strategy Pattern)：使用策略模式來封裝不同的驗證邏輯。比如說通過定義更多的 ValidatorInterface 來根據當下的需求，進行不同多的驗證策略驗證訂單。
2. 依賴注入 (Dependency Injection)：透過 Laravel `AppServiceProvider->bind()` 將依賴注入容器。將 `OrderServiceInterface` 綁定到具體的 `OrderService` 實現，並在 `OrderController` 中注入 `OrderServiceInterface`，或是 `OrderValidatorInterface` 綁定到具體的 `OrderValidator`，並在 `OrderService` 中注入。