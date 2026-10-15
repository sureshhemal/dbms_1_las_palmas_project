import type { NotivueTheme } from 'notivue'
import { lightTheme } from 'notivue'
import { projectColors } from '../../../tailwind.config.js'

export const notificationTheme: NotivueTheme = {
    ...lightTheme,
    '--nv-global-bg': 'var(--card)',
    '--nv-global-fg': 'var(--card-foreground)',
    '--nv-global-border': 'var(--border)',
    '--nv-border-width': '1px',
    '--nv-radius': 'var(--radius)',
    '--nv-success-accent': projectColors.success[500],
    '--nv-error-accent': projectColors.danger[500],
    '--nv-warning-accent': projectColors.warning[500],
    '--nv-info-accent': projectColors.info[500],
    '--nv-promise-accent': projectColors.brand[500],
}
