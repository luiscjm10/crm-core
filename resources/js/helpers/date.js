export function formatDateOnly(dateStr) {
    if (!dateStr) return '—';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
}

export function parseDateOnly(dateStr) {
    if (!dateStr) return null;
    const [y, m, d] = dateStr.split('-');
    return new Date(+y, +m - 1, +d);
}

export function isOverdue(task) {
    if (!task.due_date || task.status === 'done' || task.status === 'cancelled') return false;
    const [y, m, d] = task.due_date.split('-');
    const dueDate = new Date(+y, +m - 1, +d);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return dueDate < today;
}
