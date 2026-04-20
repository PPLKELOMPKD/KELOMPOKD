<script setup>
import SikaraLayout from '@/Layouts/SikaraLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    profile: Object,
    skills: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const topSkills = computed(() => [...(props.skills ?? [])].sort((a, b) => b.proficiency - a.proficiency));
const profileReady = computed(() => [props.profile?.nim, props.profile?.department, props.profile?.study_program, props.profile?.gpa].filter(Boolean).length);
const profileCompletion = computed(() => Math.round((profileReady.value / 4) * 100));

const profileForm = useForm({
    nim: props.profile?.nim ?? '',
    department: props.profile?.department ?? '',
    study_program: props.profile?.study_program ?? '',
    gpa: props.profile?.gpa ?? '',
    phone: props.profile?.phone ?? '',
    university: props.profile?.university ?? '',
    location: props.profile?.location ?? '',
    bio: props.profile?.bio ?? '',
});

const skillForm = useForm({
    name: '',
    proficiency: 50,
});

const submitProfile = () => profileForm.post(route('profile.store'));
const submitSkill = () => skillForm.post(route('skills.store'), { onSuccess: () => skillForm.reset('name', 'proficiency') });
</script>

<template>
    <Head title="Profil Mahasiswa" />

    <SikaraLayout title="Profil Saya" subtitle="Lengkapi biodata akademik, unggah kesiapan karier, dan tampilkan keterampilan utama Anda sesuai komposisi Figma Sprint 1.">
        <div class="grid gap-6 2xl:grid-cols-[389px_minmax(0,1fr)_448px]">
            <div class="space-y-6">
                <section class="rounded-[16px] border border-[#eaecf0] bg-white px-6 pt-6 pb-5 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-32 w-32 items-center justify-center rounded-full border-4 border-[#f3f4f6] bg-[#e5e7eb] text-4xl font-semibold text-[#344054]">
                            {{ user?.name?.charAt(0) ?? 'S' }}
                        </div>
                        <h3 class="mt-5 text-[20px] font-semibold text-[#101828]">{{ user?.name }}</h3>
                        <p class="mt-1 text-base text-[#475467]">{{ profile?.department || 'Lengkapi jurusan Anda' }}</p>
                        <p class="mt-1 text-sm text-[#667085]">{{ profile?.university || 'Universitas belum diisi' }}</p>
                        <button class="mt-4 inline-flex h-10 w-full items-center justify-center rounded-xl bg-black px-4 text-base font-medium text-white">
                            Edit Profil
                        </button>
                    </div>

                    <div class="mt-6 border-t border-[#f2f4f7] pt-5 text-sm text-[#344054]">
                        <div class="flex items-center gap-3 py-2">
                            <span class="text-[#98a2b3]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 6h16v12H4z" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </span>
                            <span>{{ user?.email }}</span>
                        </div>
                        <div class="flex items-center gap-3 py-2">
                            <span class="text-[#98a2b3]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72l.35 2.82a2 2 0 0 1-.57 1.7L7.1 10.1a16 16 0 0 0 6.8 6.8l1.86-1.79a2 2 0 0 1 1.7-.57l2.82.35A2 2 0 0 1 22 16.92Z" />
                                </svg>
                            </span>
                            <span>{{ profile?.phone || 'Nomor telepon belum diisi' }}</span>
                        </div>
                        <div class="flex items-center gap-3 py-2">
                            <span class="text-[#98a2b3]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                            </span>
                            <span>{{ profile?.location || 'Lokasi belum diisi' }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <h3 class="text-[18px] font-semibold text-[#101828]">Tentang Saya</h3>
                    <p class="mt-4 text-sm leading-8 text-[#475467]">
                        {{ profile?.bio || 'Tambahkan ringkasan singkat mengenai minat, tujuan karier, dan pengalaman Anda agar profil SIKARA terlihat lebih siap untuk proses magang.' }}
                    </p>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <h3 class="text-[18px] font-semibold text-[#101828]">Resume / CV</h3>
                    <div class="mt-4 rounded-xl border-2 border-dashed border-[#d0d5dd] px-6 py-8 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center text-[#98a2b3]">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 16V6" />
                                <path d="m7 11 5-5 5 5" />
                                <path d="M4 18v1a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1" />
                            </svg>
                        </div>
                        <p class="mt-4 text-base text-[#475467]">Unggah CV Terbaru</p>
                        <p class="mt-1 text-sm text-[#667085]">atau seret file ke sini</p>
                        <p class="mt-2 text-sm text-[#98a2b3]">PDF, DOC (Max. 5MB)</p>
                    </div>
                    <a :href="route('cv.download')" class="mt-4 flex h-11 w-full items-center justify-center rounded-xl bg-[#1e293b] px-4 text-base font-medium text-white">
                        Generate CV Otomatis (PDF)
                    </a>
                    <div class="mt-4 flex items-center justify-between rounded-xl bg-[#f9fafb] px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-[#101828]">CV_{{ (user?.name || 'Mahasiswa').replaceAll(' ', '') }}.pdf</p>
                            <p class="text-xs text-[#667085]">Diunggah dari data profil terbaru</p>
                        </div>
                        <a :href="route('cv.download')" class="rounded-lg p-2 text-[#667085]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 3v12" />
                                <path d="m7 10 5 5 5-5" />
                                <path d="M5 21h14" />
                            </svg>
                        </a>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[20px] font-semibold text-[#101828]">Profil Keterampilan</h3>
                        <span class="text-sm font-medium text-black">+ Tambah Skill</span>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div v-for="skill in topSkills" :key="skill.id" class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-[#344054]">{{ skill.name }}</span>
                                <span class="font-semibold text-black">{{ skill.proficiency }}%</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-[#e5e7eb]">
                                <div class="h-2.5 rounded-full bg-black" :style="{ width: `${skill.proficiency}%` }" />
                            </div>
                        </div>

                        <div v-if="!topSkills.length" class="rounded-xl bg-[#f9fafb] px-4 py-5 text-sm text-[#667085]">
                            Belum ada skill. Tambahkan skill pertama Anda di panel kanan.
                        </div>
                    </div>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[20px] font-semibold text-[#101828]">Sertifikasi</h3>
                        <span class="text-sm font-medium text-black">+ Tambah Sertifikat</span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="flex items-start gap-4 rounded-xl bg-[#f9fafb] p-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#e5e7eb] text-[#475467]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path d="m8.5 12.5-1 7 4.5-2 4.5 2-1-7" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-[#101828]">React Professional Certificate</p>
                                <p class="text-sm text-[#475467]">Meta</p>
                                <p class="text-xs text-[#667085]">2024</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 rounded-xl bg-[#f9fafb] p-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#e5e7eb] text-[#475467]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path d="m8.5 12.5-1 7 4.5-2 4.5 2-1-7" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-[#101828]">AWS Cloud Practitioner</p>
                                <p class="text-sm text-[#475467]">Amazon Web Services</p>
                                <p class="text-xs text-[#667085]">2023</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 rounded-xl bg-[#f9fafb] p-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#e5e7eb] text-[#475467]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path d="m8.5 12.5-1 7 4.5-2 4.5 2-1-7" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-[#101828]">Google UX Design Certificate</p>
                                <p class="text-sm text-[#475467]">Google</p>
                                <p class="text-xs text-[#667085]">2023</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-[16px] border border-[#eaecf0] bg-white p-6 shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                    <h3 class="text-[20px] font-semibold text-[#101828]">Data Akademik</h3>
                    <form class="mt-5 grid gap-4 md:grid-cols-2" @submit.prevent="submitProfile">
                        <input v-model="profileForm.nim" class="h-12 rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="NIM" />
                        <input v-model="profileForm.gpa" class="h-12 rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="IPK" />
                        <input v-model="profileForm.department" class="h-12 rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="Jurusan" />
                        <input v-model="profileForm.study_program" class="h-12 rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="Program Studi" />
                        <input v-model="profileForm.university" class="h-12 rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="Universitas" />
                        <input v-model="profileForm.phone" class="h-12 rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="Nomor Telepon" />
                        <input v-model="profileForm.location" class="h-12 rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black md:col-span-2" placeholder="Lokasi" />
                        <textarea v-model="profileForm.bio" class="min-h-32 rounded-xl border border-[#d0d5dd] px-4 py-3 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black md:col-span-2" placeholder="Tentang saya" />
                        <button class="flex h-12 items-center justify-center rounded-xl bg-black px-5 text-sm font-medium text-white md:col-span-2">
                            Simpan Profil
                        </button>
                    </form>
                </section>
            </div>

            <aside class="rounded-[16px] border border-[#eaecf0] bg-white shadow-[0_1px_3px_rgba(16,24,40,0.1),0_1px_2px_rgba(16,24,40,0.06)]">
                <div class="flex items-center justify-between border-b border-[#eaecf0] px-6 py-6">
                    <h3 class="text-[20px] font-semibold text-[#101828]">Tambah Skill</h3>
                    <button type="button" class="text-[#667085]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="m6 6 12 12" />
                            <path d="M18 6 6 18" />
                        </svg>
                    </button>
                </div>

                <form class="space-y-6 p-6" @submit.prevent="submitSkill">
                    <div>
                        <label class="text-sm font-medium text-[#344054]">Nama Skill</label>
                        <input v-model="skillForm.name" class="mt-3 h-12 w-full rounded-xl border border-[#d0d5dd] px-4 text-sm text-[#101828] focus:border-black focus:outline-none focus:ring-1 focus:ring-black" placeholder="Contoh: React, Figma, Python" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-sm font-medium text-[#344054]">
                            <label>Tingkat Keahlian</label>
                            <span class="font-semibold text-black">{{ skillForm.proficiency }}%</span>
                        </div>
                        <input v-model="skillForm.proficiency" type="range" min="1" max="100" class="mt-4 w-full accent-black" />
                        <div class="mt-3 flex items-center justify-between text-xs text-[#98a2b3]">
                            <span>Pemula</span>
                            <span>Menengah</span>
                            <span>Ahli</span>
                        </div>
                    </div>

                    <div class="rounded-xl bg-[#f9fafb] p-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-[#475467]">Kelengkapan Profil</span>
                            <span class="font-semibold text-[#101828]">{{ profileCompletion }}%</span>
                        </div>
                        <div class="mt-3 h-2.5 rounded-full bg-[#e5e7eb]">
                            <div class="h-2.5 rounded-full bg-black" :style="{ width: `${profileCompletion}%` }" />
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <button class="flex h-12 w-full items-center justify-center rounded-xl bg-black px-4 text-base font-medium text-white">
                            Simpan Skill
                        </button>
                        <button type="button" class="flex h-12 w-full items-center justify-center rounded-xl border border-[#d0d5dd] bg-white px-4 text-base font-medium text-[#344054]">
                            Batal
                        </button>
                    </div>
                </form>
            </aside>
        </div>
    </SikaraLayout>
</template>
