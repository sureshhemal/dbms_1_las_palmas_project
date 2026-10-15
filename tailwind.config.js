import colors from 'tailwindcss/colors'

export const projectColors = {
    brand: colors.sky,
    success: colors.emerald,
    warning: colors.amber,
    danger: colors.red,
    info: colors.sky,
}

export default {
    theme: {
        extend: {
            colors: projectColors,
        },
    },
}
