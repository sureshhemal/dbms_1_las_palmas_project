import { digits, integer, max, required } from '@vee-validate/rules'
import { defineRule } from 'vee-validate'

type RuleContext = {
    field: string
}
defineRule(
    'required',
    (value: unknown, _: unknown, context: RuleContext) =>
        required(value) || `${context.field} is required`,
)

defineRule(
    'integer',
    (value: unknown, _: unknown, context: RuleContext) =>
        integer(value) || `${context.field} must be a whole number`,
)

defineRule(
    'max',
    (value: unknown, [length]: [string], context: RuleContext) =>
        max(value, { length: Number(length) }) ||
        `${context.field} must be at most ${length} characters`,
)

defineRule(
    'digits',
    (value: unknown, [length]: [string], context: RuleContext) =>
        digits(value, { length: Number(length) }) ||
        `${context.field} must be exactly ${length} digits`,
)

defineRule(
    'amount',
    (value: unknown, _: unknown, context: RuleContext) =>
        value === '' ||
        value === null ||
        value === undefined ||
        ((typeof value === 'string' || typeof value === 'number') &&
            /^\d+(\.\d{1,2})?$/.test(String(value))) ||
        `${context.field} must be an amount like 1500 or 1500.50`,
)
