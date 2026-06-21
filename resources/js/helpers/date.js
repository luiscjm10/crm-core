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

export function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function formatMinutes(minutes) {
    if (!minutes || minutes <= 0) return '0 min';
    if (minutes < 60) return `${minutes} min`;
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return m > 0 ? `${h}h ${m}min` : `${h}h`;
}

export function isOverdue(task) {
    if (!task.due_date || task.status === 'done' || task.status === 'cancelled') return false;
    const [y, m, d] = task.due_date.split('-');
    const dueDate = new Date(+y, +m - 1, +d);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return dueDate < today;
}
