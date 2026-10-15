import axios from 'axios'
import { push } from 'notivue'

const statusCodesToNotify = [401, 403, 404, 422, 500, 502]

export const http = axios.create({
    baseURL: '/api/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
})

http.interceptors.response.use(
    (response) => response,
    (error) => {
        const statusCode = error.response?.status

        if (statusCodesToNotify.includes(statusCode)) {
            push.error({
                title: 'Oops...',
                message: error.response.data.message,
            })
        }

        return Promise.reject(error)
    },
)
