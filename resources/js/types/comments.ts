export interface Comment {
    id: number;
    body: string;
    created_at: string;
    author: { id: number; name: string } | null;
}

export interface MentionableUser {
    id: number;
    name: string;
}
