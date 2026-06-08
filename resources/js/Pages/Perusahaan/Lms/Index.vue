<script setup>
import SikaraLayout from "@/Layouts/SikaraLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    courses: Array,
});

const publish = (course) => {
    if(confirm('Publikasikan modul ini sekarang?')) {
        router.post(route('perusahaan.lms.publish', course.slug), {}, { preserveScroll: true });
    }
};

const unpublish = (course) => {
    if(confirm('Sembunyikan modul ini dari peserta?')) {
        router.post(route('perusahaan.lms.unpublish', course.slug), {}, { preserveScroll: true });
    }
};

const deleteCourse = (course) => {
    if(confirm('Hapus seluruh modul ini? Semua data materi dan kuis akan hilang.')) {
        router.delete(route('perusahaan.lms.destroy', course.slug));
    }
};

// ─── Moderation Modal Reason ───
const showRejectionModal = ref(false);
const selectedCourse = ref(null);

const openRejectionModal = (course) => {
    selectedCourse.value = course;
    showRejectionModal.value = true;
};

const closeRejectionModal = () => {
    showRejectionModal.value = false;
    selectedCourse.value = null;
};
</script>

<template>
    <SikaraLayout title="Manajemen LMS" subtitle="Kelola kurikulum, materi, dan evaluasi untuk peserta pelatihan.">
        <template #headerAction>
            <Link :href="route('perusahaan.lms.create')" class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-[#1D4ED8] hover:translate-y-[-1px]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                Buat Modul Baru
            </Link>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="course in courses" :key="course.id" class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm transition-all hover:shadow-xl hover:border-blue-200">
                <!-- Course Status Badge -->
                <div class="absolute top-4 right-4 z-10">
                    <!-- Pending -->
                    <span v-if="course.moderation_status === 'pending'" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-amber-50 text-amber-700 border-amber-200 shadow-sm">
                        Menunggu Review
                    </span>
                    <!-- Rejected -->
                    <span v-else-if="course.moderation_status === 'rejected'" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-red-50 text-red-700 border-red-200 shadow-sm">
                        Ditolak
                    </span>
                    <!-- Takedown -->
                    <span v-else-if="course.moderation_status === 'takedown'" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-rose-50 text-rose-700 border-rose-200 shadow-sm">
                        Takedown Admin
                    </span>
                    <!-- Approved & Published -->
                    <span v-else-if="course.status === 'published'" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-emerald-50 text-emerald-700 border-emerald-200 shadow-sm">
                        Aktif
                    </span>
                    <!-- Approved & Draft -->
                    <span v-else class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-slate-100 text-slate-600 border-slate-200 shadow-sm">
                        Draft
                    </span>
                </div>

                <!-- Course Header/Image placeholder -->
                <div class="h-32 bg-gradient-to-br from-slate-100 to-slate-200 p-6 flex items-end">
                    <div class="h-12 w-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                </div>

                <!-- Course Info -->
                <div class="p-6 flex-1">
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-1">{{ course.title }}</h3>
                    <div class="mt-4 flex items-center gap-4 text-sm text-slate-500">
                        <div class="flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                            <span class="font-semibold">{{ course.chapters_count || 0 }}</span> Bab
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <span class="font-semibold">{{ course.enrollments_count || 0 }}</span> Peserta
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 grid grid-cols-2 gap-2">
                    <!-- ❶ TAKEDOWN -->
                    <template v-if="course.moderation_status === 'takedown'">
                        <button @click="openRejectionModal(course)" class="flex items-center justify-center gap-2 py-2 rounded-lg bg-orange-50 border border-orange-200 text-xs font-bold text-orange-700 hover:bg-orange-100 transition-all">
                            <svg class="h-3.5 w-3.5 text-orange-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Alasan Takedown
                        </button>
                        <Link :href="route('perusahaan.lms.enrollments.index', course.slug)" class="flex items-center justify-center gap-2 py-2 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:border-teal-200 hover:text-teal-600 transition-all">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Kelola Peserta
                        </Link>
                        <div class="col-span-2 text-center text-[10px] text-slate-400 font-medium py-1">
                            Modul ini dinonaktifkan oleh Admin
                        </div>
                    </template>

                    <!-- ❷ OTHER MODERATION STATUSES (Approved, Pending, Rejected) -->
                    <template v-else>
                        <Link :href="route('perusahaan.lms.builder', course.slug)" class="flex items-center justify-center gap-2 py-2 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-all">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Materi & Kuis
                        </Link>
                        <Link :href="route('perusahaan.lms.enrollments.index', course.slug)" class="flex items-center justify-center gap-2 py-2 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:border-teal-200 hover:text-teal-600 transition-all">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Kelola Peserta
                        </Link>
                        <div class="col-span-2 flex items-center gap-2 mt-1">
                            <!-- If rejected, show "Lihat Alasan Ditolak" button instead of publish -->
                            <button v-if="course.moderation_status === 'rejected'" @click="openRejectionModal(course)" class="flex-1 py-2 rounded-lg bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 transition-all">Alasan Ditolak</button>
                            
                            <!-- If pending, show status info -->
                            <div v-else-if="course.moderation_status === 'pending'" class="flex-1 py-2 text-center rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-[10px] font-bold uppercase tracking-wider">Menunggu Review Admin</div>
                            
                            <!-- If approved, show Publish/Unpublish -->
                            <template v-else>
                                <button v-if="course.status === 'draft'" @click="publish(course)" class="flex-1 py-2 rounded-lg bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-700 transition-all">Publish</button>
                                <button v-else @click="unpublish(course)" class="flex-1 py-2 rounded-lg bg-amber-500 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-amber-600 transition-all">Unpublish</button>
                            </template>
                            
                            <Link :href="route('perusahaan.lms.edit', course.slug)" class="p-2 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 transition-all" title="Edit Pengaturan">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </Link>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="courses.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-6">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Belum Ada Modul LMS</h3>
                <p class="text-slate-500 mt-2 max-w-sm text-center">Mulai buat kurikulum pembelajaran untuk peserta pelatihan Anda sekarang.</p>
                <Link :href="route('perusahaan.lms.create')" class="mt-8 rounded-xl bg-blue-600 px-6 py-2.5 text-white font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">
                    Buat Modul Sekarang
                </Link>
            </div>
        </div>

        <!-- ═══ Modal Alasan Penolakan / Takedown ════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showRejectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeRejectionModal">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <!-- Modal Card -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showRejectionModal" class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="selectedCourse?.moderation_status === 'takedown' ? 'bg-orange-100' : 'bg-red-100'">
                                        <svg class="h-5 w-5" :class="selectedCourse?.moderation_status === 'takedown' ? 'text-orange-600' : 'text-red-600'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900">
                                            <template v-if="selectedCourse?.moderation_status === 'takedown'">Alasan Pencabutan (Takedown)</template>
                                            <template v-else>Alasan Penolakan Modul</template>
                                        </h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[260px]">{{ selectedCourse?.title }}</p>
                                    </div>
                                </div>
                                <button @click="closeRejectionModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-5">
                                <div class="mb-4 flex items-start gap-3 rounded-xl p-3.5 border" :class="selectedCourse?.moderation_status === 'takedown' ? 'bg-orange-50 border-orange-200' : 'bg-red-50 border-red-200'">
                                    <svg class="h-4 w-4 mt-0.5 shrink-0" :class="selectedCourse?.moderation_status === 'takedown' ? 'text-orange-500' : 'text-red-500'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    <p class="text-xs text-slate-700 leading-relaxed">
                                        <span v-if="selectedCourse?.moderation_status === 'takedown'">
                                            Modul pelatihan ini telah <strong>diturunkan (takedown)</strong> oleh Admin. Modul ini tidak lagi tampil di sisi mahasiswa dan saat ini dinonaktifkan dari perubahan kurikulum.
                                        </span>
                                        <span v-else>
                                            Modul pelatihan yang Anda ajukan telah <strong>ditolak</strong> oleh Admin. Silakan perbarui materi atau kelengkapan data sesuai catatan di bawah ini untuk dapat mempublikasikan kembali.
                                        </span>
                                    </p>
                                </div>

                                <!-- Reason -->
                                <div v-if="selectedCourse?.rejection_reason" class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-4">
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan dari Admin</p>
                                    <p class="text-sm text-slate-800 leading-relaxed whitespace-pre-wrap">{{ selectedCourse?.rejection_reason }}</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button @click="closeRejectionModal" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition shadow-sm">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </SikaraLayout>
</template>
