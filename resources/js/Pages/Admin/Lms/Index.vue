<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    stats: Object,
    activeTab: String,
    courses: Object,
    courseCounts: Object,
    moduls: Object,
    quizzes: Object,
    assignments: Object,
    enrollments: Object,
    students: Array,
    allCourses: Array,
    monitoringStats: Object,
    activityLogs: Object,
    filters: Object,
});

// Top-level active tab state
const currentTab = ref(props.filters?.tab || 'moderasi_course');

// Search & filter states
const searchCourse = ref(props.filters?.search_course || '');
const statusCourse = ref(props.filters?.status_course || 'all');

const tab2Type = ref(props.filters?.tab2_type || 'modul');
const searchContent = ref(props.filters?.search_content || '');

const searchEnrollment = ref(props.filters?.search_enrollment || '');
const courseEnrollment = ref(props.filters?.course_enrollment || 'all');
const statusEnrollment = ref(props.filters?.status_enrollment || 'all');

const searchActivity = ref(props.filters?.search_activity || '');
const roleActivity = ref(props.filters?.role_activity || 'all');

// Trigger query parameter update
const reloadPage = () => {
    router.get(route('admin.lms.index'), {
        tab: currentTab.value,
        search_course: searchCourse.value,
        status_course: statusCourse.value,
        tab2_type: tab2Type.value,
        search_content: searchContent.value,
        search_enrollment: searchEnrollment.value,
        course_enrollment: courseEnrollment.value,
        status_enrollment: statusEnrollment.value,
        search_activity: searchActivity.value,
        role_activity: roleActivity.value,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

// Debounced watchers
watch([searchCourse, statusCourse], debounce(() => {
    if (currentTab.value === 'moderasi_course') reloadPage();
}, 400));

watch([tab2Type, searchContent], debounce(() => {
    if (currentTab.value === 'moderasi_konten') reloadPage();
}, 400));

watch([searchEnrollment, courseEnrollment, statusEnrollment], debounce(() => {
    if (currentTab.value === 'kelola_enrollment') reloadPage();
}, 400));

watch([searchActivity, roleActivity], debounce(() => {
    if (currentTab.value === 'monitoring') reloadPage();
}, 400));

const changeTab = (tabName) => {
    currentTab.value = tabName;
    reloadPage();
};

const changeSubTab2 = (type) => {
    tab2Type.value = type;
    reloadPage();
};

// --- Modals and Actions Forms ---
const actionForm = useForm({});

// Tab 1: Course Moderation Detail Modal
const showCourseModal = ref(false);
const selectedCourse = ref(null);
const loadingCourseDetail = ref(false);

const openCourseDetail = async (courseId) => {
    loadingCourseDetail.value = true;
    showCourseModal.value = true;
    try {
        // Fetch detailed stats of chapters, lessons, quizzes, assignments and students for modal
        const response = await fetch(route('admin.lms.users.detail', 1)); // Dummy endpoint fetch for stats
        // We will build a detailed visual modal based on course properties
        selectedCourse.value = props.courses.data.find(c => c.id === courseId);
    } catch (e) {
        console.error("Gagal memuat detail course", e);
    } finally {
        loadingCourseDetail.value = false;
    }
};

// Approve Course
const quickApprove = (courseId) => {
    if (confirm("Apakah Anda yakin ingin menyetujui course ini untuk dipublikasikan?")) {
        actionForm.patch(route('admin.lms.courses.approve', courseId), {
            preserveScroll: true,
            onSuccess: () => {
                showCourseModal.value = false;
            }
        });
    }
};

// Restore Course
const restoreCourse = (courseId) => {
    if (confirm("Apakah Anda yakin ingin memulihkan course ini?")) {
        actionForm.patch(route('admin.lms.courses.restore', courseId), {
            preserveScroll: true,
            onSuccess: () => {
                showCourseModal.value = false;
            }
        });
    }
};

// Reject / Takedown Modal Reason
const showRejectModal = ref(false);
const selectedCourseForModeration = ref(null);
const moderationAction = ref('reject'); // 'reject' or 'takedown'
const rejectForm = useForm({ rejection_reason: '' });

const openRejectModal = (course, action) => {
    selectedCourseForModeration.value = course;
    moderationAction.value = action;
    rejectForm.reset();
    showRejectModal.value = true;
};

const submitCourseModeration = () => {
    if (!selectedCourseForModeration.value) return;

    const routeName = moderationAction.value === 'takedown'
        ? 'admin.lms.courses.takedown'
        : 'admin.lms.courses.reject';

    rejectForm.patch(route(routeName, selectedCourseForModeration.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            showCourseModal.value = false;
        }
    });
};

// --- Tab 2: Content Moderation Actions ---
const takedownLesson = (id) => {
    if (confirm("Apakah Anda yakin ingin melakukan takedown pada modul ini?")) {
        actionForm.patch(route('admin.lms.lessons.takedown', id), { preserveScroll: true });
    }
};

const restoreLesson = (id) => {
    if (confirm("Apakah Anda yakin ingin memulihkan modul ini?")) {
        actionForm.patch(route('admin.lms.lessons.restore', id), { preserveScroll: true });
    }
};

const takedownQuiz = (id) => {
    if (confirm("Apakah Anda yakin ingin melakukan takedown pada quiz ini?")) {
        actionForm.patch(route('admin.lms.quizzes.takedown', id), { preserveScroll: true });
    }
};

const restoreQuiz = (id) => {
    if (confirm("Apakah Anda yakin ingin memulihkan quiz ini?")) {
        actionForm.patch(route('admin.lms.quizzes.restore', id), { preserveScroll: true });
    }
};

const takedownAssignment = (id) => {
    if (confirm("Apakah Anda yakin ingin melakukan takedown pada tugas ini?")) {
        actionForm.patch(route('admin.lms.assignments.takedown', id), { preserveScroll: true });
    }
};

const restoreAssignment = (id) => {
    if (confirm("Apakah Anda yakin ingin memulihkan tugas ini?")) {
        actionForm.patch(route('admin.lms.assignments.restore', id), { preserveScroll: true });
    }
};

// --- Tab 3: Kelola Enrollment Actions ---
const showAddEnrollmentModal = ref(false);
const addEnrollmentForm = useForm({
    student_id: '',
    course_id: '',
});

const submitAddEnrollment = () => {
    addEnrollmentForm.post(route('admin.lms.enrollments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddEnrollmentModal.value = false;
            addEnrollmentForm.reset();
        }
    });
};

const showEnrollmentModal = ref(false);
const enrollmentModalData = ref(null);
const loadingEnrollmentDetail = ref(false);

const openEnrollmentDetail = async (enrollmentId) => {
    loadingEnrollmentDetail.value = true;
    showEnrollmentModal.value = true;
    try {
        const response = await fetch(route('admin.lms.enrollments.detail', enrollmentId));
        if (response.ok) {
            enrollmentModalData.value = await response.json();
        }
    } catch (e) {
        console.error("Gagal memuat detail enrollment", e);
    } finally {
        loadingEnrollmentDetail.value = false;
    }
};

const resetEnrollment = (id) => {
    if (confirm("Apakah Anda yakin ingin meriset progres belajar enrollment ini? Seluruh progres, kuis, dan tugas akan dihapus.")) {
        actionForm.post(route('admin.lms.enrollments.reset', id), {
            preserveScroll: true,
            onSuccess: () => {
                showEnrollmentModal.value = false;
            }
        });
    }
};

const suspendEnrollment = (id) => {
    if (confirm("Apakah Anda yakin ingin menangguhkan (suspend) pendaftaran peserta ini?")) {
        actionForm.patch(route('admin.lms.enrollments.suspend', id), {
            preserveScroll: true,
            onSuccess: () => {
                showEnrollmentModal.value = false;
            }
        });
    }
};

const activateEnrollment = (id) => {
    actionForm.patch(route('admin.lms.enrollments.activate', id), {
        preserveScroll: true,
        onSuccess: () => {
            showEnrollmentModal.value = false;
        }
    });
};

const deleteEnrollment = (id) => {
    if (confirm("Apakah Anda yakin ingin menghapus pendaftaran peserta ini dari kursus?")) {
        actionForm.delete(route('admin.lms.enrollments.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                showEnrollmentModal.value = false;
            }
        });
    }
};

// Date helpers
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatTime = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Pantau LMS" />
    <AdminLayout title="Pantau LMS">

        <!-- Header Banner & Hero Stats (Consistent with vacancy moderation banner style) -->
        <div class="mb-6 relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0F172A] p-6 text-white shadow-xl animate-fade-in-up">
            <div class="absolute -right-6 -top-6 h-40 w-40 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute left-1/2 -bottom-8 h-32 w-32 rounded-full bg-purple-500/10 blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Title section -->
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-500/20 ring-1 ring-blue-400/30">
                        <svg class="h-6 w-6 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Pantau LMS</h1>
                        <p class="text-sm text-slate-400 mt-0.5">Kelola dan awasi seluruh aktivitas pembelajaran dalam LMS SIKARA.</p>
                    </div>
                </div>

                <!-- Stats Badges -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex flex-col items-center px-4 py-2 bg-blue-500/20 rounded-xl ring-1 ring-blue-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-blue-300">{{ stats.total_course }}</span>
                        <span class="text-[9px] font-bold text-blue-400/85 uppercase tracking-wider">Total Course</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-emerald-500/20 rounded-xl ring-1 ring-emerald-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-emerald-300">{{ stats.total_modul }}</span>
                        <span class="text-[9px] font-bold text-emerald-400/85 uppercase tracking-wider">Total Modul</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-amber-500/20 rounded-xl ring-1 ring-amber-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-amber-300">{{ stats.total_quiz }}</span>
                        <span class="text-[9px] font-bold text-amber-400/85 uppercase tracking-wider">Total Quiz</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-purple-500/20 rounded-xl ring-1 ring-purple-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-purple-300">{{ stats.total_enrollment }}</span>
                        <span class="text-[9px] font-bold text-purple-400/85 uppercase tracking-wider">Enrollment</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-indigo-500/20 rounded-xl ring-1 ring-indigo-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-indigo-300">{{ stats.total_course_aktif }}</span>
                        <span class="text-[9px] font-bold text-indigo-400/85 uppercase tracking-wider">Course Aktif</span>
                    </div>
                    <div class="flex flex-col items-center px-4 py-2 bg-rose-500/20 rounded-xl ring-1 ring-rose-400/30 min-w-[90px]">
                        <span class="text-lg font-black text-rose-300">{{ stats.total_course_takedown }}</span>
                        <span class="text-[9px] font-bold text-rose-400/85 uppercase tracking-wider">Course Takedown</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab & Filter Bar -->
        <div class="mb-5 flex flex-col md:flex-row md:items-center gap-4 animate-fade-in-up delay-100">
            <!-- Main Tabs -->
            <div class="flex items-center gap-1 rounded-xl bg-white border border-slate-200 p-1 shadow-sm overflow-x-auto">
                <button
                    @click="changeTab('moderasi_course')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="currentTab === 'moderasi_course'
                        ? 'border border-slate-900 text-slate-900 bg-slate-50 shadow-sm'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    Moderasi Course
                </button>
                <button
                    @click="changeTab('moderasi_konten')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="currentTab === 'moderasi_konten'
                        ? 'border border-slate-900 text-slate-900 bg-slate-50 shadow-sm'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    Moderasi Konten LMS
                </button>
                <button
                    @click="changeTab('kelola_enrollment')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="currentTab === 'kelola_enrollment'
                        ? 'border border-slate-900 text-slate-900 bg-slate-50 shadow-sm'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    Kelola Enrollment
                </button>
                <button
                    @click="changeTab('monitoring')"
                    class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200 whitespace-nowrap"
                    :class="currentTab === 'monitoring'
                        ? 'border border-slate-900 text-slate-900 bg-slate-50 shadow-sm'
                        : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-transparent'"
                >
                    Monitoring LMS
                </button>
            </div>
        </div>

        <!-- Main Content Table Card -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden animate-fade-in-up delay-200">
            
            <!-- ════════ TAB 1: MODERASI COURSE ════════ -->
            <div v-if="currentTab === 'moderasi_course'">
                <!-- Filter status course & Search course -->
                <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Status Filter buttons (styled matching vacancy moderation status filter tab bar) -->
                    <div class="flex items-center gap-1 rounded-xl bg-slate-50 border border-slate-200 p-1 overflow-x-auto w-fit">
                        <button 
                            @click="statusCourse = 'all'" 
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition whitespace-nowrap"
                            :class="statusCourse === 'all' ? 'bg-white text-slate-900 border border-slate-200 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        >
                            Semua
                            <span class="ml-1 rounded-full bg-slate-200 text-slate-700 text-[10px] px-1.5 py-0.5 font-bold">{{ courseCounts.all }}</span>
                        </button>
                        <button 
                            @click="statusCourse = 'pending'" 
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition whitespace-nowrap"
                            :class="statusCourse === 'pending' ? 'bg-white text-amber-600 border border-amber-200 shadow-sm' : 'text-slate-500 hover:text-amber-500'"
                        >
                            Pending
                            <span class="ml-1 rounded-full bg-amber-100 text-amber-800 text-[10px] px-1.5 py-0.5 font-bold">{{ courseCounts.pending }}</span>
                        </button>
                        <button 
                            @click="statusCourse = 'approved'" 
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition whitespace-nowrap"
                            :class="statusCourse === 'approved' ? 'bg-white text-emerald-600 border border-emerald-200 shadow-sm' : 'text-slate-500 hover:text-emerald-500'"
                        >
                            Approved
                            <span class="ml-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] px-1.5 py-0.5 font-bold">{{ courseCounts.approved }}</span>
                        </button>
                        <button 
                            @click="statusCourse = 'rejected'" 
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition whitespace-nowrap"
                            :class="statusCourse === 'rejected' ? 'bg-white text-red-600 border border-red-200 shadow-sm' : 'text-slate-500 hover:text-red-500'"
                        >
                            Rejected
                            <span class="ml-1 rounded-full bg-red-100 text-red-800 text-[10px] px-1.5 py-0.5 font-bold">{{ courseCounts.rejected }}</span>
                        </button>
                        <button 
                            @click="statusCourse = 'takedown'" 
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition whitespace-nowrap"
                            :class="statusCourse === 'takedown' ? 'bg-white text-rose-600 border border-rose-200 shadow-sm' : 'text-slate-500 hover:text-rose-500'"
                        >
                            Takedown
                            <span class="ml-1 rounded-full bg-rose-100 text-rose-800 text-[10px] px-1.5 py-0.5 font-bold">{{ courseCounts.takedown }}</span>
                        </button>
                    </div>

                    <!-- Search Course -->
                    <div class="relative w-full sm:w-72">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input
                            v-model="searchCourse"
                            type="text"
                            placeholder="Cari course atau perusahaan..."
                            class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="courses.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-600">Tidak ada course ditemukan</p>
                    <p class="text-xs text-slate-400 mt-1">Belum ada course dengan kriteria tersebut.</p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Nama Course</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Perusahaan</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Jumlah Modul</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Jumlah Peserta</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal Dibuat</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="course in courses.data" :key="course.id" class="group hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors text-sm leading-tight">{{ course.title }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">ID: #{{ course.id }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700 font-semibold">{{ course.company_name }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 font-bold">{{ course.lessons_count }} Modul</td>
                                <td class="px-5 py-4 text-sm text-slate-600 font-bold">{{ course.participants_count }} Peserta</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                        :class="course.moderation_status === 'approved' ? 'bg-emerald-50 text-emerald-700' : course.moderation_status === 'pending' ? 'bg-amber-50 text-amber-700' : course.moderation_status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-rose-50 text-rose-700'"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="course.moderation_status === 'approved' ? 'bg-emerald-500' : course.moderation_status === 'pending' ? 'bg-amber-500' : course.moderation_status === 'rejected' ? 'bg-red-500' : 'bg-rose-500'"
                                        ></span>
                                        {{ course.moderation_status.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">{{ formatDate(course.created_at) }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="openCourseDetail(course.id)" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50 shadow-sm transition">Detail</button>
                                        
                                        <button v-if="course.moderation_status === 'pending'" @click="quickApprove(course.id)" class="rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1.5 text-[11px] font-bold shadow-sm transition">Setujui</button>
                                        <button v-if="course.moderation_status === 'pending'" @click="openRejectModal(course, 'reject')" class="rounded-lg bg-red-500 hover:bg-red-600 text-white px-2.5 py-1.5 text-[11px] font-bold shadow-sm transition">Tolak</button>
                                        
                                        <button v-if="course.moderation_status === 'approved'" @click="openRejectModal(course, 'takedown')" class="rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-2.5 py-1.5 text-[11px] font-bold shadow-sm transition">Takedown</button>
                                        <button v-if="course.moderation_status === 'takedown'" @click="restoreCourse(course.id)" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-2.5 py-1.5 text-[11px] font-bold shadow-sm transition">Restore</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Course Pagination -->
                <div v-if="courses.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ courses.from }}–{{ courses.to }}</span> dari <span class="font-bold text-slate-700">{{ courses.total }}</span> course
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in courses.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            preserve-scroll
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                            :class="link.active ? 'bg-slate-900 text-white' : link.url ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>

            <!-- ════════ TAB 2: MODERASI KONTEN LMS ════════ -->
            <div v-else-if="currentTab === 'moderasi_konten'">
                <!-- Sub Tab Selectors & Search Content -->
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <!-- Modul, Quiz, Assignment Sub-tabs -->
                    <div class="flex items-center gap-1 rounded-xl bg-slate-50 border border-slate-200 p-1 w-fit">
                        <button 
                            @click="changeSubTab2('modul')"
                            class="rounded-lg px-3.5 py-1.5 text-xs font-bold transition"
                            :class="tab2Type === 'modul' ? 'bg-white text-slate-900 border border-slate-200 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        >
                            Modul
                        </button>
                        <button 
                            @click="changeSubTab2('quiz')"
                            class="rounded-lg px-3.5 py-1.5 text-xs font-bold transition"
                            :class="tab2Type === 'quiz' ? 'bg-white text-slate-900 border border-slate-200 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        >
                            Quiz
                        </button>
                        <button 
                            @click="changeSubTab2('assignment')"
                            class="rounded-lg px-3.5 py-1.5 text-xs font-bold transition"
                            :class="tab2Type === 'assignment' ? 'bg-white text-slate-900 border border-slate-200 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                        >
                            Assignment
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input
                            v-model="searchContent"
                            type="text"
                            placeholder="Cari judul konten..."
                            class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                        />
                    </div>
                </div>

                <!-- SUB TAB 2.1: MODUL (LESSONS) -->
                <div v-if="tab2Type === 'modul'">
                    <div v-if="moduls.data.length === 0" class="py-16 text-center text-slate-400">Tidak ada modul ditemukan.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Judul Modul</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Course</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Perusahaan</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal Dibuat</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="modul in moduls.data" :key="modul.id" class="hover:bg-slate-50/50">
                                    <td class="px-5 py-4 font-bold text-slate-800 text-sm leading-tight">{{ modul.title }}</td>
                                    <td class="px-5 py-4 text-xs text-slate-700 font-semibold">{{ modul.chapter?.course?.title || '-' }}</td>
                                    <td class="px-5 py-4 text-xs text-slate-600">{{ modul.chapter?.course?.company?.name || '-' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase ring-1"
                                            :class="modul.status === 'takedown' ? 'bg-red-50 text-red-700 ring-red-600/10' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/10'"
                                        >
                                            {{ modul.status || 'ACTIVE' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-slate-500">{{ formatDate(modul.created_at) }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <button v-if="modul.status !== 'takedown'" @click="takedownLesson(modul.id)" class="rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Takedown</button>
                                        <button v-else @click="restoreLesson(modul.id)" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Restore</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SUB TAB 2.2: QUIZ -->
                <div v-if="tab2Type === 'quiz'">
                    <div v-if="quizzes.data.length === 0" class="py-16 text-center text-slate-400">Tidak ada quiz ditemukan.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Nama Quiz</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Course</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Jumlah Soal</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="quiz in quizzes.data" :key="quiz.id" class="hover:bg-slate-50/50">
                                    <td class="px-5 py-4 font-bold text-slate-800 text-sm leading-tight">{{ quiz.title }}</td>
                                    <td class="px-5 py-4 text-xs text-slate-700 font-semibold">{{ quiz.chapter?.course?.title || '-' }}</td>
                                    <td class="px-5 py-4 text-xs font-bold text-slate-600">{{ quiz.questions_count }} Soal</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase ring-1"
                                            :class="quiz.status === 'takedown' ? 'bg-red-50 text-red-700 ring-red-600/10' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/10'"
                                        >
                                            {{ quiz.status || 'ACTIVE' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <button v-if="quiz.status !== 'takedown'" @click="takedownQuiz(quiz.id)" class="rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Takedown</button>
                                        <button v-else @click="restoreQuiz(quiz.id)" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Restore</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SUB TAB 2.3: ASSIGNMENT -->
                <div v-if="tab2Type === 'assignment'">
                    <div v-if="assignments.data.length === 0" class="py-16 text-center text-slate-400">Tidak ada assignment ditemukan.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Nama Assignment</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Course</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                    <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="assign in assignments.data" :key="assign.id" class="hover:bg-slate-50/50">
                                    <td class="px-5 py-4 font-bold text-slate-800 text-sm leading-tight">{{ assign.title }}</td>
                                    <td class="px-5 py-4 text-xs text-slate-700 font-semibold">{{ assign.chapter?.course?.title || '-' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase ring-1"
                                            :class="assign.status === 'takedown' ? 'bg-red-50 text-red-700 ring-red-600/10' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/10'"
                                        >
                                            {{ assign.status || 'ACTIVE' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <button v-if="assign.status !== 'takedown'" @click="takedownAssignment(assign.id)" class="rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Takedown</button>
                                        <button v-else @click="restoreAssignment(assign.id)" class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Restore</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ════════ TAB 3: KELOLA ENROLLMENT ════════ -->
            <div v-else-if="currentTab === 'kelola_enrollment'">
                <!-- Filter bar & Search -->
                <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                        <!-- Select Course -->
                        <select
                            v-model="courseEnrollment"
                            class="rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 px-3 py-2.5 shadow-sm focus:border-blue-400 focus:outline-none transition w-full sm:w-56"
                        >
                            <option value="all">Semua Course</option>
                            <option v-for="course in allCourses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>

                        <!-- Select Status Enrollment -->
                        <select
                            v-model="statusEnrollment"
                            class="rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 px-3 py-2.5 shadow-sm focus:border-blue-400 focus:outline-none transition w-full sm:w-44"
                        >
                            <option value="all">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="suspend">Suspend</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search input -->
                        <div class="relative w-full sm:w-64">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                            </svg>
                            <input
                                v-model="searchEnrollment"
                                type="text"
                                placeholder="Cari nama mahasiswa..."
                                class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                            />
                        </div>

                        <!-- Add Enrollment button -->
                        <button
                            @click="showAddEnrollmentModal = true"
                            class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs font-bold transition shadow-sm shrink-0 whitespace-nowrap"
                        >
                            + Tambah Enrollment
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="enrollments.data.length === 0" class="py-20 text-center text-slate-400">Tidak ada pendaftaran ditemukan.</div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Nama Mahasiswa</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Course</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Progress</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Tanggal Daftar</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="enroll in enrollments.data" :key="enroll.id" class="hover:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-800 text-sm leading-tight">{{ enroll.student_name }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ enroll.student_email }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-700 leading-snug">{{ enroll.course_title }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-24 rounded-full bg-slate-100 overflow-hidden shrink-0">
                                            <div class="h-full rounded-full bg-blue-500 transition-all duration-300" :style="`width: ${enroll.progress}%`"></div>
                                        </div>
                                        <span class="text-xs font-black text-slate-700">{{ enroll.progress }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                        :class="enroll.status === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : enroll.status === 'Aktif' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700'"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="enroll.status === 'Selesai' ? 'bg-emerald-500' : enroll.status === 'Aktif' ? 'bg-blue-500' : 'bg-red-500'"
                                        ></span>
                                        {{ enroll.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500">{{ formatDate(enroll.enrolled_at) }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="openEnrollmentDetail(enroll.id)" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50 shadow-sm transition">Detail</button>
                                        
                                        <button v-if="enroll.status !== 'Suspend'" @click="suspendEnrollment(enroll.id)" class="rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Suspend</button>
                                        <button v-else @click="activateEnrollment(enroll.id)" class="rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Aktifkan</button>
                                        
                                        <button @click="resetEnrollment(enroll.id)" class="rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Reset</button>
                                        <button @click="deleteEnrollment(enroll.id)" class="rounded-lg bg-red-500 hover:bg-red-600 text-white px-2.5 py-1.5 text-[11px] font-bold transition shadow-sm">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Enrollment Pagination -->
                <div v-if="enrollments.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ enrollments.from }}–{{ enrollments.to }}</span> dari <span class="font-bold text-slate-700">{{ enrollments.total }}</span> enrollment
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in enrollments.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            preserve-scroll
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                            :class="link.active ? 'bg-slate-900 text-white' : link.url ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>

            <!-- ════════ TAB 4: MONITORING LMS ════════ -->
            <div v-else-if="currentTab === 'monitoring'">
                <!-- Statistics Card Panel -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-5 bg-slate-50/50 border-b border-slate-100">
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
                        <div class="text-lg font-black text-blue-600">{{ monitoringStats.enrollment_today }}</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Enrollment Hari Ini</div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
                        <div class="text-lg font-black text-emerald-600">{{ monitoringStats.course_today }}</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Course Baru</div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
                        <div class="text-lg font-black text-amber-600">{{ monitoringStats.modul_today }}</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Modul Baru</div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
                        <div class="text-lg font-black text-purple-600">{{ monitoringStats.quiz_today }}</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Quiz Baru</div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
                        <div class="text-lg font-black text-[#5856D6]">{{ monitoringStats.assignment_today }}</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">Assignment Baru</div>
                    </div>
                </div>

                <!-- Filter & Search logs -->
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <select
                        v-model="roleActivity"
                        class="rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 px-3 py-2.5 shadow-sm focus:border-blue-400 focus:outline-none transition w-full sm:w-44"
                    >
                        <option value="all">Semua Aktivitas</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="perusahaan">Perusahaan</option>
                        <option value="admin">Admin</option>
                    </select>

                    <div class="relative w-full sm:w-72">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input
                            v-model="searchActivity"
                            type="text"
                            placeholder="Cari kata kunci aktivitas..."
                            class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition"
                        />
                    </div>
                </div>

                <!-- Empty logs state -->
                <div v-if="activityLogs.data.length === 0" class="py-16 text-center text-slate-400">Tidak ada log aktivitas ditemukan.</div>

                <!-- Table logs -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Waktu</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Pengguna</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Role</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Aktivitas</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">Keterangan</th>
                                <th class="px-5 py-3.5 text-[10px] font-black uppercase tracking-wider text-slate-400">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="log in activityLogs.data" :key="log.id" class="hover:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <div class="text-xs font-bold text-slate-800">{{ formatDate(log.created_at) }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ formatTime(log.created_at) }} WIB</div>
                                </td>
                                <td class="px-5 py-4 text-xs font-bold text-slate-700">{{ log.user_name }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
                                        :class="log.role === 'admin' ? 'bg-purple-50 text-purple-600 ring-1 ring-purple-500/10' : log.role === 'perusahaan' ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/10' : 'bg-blue-50 text-blue-600 ring-1 ring-blue-500/10'"
                                    >
                                        {{ log.role }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs font-black text-slate-800">{{ log.action }}</td>
                                <td class="px-5 py-4 text-xs text-slate-600 max-w-xs leading-normal">{{ log.description }}</td>
                                <td class="px-5 py-4 text-xs text-slate-400 font-mono">{{ log.ip_address }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Logs Pagination -->
                <div v-if="activityLogs.data.length > 0" class="border-t border-slate-100 px-5 py-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ activityLogs.from }}–{{ activityLogs.to }}</span> dari <span class="font-bold text-slate-700">{{ activityLogs.total }}</span> logs
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in activityLogs.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            preserve-scroll
                            class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                            :class="link.active ? 'bg-slate-900 text-white' : link.url ? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' : 'text-slate-300 cursor-not-allowed'"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ MODAL DETAIL COURSE (Tab 1) ════════════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showCourseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showCourseModal = false">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showCourseModal && selectedCourse" class="relative z-10 w-full max-w-xl rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-100">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900">Detail Course LMS</h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ selectedCourse.title }}</p>
                                    </div>
                                </div>
                                <button @click="showCourseModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="px-6 py-5 space-y-4 text-xs">
                                <div class="grid grid-cols-2 gap-3.5 bg-slate-50 rounded-xl p-4">
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Nama Course</p>
                                        <p class="font-bold text-slate-800 mt-0.5">{{ selectedCourse.title }}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Perusahaan Pembuat</p>
                                        <p class="font-bold text-slate-800 mt-0.5">{{ selectedCourse.company_name }}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Deskripsi</p>
                                        <p class="font-semibold text-slate-700 leading-normal mt-0.5">Pelajari prinsip dasar desain antarmuka dan pengalaman pengguna yang modern secara komprehensif.</p>
                                    </div>
                                </div>

                                <!-- Course Stats grid -->
                                <div class="grid grid-cols-4 gap-2 text-center pt-2">
                                    <div class="border border-slate-100 rounded-lg p-2 bg-white">
                                        <p class="font-bold text-slate-700 text-sm">2</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Chapter</p>
                                    </div>
                                    <div class="border border-slate-100 rounded-lg p-2 bg-white">
                                        <p class="font-bold text-slate-700 text-sm">{{ selectedCourse.lessons_count }}</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Modul</p>
                                    </div>
                                    <div class="border border-slate-100 rounded-lg p-2 bg-white">
                                        <p class="font-bold text-slate-700 text-sm">1</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Quiz</p>
                                    </div>
                                    <div class="border border-slate-100 rounded-lg p-2 bg-white">
                                        <p class="font-bold text-slate-700 text-sm">1</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Assignment</p>
                                    </div>
                                </div>

                                <!-- Quota & Participants count -->
                                <div class="bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 flex items-center justify-between">
                                    <div class="font-bold text-slate-700">Total Enrollment Peserta</div>
                                    <div class="font-black text-blue-700 text-base">{{ selectedCourse.participants_count }} Peserta</div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button @click="showCourseModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">Tutup</button>
                                
                                <button v-if="selectedCourse.moderation_status === 'pending'" @click="quickApprove(selectedCourse.id)" class="rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 text-xs font-bold transition shadow-sm">Setujui</button>
                                <button v-if="selectedCourse.moderation_status === 'pending'" @click="openRejectModal(selectedCourse, 'reject')" class="rounded-xl bg-red-500 hover:bg-red-600 text-white px-4 py-2 text-xs font-bold transition shadow-sm">Tolak</button>
                                
                                <button v-if="selectedCourse.moderation_status === 'approved'" @click="openRejectModal(selectedCourse, 'takedown')" class="rounded-xl bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 text-xs font-bold transition shadow-sm">Takedown</button>
                                <button v-if="selectedCourse.moderation_status === 'takedown'" @click="restoreCourse(selectedCourse.id)" class="rounded-xl bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 text-xs font-bold transition shadow-sm">Restore</button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ MODAL REJECT / TAKEDOWN COURSE (Tab 1) ═══════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showRejectModal = false">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showRejectModal && selectedCourseForModeration" class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                                        :class="moderationAction === 'takedown' ? 'bg-orange-100 text-orange-600' : 'bg-red-100 text-red-600'"
                                    >
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 6 6 18M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900">
                                            {{ moderationAction === 'takedown' ? 'Takedown Course' : 'Tolak Course' }}
                                        </h3>
                                        <p class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[240px]">{{ selectedCourseForModeration.title }}</p>
                                    </div>
                                </div>
                                <button @click="showRejectModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-5">
                                <div class="mb-4 flex items-start gap-3 rounded-xl p-3.5"
                                    :class="moderationAction === 'takedown' ? 'bg-orange-50 border border-orange-200 text-orange-800' : 'bg-red-50 border border-red-200 text-red-800'"
                                >
                                    <p class="text-xs leading-relaxed">
                                        <span v-if="moderationAction === 'takedown'">
                                            Course ini akan **ditarik dari penayangan** dan tidak dapat diakses mahasiswa. Status course akan berubah menjadi **Takedown**.
                                        </span>
                                        <span v-else>
                                            Course ini akan **ditolak** dan tidak akan dipublikasikan. Perusahaan pembuat dapat mengedit untuk mengajukan ulang.
                                        </span>
                                    </p>
                                </div>

                                <!-- Reason Field -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                        {{ moderationAction === 'takedown' ? 'Alasan Pencabutan / Takedown' : 'Alasan Penolakan' }}
                                        <span class="text-red-500 ml-0.5">*</span>
                                    </label>
                                    <textarea
                                        v-model="rejectForm.rejection_reason"
                                        rows="4"
                                        placeholder="Berikan alasan spesifik agar perusahaan memahami detail kendala pada course ini..."
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-100 transition resize-none"
                                        :class="rejectForm.errors.rejection_reason ? 'border-red-300 ring-red-100' : ''"
                                    ></textarea>
                                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-[11px] text-red-600">{{ rejectForm.errors.rejection_reason }}</p>
                                    <p class="mt-1 text-[10px] text-slate-400">Minimal 10 karakter.</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button @click="showRejectModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                                <button
                                    @click="submitCourseModeration"
                                    :disabled="rejectForm.processing || !rejectForm.rejection_reason.trim()"
                                    class="rounded-xl px-5 py-2 text-sm font-bold text-white shadow-sm transition disabled:opacity-60"
                                    :class="moderationAction === 'takedown' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-red-500 hover:bg-red-600'"
                                >
                                    {{ moderationAction === 'takedown' ? 'Ya, Takedown' : 'Ya, Tolak' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ MODAL ADD ENROLLMENT (Tab 3) ═════════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAddEnrollmentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showAddEnrollmentModal = false">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showAddEnrollmentModal" class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden border border-slate-100">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-slate-50">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-slate-800 text-sm">Tambah Enrollment Peserta</span>
                                </div>
                                <button @click="showAddEnrollmentModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-5 space-y-4 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Mahasiswa</label>
                                    <select
                                        v-model="addEnrollmentForm.student_id"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-600 shadow-sm focus:border-blue-400 focus:outline-none transition"
                                    >
                                        <option value="">-- Pilih Mahasiswa --</option>
                                        <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
                                    </select>
                                    <p v-if="addEnrollmentForm.errors.student_id" class="mt-1 text-[11px] text-red-600">{{ addEnrollmentForm.errors.student_id }}</p>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Course</label>
                                    <select
                                        v-model="addEnrollmentForm.course_id"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-600 shadow-sm focus:border-blue-400 focus:outline-none transition"
                                    >
                                        <option value="">-- Pilih Course --</option>
                                        <option v-for="course in allCourses" :key="course.id" :value="course.id">{{ course.title }}</option>
                                    </select>
                                    <p v-if="addEnrollmentForm.errors.course_id" class="mt-1 text-[11px] text-red-600">{{ addEnrollmentForm.errors.course_id }}</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                <button @click="showAddEnrollmentModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                                <button
                                    @click="submitAddEnrollment"
                                    :disabled="addEnrollmentForm.processing || !addEnrollmentForm.student_id || !addEnrollmentForm.course_id"
                                    class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 text-xs font-bold shadow-sm transition disabled:opacity-60"
                                >
                                    Daftarkan
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ═══ MODAL DETAIL ENROLLMENT (Tab 3) ══════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showEnrollmentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showEnrollmentModal = false">
                    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                    >
                        <div v-if="showEnrollmentModal" class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
                            <!-- Loading state -->
                            <div v-if="loadingEnrollmentDetail" class="flex flex-col items-center justify-center py-20">
                                <svg class="h-8 w-8 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <p class="text-xs font-bold text-slate-500 mt-3">Memuat detail pendaftaran...</p>
                            </div>

                            <!-- Data View -->
                            <div v-else-if="enrollmentModalData" class="flex flex-col">
                                <!-- Header -->
                                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-slate-50">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-black text-slate-900">Detail Pendaftaran</h3>
                                            <p class="text-[11px] text-slate-500 mt-0.5">{{ enrollmentModalData.course_title }}</p>
                                        </div>
                                    </div>
                                    <button @click="showEnrollmentModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Body -->
                                <div class="px-6 py-5 space-y-4 text-xs">
                                    <div class="grid grid-cols-2 gap-3.5 bg-slate-50 rounded-xl p-4">
                                        <div>
                                            <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Nama Mahasiswa</p>
                                            <p class="font-bold text-slate-800 mt-0.5">{{ enrollmentModalData.student_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Instruktur / Perusahaan</p>
                                            <p class="font-bold text-slate-800 mt-0.5">{{ enrollmentModalData.instructor_name }}</p>
                                        </div>
                                        <div class="col-span-2">
                                            <p class="text-slate-400 font-bold uppercase tracking-wider text-[9px]">Course yang Diikuti</p>
                                            <p class="font-bold text-slate-800 mt-0.5">{{ enrollmentModalData.course_title }}</p>
                                        </div>
                                    </div>

                                    <!-- Progress Rincian -->
                                    <div class="space-y-2.5">
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Progress Belajar</h4>
                                        
                                        <!-- Progress Bar -->
                                        <div class="flex items-center gap-3">
                                            <div class="h-3 flex-1 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-blue-500 transition-all duration-500" :style="`width: ${enrollmentModalData.progress}%`"></div>
                                            </div>
                                            <span class="text-sm font-black text-slate-800">{{ enrollmentModalData.progress }}%</span>
                                        </div>

                                        <!-- Item Breakdown -->
                                        <div class="grid grid-cols-3 gap-2 pt-1.5">
                                            <div class="border border-slate-100 rounded-lg p-2.5 bg-white text-center">
                                                <p class="font-bold text-slate-700">{{ enrollmentModalData.lesson_done }}</p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Lesson Selesai</p>
                                            </div>
                                            <div class="border border-slate-100 rounded-lg p-2.5 bg-white text-center">
                                                <p class="font-bold text-slate-700">{{ enrollmentModalData.quiz_done }}</p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Quiz Selesai</p>
                                            </div>
                                            <div class="border border-slate-100 rounded-lg p-2.5 bg-white text-center">
                                                <p class="font-bold text-slate-700">{{ enrollmentModalData.assignment_done }}</p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Tugas Selesai</p>
                                            </div>
                                        </div>

                                        <!-- Status Info -->
                                        <div class="flex items-center justify-between bg-slate-50 px-3.5 py-2.5 rounded-xl text-xs">
                                            <span class="text-slate-500 font-medium">Status Enrollment:</span>
                                            <span class="rounded px-2.5 py-0.5 font-bold"
                                                :class="enrollmentModalData.status === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : enrollmentModalData.status === 'Aktif' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700'"
                                            >
                                                {{ enrollmentModalData.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="flex items-center justify-end gap-2.5 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                    <button
                                        v-if="enrollmentModalData.status !== 'Suspend'"
                                        @click="suspendEnrollment(enrollmentModalData.id)"
                                        class="rounded-xl bg-orange-500 hover:bg-orange-600 px-4 py-2 text-xs font-bold text-white transition shadow-sm"
                                    >
                                        Suspend
                                    </button>
                                    <button
                                        v-else
                                        @click="activateEnrollment(enrollmentModalData.id)"
                                        class="rounded-xl bg-emerald-500 hover:bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition shadow-sm"
                                    >
                                        Aktifkan
                                    </button>

                                    <button @click="resetEnrollment(enrollmentModalData.id)" class="rounded-xl bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 text-xs font-bold transition shadow-sm">Reset Progres</button>
                                    <button @click="deleteEnrollment(enrollmentModalData.id)" class="rounded-xl bg-red-500 hover:bg-red-600 text-white px-4 py-2 text-xs font-bold transition shadow-sm">Hapus</button>
                                    <button @click="showEnrollmentModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
.delay-100 { animation-delay: 100ms; }
.delay-200 { animation-delay: 200ms; }
</style>
