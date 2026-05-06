<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    course: {
        type: Object,
        required: true,
    },
    activeLesson: {
        type: Object,
        default: null,
    },
});

const selectedQuizAnswers = ref({});
const quizForm = useForm({ answers: {} });

const submitQuiz = (quizId) => {
    quizForm.answers = selectedQuizAnswers.value;
    quizForm.post(route('lms.quizzes.submit', quizId), { preserveScroll: true });
};

const assignmentForm = useForm({ file: null });

const submitAssignment = (assignmentId) => {
    assignmentForm.post(route('lms.assignments.submit', { course: props.course.slug, assignment: assignmentId }), {
        preserveScroll: true,
        onSuccess: () => assignmentForm.reset()
    });
};

const revokeAssignment = (assignmentId, submissionId) => {
    if (confirm('Yakin ingin membatalkan tugas yang sudah dikumpulkan?')) {
        router.delete(route('lms.assignments.revoke', { course: props.course.slug, assignment: assignmentId, submission: submissionId }), { preserveScroll: true });
    }
};

const completeLesson = (lessonId) => {
    router.post(route('lms.lessons.complete', lessonId), {}, { 
        preserveScroll: true,
        onSuccess: () => {
            if (nextLesson.value) {
                goToLesson(nextLesson.value);
            }
        }
    });
};

const lessonKey = (chapterIndex, lessonIndex) => `${chapterIndex}-${lessonIndex}`;

const initialLessonKey = () => {
    for (const [chapterIndex, chapter] of (props.course.chapters ?? []).entries()) {
        const lessonIndex = (chapter.lessons ?? []).findIndex((item) => item.active);

        if (lessonIndex !== -1) {
            return lessonKey(chapterIndex, lessonIndex);
        }
    }

    return '0-0';
};

const selectedLessonKey = ref(initialLessonKey());
const openChapters = ref(new Set(
    (props.course.chapters ?? [])
        .map((chapter, index) => ({ chapter, index }))
        .filter(({ chapter, index }) => chapter.state === 'active' || selectedLessonKey.value.startsWith(`${index}-`))
        .map(({ index }) => index),
));

const lessonEntries = computed(() => (props.course.chapters ?? []).flatMap((chapter, chapterIndex) =>
    (chapter.lessons ?? [])
        .map((item, lessonIndex) => ({
            key: lessonKey(chapterIndex, lessonIndex),
            chapter,
            chapterIndex,
            lessonIndex,
            lesson: item,
        }))
        .filter(({ lesson: item }) => item.state !== 'locked')
));

const selectedEntry = computed(() =>
    lessonEntries.value.find((entry) => entry.key === selectedLessonKey.value)
    ?? lessonEntries.value.find((entry) => entry.lesson.active)
    ?? lessonEntries.value[0]
);

const lesson = computed(() => {
    const selectedLesson = selectedEntry.value?.lesson ?? props.activeLesson;

    return {
        id: selectedLesson?.id,
        type: selectedLesson?.type ?? 'article',
        video_url: selectedLesson?.video_url ?? null,
        state: selectedLesson?.state ?? 'available',
        title: selectedLesson?.title ?? '',
        description_title: selectedLesson?.description_title ?? selectedLesson?.title ?? '',
        description: selectedLesson?.description ?? selectedLesson?.content ?? '',
        video_image_url: selectedLesson?.video_image_url ?? props.course.image_url,
        isQuiz: selectedLesson?.isQuiz ?? false,
        isAssignment: selectedLesson?.isAssignment ?? false,
        assignment: selectedLesson?.assignment ?? null,
        submission: selectedLesson?.submission ?? null,
        latest_score: selectedLesson?.latest_score ?? null,
        passing_score: selectedLesson?.passing_score ?? null,
        time_limit: selectedLesson?.time_limit ?? null,
        max_attempts: selectedLesson?.max_attempts ?? 1,
        attempts_count: selectedLesson?.attempts_count ?? 0,
        questions: selectedLesson?.questions ?? [],
        goals: selectedLesson?.goals ?? [],
    };
});

const getEmbedUrl = (url) => {
    if (!url) return null;
    
    // YouTube
    let match = url.match(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(.+)/);
    if (match) return `https://www.youtube.com/embed/${match[1]}`;
    
    // Vimeo
    match = url.match(/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(.+)/);
    if (match) return `https://player.vimeo.com/video/${match[1]}`;
    
    return url;
};

const selectedChapter = computed(() => selectedEntry.value?.chapter ?? {
    title: 'Bab 3: Model Deployment',
});

const selectedLessonIndex = computed(() =>
    lessonEntries.value.findIndex((entry) => entry.key === selectedEntry.value?.key)
);

const previousLesson = computed(() =>
    selectedLessonIndex.value > 0 ? lessonEntries.value[selectedLessonIndex.value - 1] : null
);

const nextLesson = computed(() =>
    selectedLessonIndex.value !== -1 && selectedLessonIndex.value < lessonEntries.value.length - 1
        ? lessonEntries.value[selectedLessonIndex.value + 1]
        : null
);

const fallbackLesson = computed(() => props.activeLesson ?? {
    title: 'Video: Publik vs Privat',
    description_title: 'Memahami Perbedaan Public dan Private Cloud',
    description: 'Dalam modul ini, kita akan membahas perbedaan mendasar antara model deployment Public Cloud dan Private Cloud.',
    video_image_url: props.course.image_url,
    goals: [],
});

if (!selectedEntry.value && fallbackLesson.value) {
    selectedLessonKey.value = 'fallback';
}

const chapterIconClass = (state) => {
    if (state === 'completed') return 'text-[#4edea3]';
    if (state === 'active') return 'text-[#2563eb]';
    return 'text-[#737686]';
};

const isChapterOpen = (chapterIndex) => openChapters.value.has(chapterIndex);

const toggleChapter = (chapter, chapterIndex) => {
    if (chapter.state === 'locked' || !(chapter.lessons ?? []).length) return;

    const nextOpenChapters = new Set(openChapters.value);

    if (nextOpenChapters.has(chapterIndex)) {
        nextOpenChapters.delete(chapterIndex);
    } else {
        nextOpenChapters.add(chapterIndex);
    }

    openChapters.value = nextOpenChapters;
};

const isLessonSelected = (chapterIndex, lessonIndex) =>
    selectedLessonKey.value === lessonKey(chapterIndex, lessonIndex);

const selectLesson = (chapter, item, chapterIndex, lessonIndex) => {
    if (item.state === 'locked') return;

    selectedLessonKey.value = lessonKey(chapterIndex, lessonIndex);
    openChapters.value = new Set([...openChapters.value, chapterIndex]);
};

const timeLeft = ref(null);
let timerInterval = null;

const startTimer = (minutes) => {
    if (!minutes) return;
    timeLeft.value = minutes * 60;
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval);
            submitQuiz(lesson.value.id);
        }
    }, 1000);
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

// Start timer when a quiz lesson with time limit is selected
const isTimerStarted = ref(false);
import { watch } from 'vue';
watch(() => lesson.value.id, (newId) => {
    if (timerInterval) clearInterval(timerInterval);
    isTimerStarted.value = false;
    timeLeft.value = null;
    
    if (lesson.value.isQuiz && lesson.value.time_limit && lesson.value.attempts_count < lesson.value.max_attempts) {
        // We might want a "Start Quiz" button before starting timer, but user asked for "waktu mengerjakan quiz muncul"
        startTimer(lesson.value.time_limit);
        isTimerStarted.value = true;
    }
}, { immediate: true });

const goToLesson = (entry) => {
    if (!entry) return;

    selectLesson(entry.chapter, entry.lesson, entry.chapterIndex, entry.lessonIndex);
};
</script>

<template>
    <Head :title="`SIKARA - ${course.title}`">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    </Head>

    <div class="flex h-screen flex-col overflow-hidden bg-[#f8f9ff] font-sans text-base leading-relaxed text-[#0b1c30]">
        <div class="flex flex-1 overflow-hidden">
            <aside class="relative z-40 hidden w-80 flex-shrink-0 flex-col border-r border-[#c3c6d7] bg-[#f8f9ff] lg:flex">
                <div class="border-b border-[#c3c6d7] bg-white p-6">
                    <Link :href="route('lms')" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-[#2563eb] hover:text-[#004ac6]">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Kembali ke LMS
                    </Link>
                    <div class="mb-2 flex items-center gap-4">
                        <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg border border-[#c3c6d7] bg-[#d3e4fe]">
                            <img :src="course.image_url" alt="Course Thumbnail" class="h-full w-full object-cover" />
                        </div>
                        <div>
                            <h1 class="line-clamp-1 text-base font-semibold text-[#0b1c30]">{{ course.title }}</h1>
                            <p class="mt-1 text-sm text-[#737686]">{{ course.progress }}% Completed</p>
                        </div>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#dce9ff]">
                        <div class="h-2 rounded-full bg-gradient-to-r from-[#2563eb] to-[#4edea3]" :style="{ width: `${course.progress}%` }"></div>
                    </div>
                </div>

                <nav class="flex-1 overflow-y-auto py-4">
                    <div v-for="(chapter, chapterIndex) in course.chapters" :key="chapter.title" class="mb-2">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-6 py-2 text-left transition-colors duration-200"
                            :class="selectedChapter.title === chapter.title ? 'bg-[#eff4ff]' : chapter.state === 'locked' ? 'cursor-not-allowed opacity-50' : 'hover:bg-[#eff4ff]'"
                            @click="toggleChapter(chapter, chapterIndex)"
                        >
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined" :class="chapterIconClass(chapter.state)" :style="chapter.state === 'completed' ? `font-variation-settings: 'FILL' 1;` : ''">
                                    {{ chapter.state === 'locked' ? 'lock' : chapter.state === 'active' ? 'radio_button_unchecked' : 'check_circle' }}
                                </span>
                                <span class="text-base font-semibold" :class="selectedChapter.title === chapter.title ? 'text-[#2563eb]' : chapter.state === 'locked' ? 'text-[#737686]' : 'text-[#0b1c30]'">{{ chapter.title }}</span>
                            </div>
                            <span class="material-symbols-outlined" :class="selectedChapter.title === chapter.title ? 'text-[#2563eb]' : 'text-[#737686]'">{{ isChapterOpen(chapterIndex) ? 'expand_less' : 'expand_more' }}</span>
                        </button>

                        <div
                            v-if="chapter.lessons?.length && isChapterOpen(chapterIndex)"
                            class="ml-16 mt-2 flex flex-col border-l pl-2"
                            :class="selectedChapter.title === chapter.title ? 'border-l-2 border-[#2563eb]' : 'border-[#c3c6d7]'"
                        >
                            <template v-for="(item, lessonIndex) in chapter.lessons" :key="item.title">
                                <a
                                    v-if="item.state !== 'locked'"
                                    href="#"
                                    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition-colors duration-200"
                                    :class="isLessonSelected(chapterIndex, lessonIndex) ? 'bg-[#e5eeff] font-semibold text-[#2563eb]' : 'text-[#737686] hover:bg-[#eff4ff] hover:text-[#004ac6]'"
                                    @click.prevent="selectLesson(chapter, item, chapterIndex, lessonIndex)"
                                >
                                    <span class="material-symbols-outlined text-sm" :style="item.state === 'completed' || isLessonSelected(chapterIndex, lessonIndex) ? `font-variation-settings: 'FILL' 1;` : ''">{{ item.type }}</span>
                                    <span>{{ item.title }}</span>
                                </a>
                                <div v-else class="flex cursor-not-allowed items-center gap-2 px-2 py-2 text-sm text-[#c3c6d7]">
                                    <span class="material-symbols-outlined text-sm">lock</span>
                                    <span>{{ item.title }}</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </nav>

                <div class="border-t border-[#c3c6d7] bg-white p-6 space-y-3">
                    <a v-if="course.is_graduated" :href="route('lms.certificate.download', course.slug)" target="_blank" class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#4edea3] px-4 py-3 text-base font-semibold text-white transition-colors duration-200 hover:bg-[#3eb382]">
                        <span class="material-symbols-outlined">workspace_premium</span>
                        Unduh Sertifikat
                    </a>
                    <div v-else-if="course.progress === 100" class="flex w-full flex-col items-center justify-center gap-2 rounded-lg bg-amber-50 border border-amber-200 p-4 text-center">
                        <span class="material-symbols-outlined text-amber-500">hourglass_empty</span>
                        <p class="text-xs font-semibold text-amber-700 uppercase">Menunggu Kelulusan</p>
                        <p class="text-[10px] text-amber-600">Pelatihan selesai. Sertifikat akan tersedia setelah dikonfirmasi perusahaan.</p>
                    </div>
                    <button v-else class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#2563eb] px-4 py-3 text-base font-semibold text-white transition-colors duration-200 hover:bg-[#004ac6]">
                        Lanjutkan Belajar
                    </button>

                    <Link as="button" method="delete" :href="route('lms.enrollments.destroy', course.slug)" class="flex w-full items-center justify-center gap-2 rounded-lg border border-red-500 px-4 py-3 text-base font-semibold text-red-500 transition-colors duration-200 hover:bg-red-50 hover:text-red-600">
                        Batalkan Pendaftaran
                    </Link>
                </div>
            </aside>

            <main class="relative flex flex-1 flex-col overflow-y-auto bg-[#f8f9ff] p-4 md:p-10">
                <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6">
                    <nav aria-label="Breadcrumb" class="flex text-sm text-[#737686]">
                        <ol class="inline-flex flex-wrap items-center gap-1 md:gap-3">
                            <li class="inline-flex items-center">
                                <Link :href="route('lms.module.show', course.slug)" class="inline-flex items-center transition-colors hover:text-[#2563eb]">
                                    {{ course.title }}
                                </Link>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined mx-1 text-sm">chevron_right</span>
                                    <span>{{ selectedChapter.title }}</span>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined mx-1 text-sm">chevron_right</span>
                                    <span class="font-medium text-[#0b1c30]">{{ lesson.title }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <div class="overflow-hidden rounded-xl border border-[#c3c6d7] bg-white shadow-sm">
                        <!-- Video Player -->
                        <div v-if="lesson.type === 'video' && lesson.video_url" class="aspect-video bg-black">
                            <iframe 
                                :src="getEmbedUrl(lesson.video_url)" 
                                class="h-full w-full" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                            ></iframe>
                        </div>
                        <!-- Article/Default Placeholder -->
                        <div v-else class="group relative flex aspect-video cursor-pointer items-center justify-center bg-[#213145]">
                            <img :src="lesson.video_image_url || course.image_url" alt="Video Player Placeholder" class="absolute inset-0 h-full w-full object-cover opacity-60" />
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#2563eb]/90 text-white shadow-lg transition-transform duration-300 group-hover:scale-110">
                                    <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                                </div>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 flex items-center gap-4 bg-gradient-to-t from-black/80 to-transparent p-4 text-white opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                <button type="button" aria-label="Play"><span class="material-symbols-outlined">play_arrow</span></button>
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-white/30">
                                    <div class="h-full w-1/3 bg-[#2563eb]"></div>
                                </div>
                                <span class="text-xs">03:45 / 12:30</span>
                                <button type="button" aria-label="Volume"><span class="material-symbols-outlined">volume_up</span></button>
                                <button type="button" aria-label="Fullscreen"><span class="material-symbols-outlined">fullscreen</span></button>
                            </div>
                        </div>
                    </div>

                    <section class="flex min-h-[400px] flex-1 flex-col rounded-xl border border-[#c3c6d7] bg-white shadow-sm">
                        <div class="flex border-b border-[#c3c6d7] px-6 pt-4">
                            <button class="mr-4 border-b-2 border-[#2563eb] px-6 py-2 text-base font-semibold text-[#2563eb]">Deskripsi</button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 md:p-10">
                            <h2 class="mb-4 text-2xl font-semibold leading-snug text-[#0b1c30]">{{ lesson.description_title }}</h2>
                            <div class="max-w-none text-base leading-relaxed text-[#434655]">
                                <p class="mb-4">{{ lesson.description }}</p>

                                <template v-if="lesson.isQuiz">
                                    <div v-if="lesson.latest_score !== undefined && lesson.latest_score !== null" class="mb-6 p-4 rounded-xl border border-blue-200 bg-blue-50 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-blue-800">Skor Terakhir Anda</p>
                                            <p class="text-xs text-blue-600">Nilai Kelulusan: {{ lesson.passing_score }} | Kesempatan: {{ lesson.attempts_count }} / {{ lesson.max_attempts }}</p>
                                        </div>
                                        <div class="text-3xl font-black" :class="lesson.latest_score >= lesson.passing_score ? 'text-green-600' : 'text-amber-500'">{{ lesson.latest_score }}</div>
                                    </div>
                                    <div v-else class="mb-6 p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">Kesempatan Mengerjakan</p>
                                            <p class="text-xs text-slate-600">Anda memiliki {{ lesson.max_attempts }} kali kesempatan untuk lulus kuis ini.</p>
                                        </div>
                                        <div class="text-xl font-bold text-slate-500">{{ lesson.attempts_count }} / {{ lesson.max_attempts }}</div>
                                    </div>

                                    <!-- Quiz Timer Display -->
                                    <div v-if="timeLeft !== null" class="sticky top-0 z-10 mb-6 flex items-center justify-center gap-3 bg-red-600 p-3 rounded-xl text-white shadow-lg animate-pulse">
                                        <span class="material-symbols-outlined">timer</span>
                                        <span class="text-xl font-black tracking-widest">SISA WAKTU: {{ formatTime(timeLeft) }}</span>
                                    </div>

                                    <form @submit.prevent="submitQuiz(lesson.id)" class="mt-6 space-y-8">
                                        <div v-for="(question, qIndex) in lesson.questions" :key="question.id" class="p-6 rounded-xl border border-slate-200 bg-slate-50">
                                            <p class="font-semibold text-slate-900 mb-4">{{ qIndex + 1 }}. {{ question.question }}</p>
                                            <div class="space-y-3 pl-4">
                                                <label v-for="option in question.options" :key="option.id" class="flex items-center gap-3 cursor-pointer">
                                                    <input type="radio" :name="`question_${question.id}`" :value="option.id" v-model="selectedQuizAnswers[question.id]" class="w-4 h-4 text-[#2563eb] border-slate-300 focus:ring-[#2563eb]" required>
                                                    <span class="text-slate-700">{{ option.option_text }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-slate-200 flex flex-col gap-3">
                                            <div v-if="lesson.attempts_count >= lesson.max_attempts" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 font-semibold">
                                                Anda sudah menghabiskan seluruh kesempatan pengerjaan kuis ini.
                                            </div>
                                            <button 
                                                v-else
                                                type="submit" 
                                                class="rounded bg-[#2563eb] px-6 py-3 text-white font-semibold hover:bg-[#004ac6] w-full sm:w-auto disabled:opacity-50" 
                                                :disabled="quizForm.processing"
                                            >
                                                Kirim Jawaban Kuis (Percobaan ke-{{ lesson.attempts_count + 1 }})
                                            </button>
                                        </div>
                                    </form>
                                </template>
                                <template v-else-if="lesson.isAssignment">
                                    <div class="mt-6 flex flex-col p-6 rounded-xl border border-slate-200 bg-slate-50 space-y-4">
                                        <div v-if="lesson.assignment.file_url" class="mb-2">
                                            <a :href="lesson.assignment.file_url" target="_blank" class="text-sm font-semibold text-[#2563eb] hover:underline flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[18px]">download</span> Unduh Lampiran Tugas
                                            </a>
                                        </div>
                                        <div v-if="lesson.assignment.deadline_at" class="text-sm font-semibold text-red-600 block mb-4">
                                            Batas Waktu: {{ new Date(lesson.assignment.deadline_at).toLocaleString() }}
                                        </div>
                                        
                                        <div v-if="lesson.submission" class="bg-white p-4 rounded-lg border border-slate-200 space-y-3 shadow-sm">
                                            <div class="flex justify-between items-center border-b pb-2">
                                                <span class="font-semibold text-green-600">Tugas Telah Dikumpulkan</span>
                                                <span class="text-xs text-slate-500 font-mono">{{ new Date(lesson.submission.submitted_at).toLocaleString() }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a :href="lesson.submission.file_url" target="_blank" class="text-sm font-medium text-blue-600 hover:underline">Lihat Dokumen Terkumpul</a>
                                            </div>

                                            <div v-if="lesson.submission.score !== null">
                                                <div class="mt-4 pt-4 border-t border-slate-100 flex items-start gap-4">
                                                    <div class="bg-blue-50 border border-blue-200 rounded p-2 text-center min-w-[70px]">
                                                        <span class="block text-xs font-bold text-slate-500 uppercase">Nilai</span>
                                                        <span class="text-xl font-bold text-blue-700">{{ lesson.submission.score }}</span>
                                                    </div>
                                                    <div class="flex-1 bg-slate-50 border border-slate-200 rounded p-3 text-sm italic text-slate-700">
                                                        "{{ lesson.submission.feedback || 'Tidak ada catatan/feedback dari pengajar.' }}"
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="text-xs text-amber-600 mt-2">
                                                Menunggu Penilaian...
                                            </div>
                                            
                                            <div class="mt-4 pt-4 border-t flex justify-end" v-if="!lesson.assignment.deadline_at || new Date() <= new Date(lesson.assignment.deadline_at)">
                                                <form @submit.prevent="revokeAssignment(lesson.assignment.id, lesson.submission.id)">
                                                    <button type="submit" class="text-red-500 text-sm font-semibold hover:underline bg-red-50 px-3 py-1.5 rounded border border-red-200" :disabled="assignmentForm.processing">Tarik / Batalkan Tugas</button>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <form v-else @submit.prevent="submitAssignment(lesson.assignment.id)" class="bg-white p-4 rounded-lg border border-slate-200 space-y-4 shadow-sm">
                                            <label class="block font-semibold">Upload File Jawaban Tugas</label>
                                            <input type="file" @change="e => assignmentForm.file = e.target.files[0]" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-[#2563eb] hover:file:bg-blue-100" required>
                                            <p class="text-xs text-slate-500">Maks. 10MB (PDF, DOC/DOCX, ZIP, MP4).</p>
                                            <button type="submit" class="mt-2 rounded bg-[#2563eb] px-6 py-2 text-white font-semibold hover:bg-[#004ac6] w-full sm:w-auto" :disabled="assignmentForm.processing">Kumpulkan Tugas</button>
                                        </form>
                                    </div>
                                </template>
                                <template v-else>
                                    <h3 class="mb-2 mt-6 text-base font-semibold text-[#0b1c30]" v-if="lesson.goals?.length">Tujuan Pembelajaran:</h3>
                                    <ul class="mb-4 list-disc space-y-2 pl-5" v-if="lesson.goals?.length">
                                        <li v-for="goal in lesson.goals" :key="goal">{{ goal }}</li>
                                    </ul>

                                    <div class="mt-6 flex items-start gap-4 rounded-lg border border-[#c3c6d7] bg-[#eff4ff] p-6">
                                        <span class="material-symbols-outlined mt-1 text-[#2563eb]">info</span>
                                        <div>
                                            <p class="m-0 text-sm font-semibold text-[#0b1c30]">Catatan Penting</p>
                                            <p class="m-0 mt-1 text-sm leading-relaxed text-[#737686]">Pastikan Anda menyelesaikan kuis di akhir bab ini untuk membuka materi selanjutnya. Kuis akan menguji pemahaman Anda tentang materi ini.</p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <div v-if="!lesson.isQuiz && !lesson.isAssignment" class="mt-8 border-t pt-6">
                                <button v-if="lesson.state !== 'completed'" @click="completeLesson(lesson.id)" class="rounded bg-[#2563eb] px-4 py-2 text-white text-sm hover:bg-[#004ac6]">
                                    Selesaikan Materi
                                </button>
                                <span v-else class="text-green-600 font-semibold flex items-center gap-2">
                                    <span class="material-symbols-outlined">check_circle</span> Materi Selesai
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between rounded-b-xl border-t border-[#c3c6d7] bg-[#eff4ff] p-6">
                            <button
                                class="flex items-center gap-2 rounded-lg border px-6 py-2 text-base font-semibold transition-colors"
                                :class="previousLesson ? 'border-[#737686] text-[#0b1c30] hover:bg-[#d3e4fe]' : 'cursor-not-allowed border-[#c3c6d7] text-[#737686]'"
                                :disabled="!previousLesson"
                                @click="goToLesson(previousLesson)"
                            >
                                <span class="material-symbols-outlined">arrow_back</span>
                                Sebelumnya
                            </button>
                            <button
                                class="flex items-center gap-2 rounded-lg px-6 py-2 text-base font-semibold transition-colors"
                                :class="nextLesson ? 'bg-[#2563eb] text-white hover:bg-[#004ac6]' : 'cursor-not-allowed bg-[#d3e4fe] text-[#737686]'"
                                :disabled="!nextLesson"
                                @click="goToLesson(nextLesson)"
                            >
                                Selanjutnya
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </button>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>
