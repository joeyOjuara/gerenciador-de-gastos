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
    ]
}
