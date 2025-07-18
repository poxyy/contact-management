export function parseErrors(errors) {
    if (!errors) return 'Unknown error occurred';
    if (typeof errors === 'string') return errors;
    if (typeof errors === 'object') {
        return Object.values(errors).flat().join(', ');
    }
    return 'Unexpected error format';
}
