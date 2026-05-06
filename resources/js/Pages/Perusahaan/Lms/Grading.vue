<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    course: Object,
    assignment: Object,
    submissions: Array,
});

const activeSubmission = ref(null);
const gradingForm = useForm({
    score: 0,
    feedback: '',
});

const startGrading = (submission) => {
    activeSubmission.value = submission.id;
    gradingForm.score = submission.score || 0;
    gradingForm.feedback = submission.feedback || '';
};

const submitGrade = (submissionId) => {
    gradingForm.patch(route('perusahaan.lms.grading.update', {
        course: props.course.slug || props.course.id,
        assignment: props.assignment.id,
        submission: submissionId
    }), { 
        preserveScroll: true,
        onSuccess: () => {
            activeSubmission.value = null;
        }
    });
};
</script>

<template>
    <SikaraLayout :title="`Penilaian: ${assignment.title}`" subtitle="Berikan skor dan feedback untuk tugas yang telah dikumpulkan peserta.">
        <template #headerAction>
            <Link :href="route('perusahaan.lms.enrollments.index', course.slug || course.id)" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali
            </Link>
        </template>
        
        <div class="space-y-8">
            <!-- Assignment Info Card -->
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white shadow-xl shadow-blue-100">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-2xl">
                        <h3 class="text-2xl font-black mb-2">{{ assignment.title }}</h3>
                        <p class="text-blue-100 text-sm leading-relaxed">{{ assignment.description }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="px-4 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold">
                            TENGGAT: {{ new Date(assignment.deadline_at).toLocaleString('id-ID') }}
                        </div>
                        <div class="px-4 py-2 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold">
                            TOTAL: {{ submissions.length }} PENGUMPULAN
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submissions Grid/Table -->
            <div class="grid grid-cols-1 gap-6">
                <div v-for="submission in submissions" :key="submission.id" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden transition-all hover:shadow-md" :class="activeSubmission === submission.id ? 'ring-2 ring-blue-500 border-transparent' : ''">
                    <div class="p-6 md:p-8">
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Left: Student Info -->
                            <div class="md:w-64 flex-shrink-0">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200 font-black text-xl uppercase">
                                        {{ submission.student_name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-900 leading-none">{{ submission.student_name }}</p>
                                        <p class="text-[10px] text-slate-500 mt-1 truncate">{{ submission.student_email }}</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dikirim Pada</p>
                                    <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-100 text-[10px] font-bold text-slate-600">
                                        {{ submission.submitted_at }}
                                    </div>
                                    <a :href="submission.file_url" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-blue-50 text-blue-600 text-xs font-black hover:bg-blue-600 hover:text-white transition-all">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        UNDUH JAWABAN
                                    </a>
                                </div>
                            </div>

                            <!-- Right: Grading Form -->
                            <div class="flex-1">
                                <div v-if="activeSubmission === submission.id" class="space-y-6 animate-in fade-in slide-in-from-top-2 duration-300">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                        <div class="md:col-span-3">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Skor (0-100)</label>
                                            <input type="number" min="0" max="100" v-model="gradingForm.score" class="w-full h-14 rounded-2xl border-slate-200 text-2xl font-black focus:border-blue-500 focus:ring-blue-500" required>
                                        </div>
                                        <div class="md:col-span-9">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Feedback / Komentar</label>
                                            <textarea v-model="gradingForm.feedback" rows="3" class="w-full rounded-2xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Berikan catatan konstruktif untuk peserta..."></textarea>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button @click="activeSubmission = null" class="px-6 py-3 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition-all">
                                            Batal
                                        </button>
                                        <button @click="submitGrade(submission.id)" class="px-8 py-3 rounded-xl bg-blue-600 text-xs font-black text-white shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all" :disabled="gradingForm.processing">
                                            {{ gradingForm.processing ? 'MENYIMPAN...' : 'SIMPAN NILAI' }}
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="h-full flex flex-col">
                                    <div class="flex-1 flex flex-col justify-center">
                                        <div class="flex items-center justify-between p-6 rounded-2xl bg-slate-50 border border-slate-100">
                                            <div>
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Penilaian</p>
                                                <h4 v-if="submission.score !== null" class="text-xl font-black text-emerald-600">Terdaftar ({{ submission.score }}/100)</h4>
                                                <h4 v-else class="text-xl font-black text-amber-500">Belum Dinilai</h4>
                                            </div>
                                            <button @click="startGrading(submission)" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-xs font-black text-slate-700 hover:border-blue-500 hover:text-blue-600 transition-all shadow-sm">
                                                {{ submission.score !== null ? 'UBAH NILAI' : 'MULAI MENILAI' }}
                                            </button>
                                        </div>
                                        <div v-if="submission.feedback" class="mt-4 p-4 rounded-xl bg-blue-50/50 border border-blue-100/50 italic text-xs text-slate-600">
                                            "{{ submission.feedback }}"
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!submissions.length" class="flex flex-col items-center justify-center py-32 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Belum Ada Pengumpulan</h3>
                    <p class="text-slate-500 text-sm mt-1">Jawaban peserta akan muncul di sini setelah mereka mengunggah file tugas.</p>
                </div>
            </div>
        </div>
    </SikaraLayout>
</template>
