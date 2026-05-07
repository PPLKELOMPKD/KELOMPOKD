<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { useForm, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    course: Object,
});

// Chapter Management
const chapterForm = useForm({ title: '', description: '' });
const editingChapter = ref(null);
const startEditChapter = (chapter) => {
    editingChapter.value = chapter.id;
    chapterForm.title = chapter.title;
    chapterForm.description = chapter.description;
};
const cancelEditChapter = () => {
    editingChapter.value = null;
    chapterForm.reset();
};
const submitChapter = () => {
    if (editingChapter.value) {
        chapterForm.put(route('perusahaan.lms.chapters.update', editingChapter.value), {
            preserveScroll: true,
            onSuccess: () => cancelEditChapter(),
        });
    } else {
        chapterForm.post(route('perusahaan.lms.chapters.store', props.course.slug), {
            preserveScroll: true,
            onSuccess: () => chapterForm.reset(),
        });
    }
};
const deleteChapter = (id) => {
    if(confirm('Hapus bab ini beserta seluruh isinya?')) {
        router.delete(route('perusahaan.lms.chapters.destroy', id), { preserveScroll: true });
    }
};

// Lesson Management
const lessonForms = ref({});
const editingLesson = ref(null);
const getLessonForm = (chapterId) => {
    if (!lessonForms.value[chapterId]) {
        lessonForms.value[chapterId] = useForm({ title: '', type: 'article', content: '', video_url: '' });
    }
    return lessonForms.value[chapterId];
};
const startEditLesson = (chapterId, lesson) => {
    editingLesson.value = lesson.id;
    const form = getLessonForm(chapterId);
    form.title = lesson.title;
    form.type = lesson.type;
    form.content = lesson.content;
    form.video_url = lesson.video_url;
};
const cancelEditLesson = (chapterId) => {
    editingLesson.value = null;
    getLessonForm(chapterId).reset();
};
const submitLesson = (chapterId) => {
    const form = getLessonForm(chapterId);
    if (editingLesson.value) {
        form.put(route('perusahaan.lms.lessons.update', editingLesson.value), {
            preserveScroll: true,
            onSuccess: () => {
                editingLesson.value = null;
                form.reset();
            },
        });
    } else {
        form.post(route('perusahaan.lms.lessons.store', chapterId), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
};
const deleteLesson = (id) => {
    if(confirm('Hapus materi ini?')) {
        router.delete(route('perusahaan.lms.lessons.destroy', id), { preserveScroll: true });
    }
};

// Quiz Management
const quizForms = ref({});
const getQuizForm = (chapterId, quiz) => {
    if (!quizForms.value[chapterId]) {
        quizForms.value[chapterId] = useForm({ 
            title: quiz?.title || '', 
            passing_score: quiz?.passing_score || 70,
            time_limit: quiz?.time_limit || null,
            max_attempts: quiz?.max_attempts || 1
        });
    }
    return quizForms.value[chapterId];
};
const submitQuiz = (chapterId) => {
    const form = getQuizForm(chapterId);
    form.post(route('perusahaan.lms.quizzes.store', chapterId), {
        preserveScroll: true,
    });
};
const deleteQuiz = (id) => {
    if(confirm('Hapus kuis ini?')) {
        router.delete(route('perusahaan.lms.quizzes.destroy', id), { preserveScroll: true });
    }
};

// Question Management
const questionForms = ref({});
const getQuestionForm = (quizId) => {
    if (!questionForms.value[quizId]) {
        questionForms.value[quizId] = useForm({ question: '' });
    }
    return questionForms.value[quizId];
};
const submitQuestion = (quizId) => {
    const form = getQuestionForm(quizId);
    form.post(route('perusahaan.lms.questions.store', quizId), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
const deleteQuestion = (id) => {
    if(confirm('Hapus pertanyaan ini?')) {
        router.delete(route('perusahaan.lms.questions.destroy', id), { preserveScroll: true });
    }
};

// Option Management
const optionForms = ref({});
const getOptionForm = (questionId) => {
    if (!optionForms.value[questionId]) {
        optionForms.value[questionId] = useForm({ option_text: '', is_correct: false });
    }
    return optionForms.value[questionId];
};
const submitOption = (questionId) => {
    const form = getOptionForm(questionId);
    form.post(route('perusahaan.lms.options.store', questionId), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
const deleteOption = (id) => {
    router.delete(route('perusahaan.lms.options.destroy', id), { preserveScroll: true });
};

// Assignment Management
const assignmentForms = ref({});
const getAssignmentForm = (chapterId) => {
    if (!assignmentForms.value[chapterId]) {
        assignmentForms.value[chapterId] = useForm({ title: '', description: '', deadline_at: '', allowed_formats: 'pdf,doc,docx,zip' });
    }
    return assignmentForms.value[chapterId];
};
const submitAssignment = (chapterId) => {
    const form = getAssignmentForm(chapterId);
    form.post(route('perusahaan.lms.assignments.store', chapterId), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
const deleteAssignment = (id) => {
    if(confirm('Hapus tugas ini?')) {
        router.delete(route('perusahaan.lms.assignments.destroy', id), { preserveScroll: true });
    }
};

const editingAssignment = ref(null);
const startEditAssignment = (chapterId, assignment) => {
    editingAssignment.value = assignment.id;
    const form = getAssignmentForm(chapterId);
    form.title = assignment.title;
    form.description = assignment.description;
    form.deadline_at = assignment.deadline_at ? new Date(assignment.deadline_at).toISOString().slice(0, 16) : '';
    form.allowed_formats = assignment.allowed_formats;
};

const cancelEditAssignment = () => {
    editingAssignment.value = null;
};

const updateAssignment = (chapterId) => {
    const form = getAssignmentForm(chapterId);
    form.patch(route('perusahaan.lms.assignments.update', editingAssignment.value), {
        preserveScroll: true,
        onSuccess: () => {
            editingAssignment.value = null;
            form.reset();
        },
    });
};

const activeTab = ref('content');
</script>

<template>
    <SikaraLayout :title="`Builder: ${course.title}`" subtitle="Susun kurikulum pembelajaran dengan menambahkan bab, materi, tugas, dan kuis.">
        <template #headerAction>
            <Link :href="route('perusahaan.lms.index')" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:bg-slate-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                Selesai & Simpan
            </Link>
        </template>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Sidebar: Chapter List & Add Chapter -->
            <div class="lg:w-80 flex-shrink-0 space-y-6">
                <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        {{ editingChapter ? 'Edit Bab' : 'Tambah Bab Baru' }}
                    </h3>
                    <form @submit.prevent="submitChapter" class="space-y-3">
                        <input v-model="chapterForm.title" type="text" placeholder="Judul Bab (cth: Pendahuluan)" class="w-full rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" required />
                        <div class="flex gap-2">
                            <button v-if="editingChapter" type="button" @click="cancelEditChapter" class="flex-1 rounded-xl bg-slate-100 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-200 transition-all">
                                Batal
                            </button>
                            <button type="submit" class="flex-[2] rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-100 hover:bg-blue-700 transition-all" :disabled="chapterForm.processing">
                                {{ editingChapter ? 'Simpan' : 'Buat Bab' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl bg-slate-100/50 p-2 border border-slate-200">
                    <h4 class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Daftar Bab</h4>
                    <div class="space-y-1">
                        <a v-for="(chapter, idx) in course.chapters" :key="chapter.id" :href="`#chapter-${chapter.id}`" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white hover:shadow-sm transition-all text-sm font-semibold text-slate-600 hover:text-blue-600">
                            <span class="h-6 w-6 rounded-lg bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500">{{ idx + 1 }}</span>
                            {{ chapter.title }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content: Chapter Details -->
            <div class="flex-1 space-y-10">
                <div v-for="(chapter, cIdx) in course.chapters" :key="chapter.id" :id="`chapter-${chapter.id}`" class="relative rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
                    <!-- Chapter Header -->
                    <div class="bg-slate-50 border-b border-slate-200 px-8 py-6 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-lg shadow-blue-100">
                                {{ cIdx + 1 }}
                            </div>
                            <div>
                                <h2 class="text-xl font-black text-slate-900">{{ chapter.title }}</h2>
                                <p class="text-xs text-slate-500 font-medium">Struktur materi, tugas, dan kuis untuk bab ini.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="startEditChapter(chapter)" class="p-2 rounded-xl text-slate-300 hover:text-blue-500 hover:bg-blue-50 transition-all">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button @click="deleteChapter(chapter.id)" class="p-2 rounded-xl text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-8 space-y-10">
                        <!-- SECTION: MATERI -->
                        <section>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                    <div class="h-1.5 w-1.5 rounded-full bg-blue-600"></div>
                                    Materi Pembelajaran
                                </h4>
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ chapter.lessons?.length || 0 }} MATERI</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div v-for="lesson in chapter.lessons" :key="lesson.id" class="group flex items-center justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-100 hover:shadow-md transition-all">
                                    <div class="flex items-center gap-3">
                                        <div :class="lesson.type === 'video' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'" class="h-10 w-10 rounded-xl flex items-center justify-center">
                                            <svg v-if="lesson.type === 'video'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 line-clamp-1">{{ lesson.title }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ lesson.type }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                        <button @click="startEditLesson(chapter.id, lesson)" class="p-2 text-slate-300 hover:text-blue-500 transition-all">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button @click="deleteLesson(lesson.id)" class="p-2 text-slate-300 hover:text-red-500 transition-all">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Add/Edit Lesson Form -->
                            <form @submit.prevent="submitLesson(chapter.id)" class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-blue-50/30 p-4 rounded-2xl border border-blue-100 border-dashed" :class="editingLesson ? 'bg-blue-100/50 border-solid border-blue-300' : ''">
                                <div class="md:col-span-12 mb-1 flex justify-between items-center" v-if="editingLesson">
                                    <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">EDIT MATERI #{{ editingLesson }}</span>
                                    <button type="button" @click="cancelEditLesson(chapter.id)" class="text-[10px] font-bold text-slate-400 hover:text-slate-600">BATAL</button>
                                </div>
                                <div class="md:col-span-3">
                                    <select v-model="getLessonForm(chapter.id).type" class="w-full rounded-xl border-slate-200 text-xs font-bold focus:border-blue-500 focus:ring-blue-500">
                                        <option value="article">ARTIKEL / TEKS</option>
                                        <option value="video">VIDEO TUTORIAL</option>
                                    </select>
                                </div>
                                <div class="md:col-span-7">
                                    <input v-model="getLessonForm(chapter.id).title" type="text" placeholder="Judul materi..." class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500" required />
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="w-full h-full rounded-xl bg-blue-600 text-[10px] font-black text-white uppercase tracking-widest hover:bg-blue-700 transition-all" :disabled="getLessonForm(chapter.id).processing">
                                        {{ editingLesson ? 'SIMPAN' : 'TAMBAH' }}
                                    </button>
                                </div>

                                <!-- Content/Video Input Row -->
                                <div class="md:col-span-12 mt-2">
                                    <div v-if="getLessonForm(chapter.id).type === 'video'">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">URL Video (YouTube/Vimeo)</label>
                                        <input v-model="getLessonForm(chapter.id).video_url" type="url" placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500" required />
                                    </div>
                                    <div v-else>
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Isi Materi (Artikel/Teks)</label>
                                        <textarea v-model="getLessonForm(chapter.id).content" rows="4" placeholder="Tuliskan isi materi pembelajaran di sini..." class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500" required></textarea>
                                    </div>
                                </div>
                            </form>
                        </section>

                        <!-- SECTION: TUGAS -->
                        <section>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                    <div class="h-1.5 w-1.5 rounded-full bg-teal-500"></div>
                                    Tugas / Proyek
                                </h4>
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ chapter.assignments?.length || 0 }} TUGAS</span>
                            </div>

                            <div class="space-y-3 mb-6">
                                <div v-for="assignment in chapter.assignments" :key="assignment.id" class="flex items-center justify-between p-4 rounded-2xl border border-teal-50 bg-teal-50/20 group hover:border-teal-200 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ assignment.title }}</p>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span v-if="assignment.deadline_at" class="text-[10px] font-bold text-red-500 flex items-center gap-1 uppercase">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    DEADLINE: {{ new Date(assignment.deadline_at).toLocaleDateString() }}
                                                </span>
                                                <Link :href="route('perusahaan.lms.grading.index', { course: course.slug || course.id, assignment: assignment.id })" class="text-[10px] font-black text-teal-600 hover:underline uppercase tracking-widest">Lihat Pengumpulan</Link>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                        <button @click="startEditAssignment(chapter.id, assignment)" class="p-2 text-slate-300 hover:text-blue-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button @click="deleteAssignment(assignment.id)" class="p-2 text-slate-300 hover:text-red-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <form @submit.prevent="editingAssignment ? updateAssignment(chapter.id) : submitAssignment(chapter.id)" class="space-y-4 bg-slate-50 p-6 rounded-2xl border border-slate-200 border-dashed" :class="editingAssignment ? 'border-blue-300 bg-blue-50/20' : ''">
                                <div class="flex items-center justify-between mb-2" v-if="editingAssignment">
                                    <h5 class="text-[10px] font-black text-blue-600 uppercase tracking-widest">EDIT TUGAS #{{ editingAssignment }}</h5>
                                    <button type="button" @click="cancelEditAssignment" class="text-[10px] font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal Edit</button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input v-model="getAssignmentForm(chapter.id).title" type="text" placeholder="Judul Tugas (cth: Review Jurnal)" class="rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 shadow-sm" required />
                                    <input v-model="getAssignmentForm(chapter.id).deadline_at" type="datetime-local" class="rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 shadow-sm" />
                                </div>
                                <textarea v-model="getAssignmentForm(chapter.id).description" placeholder="Instruksi pengerjaan tugas..." class="w-full rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 shadow-sm h-24" required></textarea>
                                <div class="flex justify-end gap-3">
                                    <button v-if="editingAssignment" type="button" @click="cancelEditAssignment" class="rounded-xl bg-slate-200 px-6 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-300 transition-all">
                                        Batal
                                    </button>
                                    <button type="submit" class="rounded-xl px-6 py-2.5 text-xs font-bold text-white shadow-lg transition-all" :class="editingAssignment ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-100' : 'bg-teal-600 hover:bg-teal-700 shadow-teal-100'" :disabled="getAssignmentForm(chapter.id).processing">
                                        {{ editingAssignment ? 'Simpan Perubahan' : 'Tambah Tugas' }}
                                    </button>
                                </div>
                            </form>
                        </section>

                        <!-- SECTION: KUIS -->
                        <section>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                    <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                                    Kuis & Evaluasi
                                </h4>
                                <span v-if="chapter.quiz" class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">KUIS TERSEDIA</span>
                                <span v-else class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">TIDAK ADA KUIS</span>
                            </div>

                            <div v-if="chapter.quiz" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
                                <div class="p-6 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-black italic text-xl">Q</div>
                                        <div>
                                            <h5 class="font-bold text-slate-900">{{ chapter.quiz.title }}</h5>
                                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                                LULUS: {{ chapter.quiz.passing_score }}% | WAKTU: {{ chapter.quiz.time_limit || '∞' }} MENIT | KESEMPATAN: {{ chapter.quiz.max_attempts }}x
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="deleteQuiz(chapter.quiz.id)" class="p-2 text-slate-300 hover:text-red-500 transition-all">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Questions Area -->
                                <div class="p-6 space-y-6">
                                    <div v-for="(question, qIdx) in chapter.quiz.questions" :key="question.id" class="p-6 rounded-2xl border border-slate-100 bg-slate-50/30">
                                        <div class="flex justify-between items-start mb-4">
                                            <p class="text-sm font-bold text-slate-900">{{ qIdx + 1 }}. {{ question.question }}</p>
                                            <button @click="deleteQuestion(question.id)" class="p-1 text-slate-300 hover:text-red-400"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pl-4">
                                            <div v-for="option in question.options" :key="option.id" class="flex items-center justify-between p-2 rounded-lg" :class="option.is_correct ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : 'bg-white border border-slate-100 text-slate-600'">
                                                <span class="text-xs font-medium">{{ option.option_text }}</span>
                                                <div class="flex items-center gap-2">
                                                    <svg v-if="option.is_correct" class="h-3.5 w-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                    <button @click="deleteOption(option.id)" class="text-slate-300 hover:text-red-400"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Add Option Form -->
                                        <form @submit.prevent="submitOption(question.id)" class="mt-4 flex gap-2">
                                            <input v-model="getOptionForm(question.id).option_text" type="text" placeholder="Tambah opsi jawaban..." class="flex-1 rounded-xl border-slate-200 text-xs focus:border-emerald-500 focus:ring-emerald-500" required />
                                            <label class="flex items-center gap-2 px-3 rounded-xl border border-slate-200 bg-white cursor-pointer">
                                                <input type="checkbox" v-model="getOptionForm(question.id).is_correct" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">BENAR</span>
                                            </label>
                                            <button type="submit" class="rounded-xl bg-slate-800 px-4 text-white hover:bg-slate-900 transition-all">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Add Question Form -->
                                    <form @submit.prevent="submitQuestion(chapter.quiz.id)" class="flex gap-3 bg-slate-100/50 p-4 rounded-2xl border border-slate-200 border-dashed">
                                        <input v-model="getQuestionForm(chapter.quiz.id).question" type="text" placeholder="Tulis pertanyaan baru untuk kuis ini..." class="flex-1 rounded-xl border-slate-200 text-sm focus:border-amber-500 focus:ring-amber-500 shadow-sm" required />
                                        <button type="submit" class="rounded-xl bg-slate-700 px-6 text-xs font-bold text-white hover:bg-slate-800 transition-all" :disabled="getQuestionForm(chapter.quiz.id).processing">
                                            Tambah Pertanyaan
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Create/Update Quiz Form -->
                            <form @submit.prevent="submitQuiz(chapter.id)" class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-amber-50/20 p-6 rounded-3xl border border-amber-200 border-dashed">
                                <div class="md:col-span-12 mb-2">
                                    <h5 class="text-[10px] font-black text-amber-600 uppercase tracking-[2px]">{{ chapter.quiz ? 'PENGATURAN KUIS' : 'BUAT KUIS BARU' }}</h5>
                                </div>
                                <div class="md:col-span-5">
                                    <input v-model="getQuizForm(chapter.id, chapter.quiz).title" type="text" placeholder="Judul Kuis (cth: Evaluasi Bab 1)" class="w-full rounded-xl border-slate-200 text-sm focus:border-amber-500 focus:ring-amber-500 shadow-sm" required />
                                </div>
                                <div class="md:col-span-2">
                                    <input v-model="getQuizForm(chapter.id, chapter.quiz).passing_score" type="number" placeholder="Lulus (%)" title="Nilai Kelulusan (%)" class="w-full rounded-xl border-slate-200 text-sm focus:border-amber-500 focus:ring-amber-500 shadow-sm" required />
                                </div>
                                <div class="md:col-span-2">
                                    <input v-model="getQuizForm(chapter.id, chapter.quiz).time_limit" type="number" placeholder="Waktu (Menit)" title="Batas Waktu (Menit)" class="w-full rounded-xl border-slate-200 text-sm focus:border-amber-500 focus:ring-amber-500 shadow-sm" />
                                </div>
                                <div class="md:col-span-1">
                                    <input v-model="getQuizForm(chapter.id, chapter.quiz).max_attempts" type="number" placeholder="Try" title="Kesempatan Mengerjakan" class="w-full rounded-xl border-slate-200 text-sm focus:border-amber-500 focus:ring-amber-500 shadow-sm" required />
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="w-full h-full rounded-xl bg-amber-500 text-xs font-black text-white uppercase tracking-widest hover:bg-amber-600 shadow-md shadow-amber-100 transition-all" :disabled="getQuizForm(chapter.id, chapter.quiz).processing">
                                        SIMPAN
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- Empty State for Chapters -->
                <div v-if="course.chapters.length === 0" class="flex flex-col items-center justify-center py-32 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="h-24 w-24 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-6">
                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">Belum Ada Struktur Kurikulum</h3>
                    <p class="text-slate-500 mt-2 max-w-sm text-center">Gunakan menu di sebelah kiri untuk menambahkan Bab pertama Anda.</p>
                </div>
            </div>
        </div>
    </SikaraLayout>
</template>

<style scoped>
.scroll-mt-24 {
    scroll-margin-top: 6rem;
}
</style>
