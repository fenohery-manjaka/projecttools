import type { Status } from './statuses';

/**
 * A task as sent by TaskPresenter::summary() (list, board, My tasks, home).
 */
export type TaskSummary = {
    id: number;
    title: string;
    project: { id: number; name: string; color: string };
    status: Status;
    priority: { value: string; label: string };
    assignees: { id: number; name: string }[];
    due_on: string | null;
    is_late: boolean;
    subtasks: { done: number; total: number } | null;
};

/**
 * Options of the task form, sent by TaskFormOptions (optional prop "taskForm" for the dialog).
 */
export type TaskFormOptions = {
    projects: {
        id: number;
        name: string;
        color: string;
        members: { id: number; name: string }[];
        milestones: { id: number; name: string }[];
    }[];
    statuses: Status[];
    priorities: { value: string; label: string }[];
    defaults: TaskFormValues;
};

export type TaskFormValues = {
    title?: string;
    project_id: number | null;
    milestone_id?: number | null;
    status_id: number;
    priority: string;
    assignee_ids: number[];
    due_on?: string | null;
    estimate_hours?: string | null;
    description?: string | null;
    blocked_reason?: string | null;
};
