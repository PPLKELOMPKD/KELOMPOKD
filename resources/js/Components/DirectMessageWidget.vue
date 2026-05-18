<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const user = computed(() => page.props.auth?.user ?? null);
const isOpen = ref(false);
const isExpanded = ref(false);
const isSearching = ref(false);
const isLoading = ref(false);
const isSending = ref(false);
const conversations = ref([]);
const messages = ref([]);
const selectedConversation = ref(null);
const unreadCount = ref(page.props.dm?.unreadCount ?? 0);
const conversationSearch = ref('');
const search = ref('');
const searchResults = ref([]);
const messageBody = ref('');
const messageList = ref(null);
let searchTimer = null;
const subscribedConversationIds = new Set();

const initials = (name) => (name || '?')
    .split(' ')
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');

const panelClass = computed(() => [
    'fixed bottom-4 right-4 z-50 flex max-h-[calc(100vh-2rem)] flex-col overflow-hidden rounded-lg border border-[#C3C6D7] bg-white shadow-2xl shadow-slate-900/20 sm:bottom-6 sm:right-6',
    isExpanded.value
        ? 'h-[calc(100vh-2rem)] w-[calc(100vw-2rem)] sm:h-[90vh] sm:w-[480px]'
        : 'h-[min(600px,calc(100vh-2rem))] w-[calc(100vw-2rem)] sm:w-[360px]',
]);

const totalUnreadFromConversations = () => conversations.value.reduce(
    (total, conversation) => total + (conversation.unread_count || 0),
    0,
);

const filteredConversations = computed(() => {
    const query = conversationSearch.value.trim().toLowerCase();

    if (!query) return conversations.value;

    return conversations.value.filter((conversation) => {
        const recipient = conversation.recipient?.name?.toLowerCase() ?? '';
        const body = conversation.last_message?.body?.toLowerCase() ?? '';

        return recipient.includes(query) || body.includes(query);
    });
});

const scrollToBottom = async () => {
    await nextTick();
    if (messageList.value) {
        messageList.value.scrollTop = messageList.value.scrollHeight;
    }
};

const appendMessage = async (message) => {
    if (!message || messages.value.some((item) => item.id === message.id)) {
        return;
    }

    messages.value.push(message);
    await scrollToBottom();
};

const upsertConversation = (conversation) => {
    const index = conversations.value.findIndex((item) => item.id === conversation.id);

    if (index === -1) {
        conversations.value.unshift(conversation);
    } else {
        conversations.value[index] = conversation;
        conversations.value.unshift(...conversations.value.splice(index, 1));
    }

    if (selectedConversation.value?.id === conversation.id) {
        selectedConversation.value = conversation;
    }

    subscribeToConversation(conversation.id);
};

const subscribeToConversation = (conversationId) => {
    if (!user.value || !window.Echo || !conversationId || subscribedConversationIds.has(conversationId)) {
        return;
    }

    subscribedConversationIds.add(conversationId);
    window.Echo.private(`conversations.${conversationId}`)
        .listen('.direct-message.sent', handleIncomingMessage);
};

const subscribeToKnownConversations = () => {
    conversations.value.forEach((conversation) => subscribeToConversation(conversation.id));
};

const loadConversations = async () => {
    if (!user.value) return;

    isLoading.value = true;

    try {
        const response = await axios.get(route('dm.conversations.index'));
        conversations.value = response.data.conversations ?? [];
        unreadCount.value = response.data.unread_count ?? totalUnreadFromConversations();
        subscribeToKnownConversations();
    } finally {
        isLoading.value = false;
    }
};

const openWidget = async () => {
    isOpen.value = true;
    await loadConversations();
};

const closeWidget = () => {
    isOpen.value = false;
    selectedConversation.value = null;
    messages.value = [];
    isSearching.value = false;
    conversationSearch.value = '';
    search.value = '';
};

const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value;
};

const markRead = async (conversation) => {
    const response = await axios.post(route('dm.conversations.read', conversation.id));
    upsertConversation(response.data.conversation);
    unreadCount.value = response.data.unread_count ?? totalUnreadFromConversations();
};

const openConversation = async (conversation) => {
    selectedConversation.value = conversation;
    isSearching.value = false;
    const response = await axios.get(route('dm.conversations.messages', conversation.id));
    messages.value = response.data.messages ?? [];
    upsertConversation(response.data.conversation);
    subscribeToConversation(conversation.id);
    await markRead(response.data.conversation);
    await scrollToBottom();
};

const backToList = () => {
    selectedConversation.value = null;
    messages.value = [];
    messageBody.value = '';
};

const searchUsers = async () => {
    const query = search.value.trim();

    if (query.length < 2) {
        searchResults.value = [];
        return;
    }

    const response = await axios.get(route('dm.users.index'), {
        params: { search: query },
    });

    searchResults.value = response.data.users ?? [];
};

watch(search, () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(searchUsers, 250);
});

const startConversation = async (recipient) => {
    const response = await axios.post(route('dm.conversations.store'), {
        recipient_id: recipient.id,
    });

    upsertConversation(response.data.conversation);
    unreadCount.value = response.data.unread_count ?? totalUnreadFromConversations();
    search.value = '';
    searchResults.value = [];
    await openConversation(response.data.conversation);
};

const sendMessage = async () => {
    const body = messageBody.value.trim();

    if (!body || !selectedConversation.value || isSending.value) return;

    isSending.value = true;

    try {
        const response = await axios.post(route('dm.conversations.send', selectedConversation.value.id), {
            body,
        });

        await appendMessage(response.data.message);
        upsertConversation(response.data.conversation);
        messageBody.value = '';
    } finally {
        isSending.value = false;
    }
};

const handleIncomingMessage = async (payload) => {
    const message = payload.message;

    if (!message) return;

    if (selectedConversation.value?.id === message.conversation_id) {
        await appendMessage(message);

        await markRead(selectedConversation.value);
    } else {
        await loadConversations();
    }
};

onMounted(() => {
    if (user.value && window.Echo) {
        window.Echo.private(`users.${user.value.id}`)
            .listen('.direct-message.sent', handleIncomingMessage);
    }
});

onBeforeUnmount(() => {
    window.clearTimeout(searchTimer);

    if (user.value && window.Echo) {
        window.Echo.leave(`users.${user.value.id}`);
        subscribedConversationIds.forEach((conversationId) => {
            window.Echo.leave(`conversations.${conversationId}`);
        });
        subscribedConversationIds.clear();
    }
});
</script>

<template>
    <div v-if="user">
        <button
            v-if="!isOpen"
            type="button"
            class="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#004AC6] text-white shadow-xl shadow-blue-900/25 transition hover:bg-[#2563EB] focus:outline-none focus:ring-4 focus:ring-blue-200 sm:bottom-6 sm:right-6"
            aria-label="Buka pesan"
            @click="openWidget"
        >
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
            </svg>
            <span
                v-if="unreadCount"
                class="absolute -right-1 -top-1 flex h-6 min-w-6 items-center justify-center rounded-full bg-red-600 px-1.5 text-xs font-bold text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <section v-else :class="panelClass" aria-label="Pesan SIKARA">
            <header class="flex items-center justify-between bg-[#004AC6] px-4 py-3 text-white">
                <div>
                    <h2 class="text-base font-bold leading-none">Pesan SIKARA</h2>
                    <p v-if="selectedConversation" class="mt-1 text-xs text-blue-100">{{ selectedConversation.recipient?.name }}</p>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        v-if="!selectedConversation"
                        type="button"
                        class="rounded p-1.5 transition hover:bg-white/15"
                        title="Pesan Baru"
                        @click="isSearching = !isSearching"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="rounded p-1.5 transition hover:bg-white/15"
                        :title="isExpanded ? 'Perkecil' : 'Perbesar'"
                        @click="toggleExpanded"
                    >
                        <svg v-if="!isExpanded" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h6v6" />
                            <path d="m21 3-7 7" />
                            <path d="M9 21H3v-6" />
                            <path d="m3 21 7-7" />
                        </svg>
                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 14h6v6" />
                            <path d="m10 14-7 7" />
                            <path d="M20 10h-6V4" />
                            <path d="m14 10 7-7" />
                        </svg>
                    </button>
                    <button type="button" class="rounded p-1.5 transition hover:bg-white/15" title="Tutup" @click="closeWidget">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
            </header>

            <template v-if="!selectedConversation">
                <div class="border-b border-[#C3C6D7] bg-white p-4">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#434655]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                        <input
                            v-model="conversationSearch"
                            type="text"
                            class="w-full rounded-lg border border-[#C3C6D7] bg-white py-2 pl-10 pr-3 text-sm text-[#0B1C30] focus:border-[#004AC6] focus:outline-none focus:ring-1 focus:ring-[#B4C5FF]"
                            placeholder="Cari percakapan..."
                        >
                    </div>
                </div>

                <div v-if="isSearching" class="max-h-64 overflow-y-auto border-b border-[#C3C6D7] bg-[#F8F9FF]">
                    <div class="border-b border-[#E5EEFF] bg-white p-3">
                        <input
                            v-model="search"
                            type="text"
                            class="w-full rounded-lg border border-[#C3C6D7] bg-white px-3 py-2 text-sm text-[#0B1C30] focus:border-[#004AC6] focus:outline-none focus:ring-1 focus:ring-[#B4C5FF]"
                            placeholder="Cari penerima baru..."
                        >
                    </div>
                    <button
                        v-for="result in searchResults"
                        :key="result.id"
                        type="button"
                        class="flex w-full items-center gap-3 border-b border-[#E5EEFF] px-4 py-3 text-left transition hover:bg-[#E5EEFF]"
                        @click="startConversation(result)"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#DBE1FF] text-sm font-bold text-[#003EA8]">
                            {{ initials(result.name) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-[#0B1C30]">{{ result.name }}</span>
                            <span class="block truncate text-xs capitalize text-[#434655]">{{ result.role }}</span>
                        </span>
                    </button>
                    <p v-if="search.trim().length >= 2 && !searchResults.length" class="px-4 py-5 text-center text-sm text-[#434655]">
                        User tidak ditemukan.
                    </p>
                    <p v-if="search.trim().length < 2" class="px-4 py-5 text-center text-sm text-[#434655]">
                        Ketik minimal 2 karakter untuk mencari penerima.
                    </p>
                </div>

                <div class="flex-1 overflow-y-auto bg-white">
                    <p v-if="isLoading" class="px-4 py-6 text-center text-sm text-[#434655]">Memuat percakapan...</p>
                    <p v-else-if="!conversations.length" class="px-4 py-8 text-center text-sm text-[#434655]">Belum ada percakapan.</p>
                    <p v-else-if="!filteredConversations.length" class="px-4 py-8 text-center text-sm text-[#434655]">Percakapan tidak ditemukan.</p>

                    <button
                        v-for="conversation in filteredConversations"
                        :key="conversation.id"
                        type="button"
                        class="relative flex w-full items-start gap-3 border-b border-[#C3C6D7] px-4 py-4 text-left transition hover:bg-[#E5EEFF]"
                        @click="openConversation(conversation)"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[#C3C6D7] bg-[#D3E4FE] text-sm font-bold text-[#003EA8]">
                            {{ initials(conversation.recipient?.name) }}
                        </span>
                        <span class="min-w-0 flex-1 pr-7">
                            <span class="flex items-baseline justify-between gap-2">
                                <span class="truncate text-sm font-bold text-[#0B1C30]">{{ conversation.recipient?.name }}</span>
                                <span class="shrink-0 text-[10px] text-[#004AC6]">{{ conversation.last_message_at_human || '' }}</span>
                            </span>
                            <span
                                class="mt-1 block truncate text-[13px] leading-tight"
                                :class="conversation.unread_count ? 'font-bold text-[#0B1C30]' : 'text-[#434655]'"
                            >
                                {{ conversation.last_message?.body || 'Mulai percakapan baru.' }}
                            </span>
                        </span>
                        <span
                            v-if="conversation.unread_count"
                            class="absolute right-4 top-1/2 flex h-5 min-w-5 -translate-y-1/2 items-center justify-center rounded-full bg-[#004AC6] px-1.5 text-[10px] font-bold text-white"
                        >
                            {{ conversation.unread_count }}
                        </span>
                    </button>
                </div>
            </template>

            <template v-else>
                <div class="flex items-center gap-3 border-b border-[#C3C6D7] bg-white px-4 py-3">
                    <button type="button" class="rounded p-1.5 text-[#0B1C30] transition hover:bg-[#E5EEFF]" title="Kembali" @click="backToList">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                    </button>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#DBE1FF] text-sm font-bold text-[#003EA8]">
                        {{ initials(selectedConversation.recipient?.name) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-[#0B1C30]">{{ selectedConversation.recipient?.name }}</p>
                        <p class="truncate text-xs capitalize text-[#434655]">{{ selectedConversation.recipient?.role }}</p>
                    </div>
                </div>

                <div ref="messageList" class="flex-1 space-y-3 overflow-y-auto bg-[#F8F9FF] px-4 py-4">
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="flex"
                        :class="message.sender_id === user.id ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[82%] rounded-lg px-3 py-2 text-sm leading-relaxed shadow-sm"
                            :class="message.sender_id === user.id ? 'bg-[#004AC6] text-white' : 'border border-[#C3C6D7] bg-white text-[#0B1C30]'"
                        >
                            <p class="whitespace-pre-wrap break-words">{{ message.body }}</p>
                            <p class="mt-1 text-[10px]" :class="message.sender_id === user.id ? 'text-blue-100' : 'text-[#737686]'">
                                {{ message.created_at_human }}
                            </p>
                        </div>
                    </div>
                    <p v-if="!messages.length" class="py-8 text-center text-sm text-[#434655]">Belum ada pesan.</p>
                </div>

                <form class="border-t border-[#C3C6D7] bg-white p-3" @submit.prevent="sendMessage">
                    <div class="flex items-end gap-2">
                        <textarea
                            v-model="messageBody"
                            rows="1"
                            class="max-h-28 min-h-11 flex-1 resize-none rounded-lg border border-[#C3C6D7] px-3 py-2 text-sm text-[#0B1C30] focus:border-[#004AC6] focus:outline-none focus:ring-1 focus:ring-[#B4C5FF]"
                            placeholder="Tulis pesan..."
                            @keydown.enter.exact.prevent="sendMessage"
                        />
                        <button
                            type="submit"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-[#004AC6] text-white transition hover:bg-[#2563EB] disabled:cursor-not-allowed disabled:bg-[#C3C6D7]"
                            :disabled="!messageBody.trim() || isSending"
                            title="Kirim"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m22 2-7 20-4-9-9-4z" />
                                <path d="M22 2 11 13" />
                            </svg>
                        </button>
                    </div>
                </form>
            </template>
        </section>
    </div>
</template>
