# Mini App

API para cadastro de propostas de empréstimo com processamento assíncrono.

## Requisitos

- Docker e Docker Compose

## Instalação

```bash
git clone https://github.com/erickmolina2002/test-app.git
cd test-app
cp .env.example .env
docker compose up -d --build
```

O entrypoint cuida automaticamente de:
- Instalar dependências (`composer install`)
- Gerar `APP_KEY` se estiver vazia
- Rodar migrations

A API estará disponível em `http://localhost:8000`.

## Testando a API

Acesse o Swagger para testar direto no navegador:

**http://localhost:8000/docs/api**

Clique em `POST /proposal` → `Try it out` → preencha o body → `Execute`.

## Testes automatizados

```bash
docker exec app-app php artisan test
```

## Fluxo de processamento

```
POST /proposal → salva no banco (status: pending) → retorna 201
                      ↓ (assíncrono via Redis)
              RegisterProposalJob → chama API de autorização → status: registered
                      ↓
              SendProposalNotificationJob → chama API de notificação → status: completed
```

Os jobs possuem retry automático (5 tentativas com backoff progressivo). Se todas as tentativas falharem, o status é atualizado para `failed`.

## Monitoramento

- **Telescope:** http://localhost:8000/telescope
- **Logs do worker:** `docker logs app-queue -f`
- **Fila:** `docker exec app-app php artisan queue:monitor redis:default`
- **Jobs falhados:** `docker exec app-app php artisan queue:failed`

## Stack

- PHP 8.4 / Laravel 13
- PostgreSQL 16
- Redis 7 (filas)
- Docker
