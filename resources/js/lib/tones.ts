import type { BadgeVariants } from '@/components/ui/badge';

/**
 * Colours of the prototype for people: each member keeps the same colour everywhere.
 */
const MEMBER_COLORS = [
    '#2F6F5E',
    '#B26A28',
    '#3E6A9E',
    '#8A4E8C',
    '#A8463E',
    '#5A6E2E',
    '#9A6B4F',
    '#2F7C86',
];

export function memberColor(id: number): string {
    return MEMBER_COLORS[Math.abs(id) % MEMBER_COLORS.length];
}

type Tone = NonNullable<BadgeVariants['variant']>;

const STATUS_TONES: string[] = [
    'secondary',
    'info',
    'warn',
    'ok',
    'bad',
    'accent',
];

/**
 * Pill colour of a configurable status: its tone is a Badge variant (StatusTone on the server).
 */
export function statusTone(tone: string): Tone {
    return STATUS_TONES.includes(tone) ? (tone as Tone) : 'default';
}

/**
 * Pill colour of a task priority, as in the prototype.
 */
const PRIORITY_TONES: Record<string, Tone> = {
    urgent: 'bad',
    high: 'warn',
    normal: 'secondary',
    low: 'secondary',
};

export function priorityTone(priority: string): Tone {
    return PRIORITY_TONES[priority] ?? 'default';
}
