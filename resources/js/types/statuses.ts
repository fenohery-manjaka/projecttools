/**
 * A configurable status (project, task…), as sent by Status::toSummary().
 */
export type Status = {
    id: number;
    name: string;
    tone: string;
    category: string;
};
