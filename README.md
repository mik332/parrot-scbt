Исходные данные, которые помогут вам быстрее собрать и развернуть приложение. Можете доработать по своему вкусу.

Подготовка базового образа с собранным драйвером postgres
```bash
docker build --no-cache -t php-pgsql:8.2.0-0.1 ./php
```

Сборка имиджа для тестирования
```bash
docker build --no-cache -t registry.sovcombank.group/s-devops/parrot:v0.1.1 ./app
docker compose down && docker build --no-cache -t parrot:v0.1.1 ./app && docker compose up -d
docker compose down && docker build --no-cache -t parrot:v0.1.1 ./app && docker compose --env-file ./.env up -d
```

SQL to/from docker
```sql
# cat sql/flight.sql | docker exec -i parrot_db psql -U postgres
cat sql/flight.sql | docker exec -i parrot_db psql -U flight_user -d flight_base
docker exec -t parrot_db pg_dumpall -c -U flight_user -d flight_base > sql/dump_`date +%d-%m-%Y"_"%H_%M_%S`.sql
```
