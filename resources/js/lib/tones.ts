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

/**
 * Pill colour of a project status, as in the prototype.
 */
const PROJECT_STATUS_TONES: Record<
    string,
    NonNullable<BadgeVariants['variant']>
> = {
    planned: 'secondary',
    active: 'info',
    paused: 'warn',
    done: 'ok',
    abandoned: 'bad',
};

export function projectStatusTone(
    status: string,
): NonNullable<BadgeVariants['variant']> {
    return PROJECT_STATUS_TONES[status] ?? 'default';
}
