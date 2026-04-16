import { reactive } from 'vue'

const state = reactive({ toasts: [] })
let nextId = 0

export function useToast() {
    const show = (message, type = 'success', duration = 3500) => {
        const id = ++nextId
        state.toasts.push({ id, message, type })
        setTimeout(() => remove(id), duration)
    }

    const remove = (id) => {
        const index = state.toasts.findIndex(t => t.id === id)
        if (index > -1) state.toasts.splice(index, 1)
    }

    return { toasts: state.toasts, show, remove }
}
