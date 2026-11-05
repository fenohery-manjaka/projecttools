/**
 * Dates are sent by the server as "YYYY-MM-DD" (local calendar days, no time zone).
 */
export function parseDay(date: string): Date {
    return new Date(`${date.slice(0, 10)}T00:00`);
}

export function formatDate(
    date: string,
    style: 'short' | 'long' = 'short',
): string {
    return parseDay(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: style === 'long' ? 'long' : 'short',
        year: 'numeric',
    });
}

/**
 * "12 nov." — for cards and lists where the year is obvious.
 */
export function formatDayMonth(date: string): string {
    return parseDay(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
    });
}

/**
 * "il y a 3 h", "il y a 2 j" — for comments and notifications.
 */
export function timeAgo(iso: string): string {
    const seconds = Math.round((Date.now() - new Date(iso).getTime()) / 1000);
    const rtf = new Intl.RelativeTimeFormat('fr', { numeric: 'auto' });

    if (seconds < 60) {
        return 'à l’instant';
    }

    const units: [Intl.RelativeTimeFormatUnit, number][] = [
        ['minute', 60],
        ['hour', 3600],
        ['day', 86400],
        ['week', 604800],
        ['month', 2592000],
        ['year', 31536000],
    ];

    let unit: Intl.RelativeTimeFormatUnit = 'minute';
    let size = 60;

    for (const [name, length] of units) {
        if (seconds >= length) {
            unit = name;
            size = length;
        }
    }

    return rtf.format(-Math.floor(seconds / size), unit);
}
