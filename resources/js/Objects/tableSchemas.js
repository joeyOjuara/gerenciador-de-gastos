import { formatCurrency } from '@/utils/formatCurrency'

export const tableSchemas = {
    transactions: [
        {
            label: "Descrição",
            key: "description"
        },
        {
            label: "Categoria",
            key: "category.name"
        },
        {
            label: "Origem",
            key: "source",
            format: (_, row) => row.payment_method === 'credit_card' ? row.credit_card?.name : row.account?.name
        },
        {
            label: "Forma de Pagamento",
            key: "payment.name"
        },
        {
            label: "Valor",
            key: "amount",
            format: value => formatCurrency(value)
        },
        {
            label: "Data",
            key: "date",
            format: value => new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR')
        }
    ],

    categories: [
        {
            label: "Nome",
            key: "name"
        }
    ],

    payments: [
        {
            label: "Nome",
            key: "name"
        }
    ],

    accounts: [
        {
            label: "Nome",
            key: "name"
        },
        {
            label: "Saldo Inicial",
            key: "initial_balance",
            format: value => formatCurrency(value)
        },
        {
            label: "Saldo Atual",
            key: "current_balance",
            format: value => formatCurrency(value)
        }
    ],

    creditCards: [
        { label: "Nome", key: "name" },
        { label: "Limite", key: "limit_amount", format: value => formatCurrency(value) },
        { label: "Usado", key: "used_limit", format: value => formatCurrency(value ?? 0) },
        { label: "Disponível", key: "available_limit", format: value => formatCurrency(value ?? 0) },
        { label: "Fechamento", key: "closing_day" },
        { label: "Vencimento", key: "due_day" }
    ]
}
