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
            format: value => `R$ ${parseFloat(value).toFixed(2)}`
        },
        {
            label: "Data",
            key: "date",
            format: value => new Date(value).toLocaleDateString('pt-BR')
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
