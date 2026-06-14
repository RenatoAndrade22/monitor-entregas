# Painel de Desempenho e Gestão de Entregas

Sistema para acompanhar a performance de motoristas e gerenciar pedidos. Permite visualizar o panorama da operação e editar ordens de serviço em lote sem recarregar a página.

## Funcionalidades
- Dashboard com total de pedidos, entregues, pendentes e gráfico de status.
- Listagem de motoristas com cálculo automático de taxa de sucesso.
- Filtros por período e status operacional.
- Edição rápida de endereços e status dos pedidos via modais reativos ( AJAX).

## Instalação e Execução (Via Docker)
Certifique-se de ter o **Docker** e o **Docker Compose** instalados em sua máquina.

1. Clone o repositório e acesse a pasta:
```bash
git clone https://github.com/RenatoAndrade22/monitor-entregas.git
cd monitor-entregas
```

2. Configure o arquivo de ambiente
(Abra o .env e configure as credenciais do seu banco de dados: DB_DATABASE, DB_USERNAME, DB_PASSWORD)
```bash
cp .env.example .env
```

3. Suba os containers da aplicação:
```bash
docker compose up -d --build
```

4. Instale as dependências:
```bash
docker compose exec laravel.test composer install
```


5. Gere a chave da aplicação:
```bash
docker compose exec laravel.test php artisan key:generate
```

6. Crie as tabelas no banco:
```bash
docker compose exec laravel.test php artisan migrate --seed
```

Acesse no navegador: http://localhost:83