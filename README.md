# Laminas MVC Skeleton Application for Swoole

## Requirements

Do not use laminas-mvc-console as dependency.

## Build and Run

Use Docker for run application

```bash
docker build -f ./Dockerfile -t swoole-php .
```

```bash
docker run --rm -p 9501:9501 -v $(pwd):/app -w /app swoole-php public/index.php
```


