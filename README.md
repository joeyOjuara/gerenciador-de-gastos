# Gerenciador de Gastos

Aplicação web de gerenciamento financeiro pessoal construída com **Laravel 12** e **Vue.js 3**, permitindo o controle de receitas, despesas, categorias e formas de pagamento com visualizações em gráficos.

---

## Funcionalidades

- **Dashboard** — Visão geral financeira com cards de resumo, gráfico de barras por categoria e gráfico de linha dos últimos 6 meses
- **Transações (Despesas)** — Cadastro, edição e exclusão de gastos com suporte a transações recorrentes (semanal, mensal, anual)
- **Receitas** — Cadastro, edição e exclusão de entradas financeiras
- **Categorias** — Gerenciamento de categorias de despesa
- **Formas de Pagamento** — Gerenciamento de métodos de pagamento (cartão de crédito, débito, dinheiro, etc.)
- **Perfil** — Edição de dados do usuário, senha e exclusão de conta
- **Autenticação completa** — Registro, login, verificação de e-mail e redefinição de senha

---

## Stack Tecnológica

### Backend
| Tecnologia | Versão |
|---|---|
| PHP | 8.2+ |
| Laravel | 12.x |
| Inertia.js (server) | 2.x |
| Laravel Sanctum | 4.x |
| Ziggy | 2.x |

### Frontend
| Tecnologia | Versão |
|---|---|
| Vue.js | 3.4 |
| Inertia.js (client) | 2.x |
| Tailwind CSS | 3.2 |
| Chart.js | 4.5 |
| Vue-ChartJS | 5.3 |
| Vite | 7.x |

---

## Pré-requisitos

- PHP >= 8.2
- Composer
- Node.js >= 18
- SQLite (padrão) ou MySQL/PostgreSQL

---

## Instalação

### Instalação automática

```bash
git clone <url-do-repositorio>
cd gerenciador-de-gastos
composer setup
```

O script `composer setup` executa automaticamente:
1. `composer install`
2. Cria o arquivo `.env` a partir do `.env.example`
3. `php artisan key:generate`
4. `php artisan migrate --force`
5. `npm install`
6. `npm run build`

### Instalação manual

```bash
git clone <url-do-repositorio>
cd gerenciador-de-gastos

# Dependências PHP
composer install

# Variáveis de ambiente
cp .env.example .env
php artisan key:generate

# Banco de dados
php artisan migrate

# Dependências Node e build
npm install
npm run build
```

---

## Executando em desenvolvimento

```bash
composer dev
```

Esse comando sobe em paralelo:
- Servidor Laravel (`php artisan serve`)
- Queue listener (`php artisan queue:listen`)
- Visualizador de logs (`php artisan pail`)
- Servidor de desenvolvimento Vite (`npm run dev`)

---

## Estrutura do Projeto

```
app/
├── Console/Commands/
│   ├── GenerateRecurringTransactions.php   # Gera transações recorrentes
│   └── MakeRepositoryCommand.php           # Comando para criar repositórios
├── Contracts/                              # Interfaces dos repositórios
├── Http/
│   ├── Controllers/                        # Controllers da aplicação
│   └── Requests/                           # Form Requests (validação)
├── Models/                                 # Modelos Eloquent
├── Providers/
│   └── RepositoryServiceProvider.php       # Bind interfaces → implementações
└── Repositories/                           # Implementações dos repositórios

resources/js/
├── Components/                             # Componentes Vue reutilizáveis
├── Layouts/                                # Layouts da aplicação
├── Pages/                                  # Páginas Inertia (Vue)
│   ├── Auth/                               # Telas de autenticação
│   ├── Categories/                         # Gerenciamento de categorias
│   ├── Dashboard.vue                       # Dashboard principal
│   ├── Incomes/                            # Gerenciamento de receitas
│   ├── Payments/                           # Formas de pagamento
│   ├── Profile/                            # Perfil do usuário
│   └── Transactions/                       # Gerenciamento de despesas
├── composables/                            # Composables Vue
└── utils/                                  # Utilitários (ex: formatCurrency)

database/migrations/                        # Migrations do banco de dados
routes/web.php                              # Definição de rotas
```

---

## Modelos e Relacionamentos

```
User
 └── hasMany → Transaction

Category
 └── hasMany → Transaction

Payment
 └── hasMany → Transaction

Transaction
 ├── belongsTo → User
 ├── belongsTo → Category
 ├── belongsTo → Payment
 ├── type: "income" | "expense"
```

---

## Transações Recorrentes

A aplicação suporta transações recorrentes. Para gerar automaticamente as transações devidas, execute o comando:

```bash
php artisan transactions:generate-recurring
```

Recomenda-se agendar esse comando para rodar diariamente via Laravel Scheduler. Adicione ao `app/Console/Kernel.php`:

```php
$schedule->command('transactions:generate-recurring')->daily();
```

---

## Rotas Principais

| Método | URI | Descrição |
|---|---|---|
| GET | `/dashboard` | Dashboard financeiro |
| GET/POST | `/transactions` | Listar e criar despesas |
| PUT/DELETE | `/transactions/{id}` | Editar e excluir despesa |
| GET/POST | `/incomes` | Listar e criar receitas |
| PUT/DELETE | `/incomes/{id}` | Editar e excluir receita |
| GET/POST | `/categories` | Listar e criar categorias |
| PUT/DELETE | `/categories/{id}` | Editar e excluir categoria |
| GET/POST | `/payments` | Listar e criar formas de pagamento |
| PUT/DELETE | `/payments/{id}` | Editar e excluir forma de pagamento |
| GET/PUT | `/profile` | Perfil do usuário |

---

## Testes

```bash
composer test
# ou
php artisan test
```

---

## Padrões de Arquitetura

- **Repository Pattern** — Abstração da camada de acesso a dados via interfaces em `app/Contracts/` e implementações em `app/Repositories/`
- **Form Requests** — Validação centralizada em `app/Http/Requests/`
- **Inertia.js** — Comunicação entre Laravel e Vue.js sem necessidade de API REST separada

---

## Licença

Este projeto está sob a licença [MIT](https://opensource.org/licenses/MIT).
