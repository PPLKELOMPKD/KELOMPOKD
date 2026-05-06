<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { Link, useForm, router } from "@inertiajs/vue3";

const props = defineProps({
    course: Object,
    enrollments: Array,
});

const form = useForm({});

const toggleGraduation = (enrollmentId) => {
    form.patch(route('perusahaan.lms.enrollments.graduate', { course: props.course.slug || props.course.id, enrollment: enrollmentId }), {
        preserveScroll: true,
        onError: (errors) => {
            alert('Gagal memproses kelulusan: ' + Object.values(errors).join(', '));
        }
    });
};

const resetProgress = (enrollmentId) => {
    if (confirm('Izinkan peserta mengulang pelatihan? Seluruh progress pengerjaan kuis, tugas, dan materi akan DIHAPUS agar peserta bisa mengulang dari awal.')) {
        router.post(route('perusahaan.lms.enrollments.reset', { course: props.course.slug || props.course.id, enrollment: enrollmentId }), {}, {
            preserveScroll: true,
        });
    }
};

const unenroll = (enrollmentId) => {
    if (confirm('Yakin ingin mengeluarkan peserta ini dari pelatihan? Semua progress pengerjaan kuis dan tugas akan dihapus.')) {
        form.delete(route('perusahaan.lms.enrollments.destroy', { course: props.course.slug || props.course.id, enrollment: enrollmentId }), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <SikaraLayout :title="`Peserta: ${course.title}`" subtitle="Pantau kemajuan belajar, nilai kuis/tugas, dan kelola kelulusan peserta.">
        <template #headerAction>
            <Link :href="route('perusahaan.lms.index')" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali
            </Link>
        </template>
        
        <div class="space-y-6">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Peserta</p>
                    <h3 class="text-2xl font-black text-slate-900">{{ enrollments.length }}</h3>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">KKM Modul</p>
                    <h3 class="text-2xl font-black text-blue-600">{{ course.passing_grade }}</h3>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lulus</p>
                    <h3 class="text-2xl font-black text-emerald-600">{{ enrollments.filter(e => e.is_graduated).length }}</h3>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Rata-rata Progress</p>
                    <h3 class="text-2xl font-black text-blue-600">
                        {{ enrollments.length ? Math.round(enrollments.reduce((acc, e) => acc + e.progress, 0) / enrollments.length) : 0 }}%
                    </h3>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Menunggu Kelulusan</p>
                    <h3 class="text-2xl font-black text-amber-500">{{ enrollments.filter(e => e.progress === 100 && !e.is_graduated).length }}</h3>
                </div>
            </div>

            <!-- Participants Table -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Peserta</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Aktivitas & Nilai</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Progress</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Nilai Akhir</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="enrollment in enrollments" :key="enrollment.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 border border-slate-200 font-bold uppercase">
                                            {{ enrollment.student_name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ enrollment.student_name }}</p>
                                            <p class="text-xs text-slate-500">{{ enrollment.student_email }}</p>
                                            <p class="text-[10px] text-slate-400 mt-1 font-medium">Joined: {{ enrollment.enrolled_at }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="space-y-3">
                                        <!-- Quizzes -->
                                        <div v-if="Object.keys(enrollment.quiz_results).length" class="flex flex-wrap gap-1.5">
                                            <span v-for="(score, quizId) in enrollment.quiz_results" :key="quizId" class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 text-[10px] font-black">
                                                QUIZ: {{ score }}
                                            </span>
                                        </div>
                                        <!-- Submissions -->
                                        <div v-if="enrollment.submissions.length" class="flex flex-col gap-1.5">
                                            <Link 
                                                v-for="sub in enrollment.submissions" 
                                                :key="sub.id" 
                                                :href="route('perusahaan.lms.grading.index', { course: course.slug || course.id, assignment: sub.assignment_id })" 
                                                class="group/sub flex items-center justify-between gap-4 p-2.5 rounded-xl bg-blue-50 border border-blue-100 hover:border-blue-300 hover:bg-blue-100/50 transition-all shadow-sm"
                                            >
                                                <div class="flex flex-col">
                                                    <span class="text-[10px] font-bold text-slate-700 truncate max-w-[140px]">{{ sub.assignment_title }}</span>
                                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">{{ sub.submitted_at }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-black" :class="sub.score !== null ? 'text-emerald-600' : 'text-amber-500'">
                                                        {{ sub.score !== null ? sub.score : 'PENILAIAN' }}
                                                    </span>
                                                    <svg v-if="sub.score === null" class="h-3 w-3 text-amber-500 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </div>
                                            </Link>
                                        </div>
                                        <div v-if="!Object.keys(enrollment.quiz_results).length && !enrollment.submissions.length" class="text-[10px] font-bold text-slate-300 italic">Belum ada aktivitas</div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 bg-slate-100 rounded-full h-1.5 max-w-[100px]">
                                            <div :class="enrollment.progress === 100 ? 'bg-emerald-500' : 'bg-blue-600'" class="h-1.5 rounded-full shadow-sm" :style="{ width: enrollment.progress + '%' }"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-600">{{ enrollment.progress }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="inline-flex flex-col items-center justify-center h-12 w-12 rounded-2xl border-2" :class="enrollment.final_grade >= 70 ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-400'">
                                        <span class="text-xs font-black">{{ enrollment.final_grade }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <span v-if="enrollment.is_graduated" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest border border-emerald-200">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        Lulus
                                    </span>
                                    <span v-else :class="enrollment.progress === 100 ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-400 border-slate-200'" class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border">
                                        {{ enrollment.progress === 100 ? 'Menunggu' : 'Aktif' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right space-x-2">
                                    <button 
                                        v-if="!enrollment.is_graduated && enrollment.progress === 100 && enrollment.final_grade < course.passing_grade"
                                        @click="resetProgress(enrollment.id)"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-amber-500 text-white hover:bg-amber-600 shadow-sm transition-all"
                                    >
                                        IZINKAN ULANG
                                    </button>
                                    <button 
                                        @click="toggleGraduation(enrollment.id)" 
                                        :disabled="form.processing"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm"
                                        :class="enrollment.is_graduated ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-emerald-600 text-white hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-100'"
                                    >
                                        <template v-if="form.processing">Processing...</template>
                                        <template v-else>
                                            {{ enrollment.is_graduated ? 'Batal Lulus' : 'Luluskan' }}
                                        </template>
                                    </button>
                                    <button @click="unenroll(enrollment.id)" class="p-2 text-slate-300 hover:text-red-500 transition-colors" title="Keluarkan Peserta">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!enrollments.length">
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-4">
                                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-900">Belum Ada Peserta</h3>
                                        <p class="text-sm text-slate-500 mt-1">Siswa akan muncul di sini setelah mereka mendaftar ke modul ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SikaraLayout>
</template>
