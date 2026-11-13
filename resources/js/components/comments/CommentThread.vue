<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import type { Comment, MentionableUser } from '@/types/comments';

const MAX_LENGTH = 5000;

const props = withDefaults(
    defineProps<{
        comments: Comment[];
        mentionable: MentionableUser[];
        submitUrl: string;
        canDelete?: (comment: Comment) => boolean;
    }>(),
    { canDelete: () => false },
);

const form = useForm({ body: '', mentioned_user_ids: [] as number[] });
const textarea = ref<HTMLTextAreaElement | null>(null);
const suggestionsOpen = ref(false);
const highlightedSuggestion = ref(0);
const mentionStart = ref<number | null>(null);
const mentionQuery = ref('');

const suggestions = computed(() => {
    const query = mentionQuery.value.toLocaleLowerCase();

    return props.mentionable.filter((person) =>
        person.name.toLocaleLowerCase().includes(query),
    );
});

function updateMentionState(): void {
    const cursor = textarea.value?.selectionStart ?? form.body.length;
    const beforeCursor = form.body.slice(0, cursor);
    const match = beforeCursor.match(/(^|\s)@([^\s@]*)$/u);

    if (!match) {
        suggestionsOpen.value = false;
        mentionStart.value = null;
        return;
    }

    mentionStart.value = cursor - match[2].length - 1;
    mentionQuery.value = match[2];
    highlightedSuggestion.value = 0;
    suggestionsOpen.value = suggestions.value.length > 0;
}

function chooseMention(person: MentionableUser): void {
    if (mentionStart.value === null || textarea.value === null) {
        return;
    }

    const cursor = textarea.value.selectionStart;
    const start = mentionStart.value;
    form.body = `${form.body.slice(0, start)}@${person.name} ${form.body.slice(cursor)}`;
    form.mentioned_user_ids = [...new Set([...form.mentioned_user_ids, person.id])];
    suggestionsOpen.value = false;
    mentionStart.value = null;

    nextTick(() => {
        const position = start + person.name.length + 2;
        textarea.value?.focus();
        textarea.value?.setSelectionRange(position, position);
    });
}

function handleKeydown(event: KeyboardEvent): void {
    if (!suggestionsOpen.value) {
        return;
    }

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        highlightedSuggestion.value = (highlightedSuggestion.value + 1) % suggestions.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        highlightedSuggestion.value = (highlightedSuggestion.value - 1 + suggestions.value.length) % suggestions.value.length;
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const person = suggestions.value[highlightedSuggestion.value];
        if (person) {
            chooseMention(person);
        }
    } else if (event.key === 'Escape') {
        event.preventDefault();
        suggestionsOpen.value = false;
    }
}

function submit(): void {
    const mentionedUserIds = form.mentioned_user_ids.filter((id) =>
        props.mentionable.find((person) => person.id === id && form.body.includes(`@${person.name}`)),
    );

    form.transform(() => ({ body: form.body, mentioned_user_ids: mentionedUserIds })).post(props.submitUrl, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function relativeDate(value: string): string {
    const seconds = Math.max(0, Math.floor((Date.now() - new Date(value).getTime()) / 1000));
    const units: [number, string][] = [[31536000, 'an'], [2592000, 'mois'], [86400, 'jour'], [3600, 'heure'], [60, 'minute']];
    const unit = units.find(([length]) => seconds >= length);

    if (!unit) {
        return 'à l’instant';
    }

    const count = Math.floor(seconds / unit[0]);
    return `il y a ${count} ${unit[1]}${unit[1] === 'mois' || count === 1 ? '' : 's'}`;
}

watch(() => form.body, () => {
    form.body = form.body.slice(0, MAX_LENGTH);
    updateMentionState();
});
</script>

<template>
    <section class="grid gap-4" aria-labelledby="comments-title">
        <h2 id="comments-title" class="text-lg font-semibold">Commentaires</h2>

        <p v-if="comments.length === 0" class="text-sm text-muted-foreground">Aucun commentaire</p>
        <ol v-else class="grid gap-4" aria-live="polite">
            <li v-for="comment in comments" :key="comment.id" class="grid gap-1">
                <div class="flex items-baseline justify-between gap-3">
                    <span class="font-medium">{{ comment.author?.name ?? 'Utilisateur supprimé' }}</span>
                    <time class="text-xs text-muted-foreground" :datetime="comment.created_at">{{ relativeDate(comment.created_at) }}</time>
                </div>
                <p class="whitespace-pre-wrap text-sm">{{ comment.body }}</p>
            </li>
        </ol>

        <form class="relative grid gap-2" @submit.prevent="submit">
            <label for="comment-body" class="sr-only">Votre commentaire</label>
            <textarea
                id="comment-body"
                ref="textarea"
                v-model="form.body"
                maxlength="5000"
                rows="3"
                class="control"
                placeholder="Écrire un commentaire…"
                autocomplete="off"
                @input="updateMentionState"
                @keydown="handleKeydown"
            />
            <ul v-if="suggestionsOpen" class="absolute inset-x-0 bottom-full z-10 mb-1 max-h-48 overflow-auto rounded-md border bg-background p-1 shadow-md" role="listbox" aria-label="Personnes à mentionner">
                <li v-for="(person, index) in suggestions" :key="person.id">
                    <button type="button" class="w-full rounded px-2 py-1 text-left text-sm hover:bg-muted" :class="{ 'bg-muted': index === highlightedSuggestion }" role="option" :aria-selected="index === highlightedSuggestion" @mousedown.prevent="chooseMention(person)">
                        {{ person.name }}
                    </button>
                </li>
            </ul>
            <InputError :message="form.errors.body" />
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs text-muted-foreground">{{ form.body.length }}/{{ MAX_LENGTH }}</span>
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm text-primary-foreground disabled:cursor-not-allowed disabled:opacity-50" :disabled="form.processing || form.body.trim() === ''">
                    {{ form.processing ? 'Envoi…' : 'Commenter' }}
                </button>
            </div>
        </form>
    </section>
</template>
