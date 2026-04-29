<template>
    <div class="relative" ref="dropdownRef" :class="{'z-50': isOpen, 'z-10': !isOpen}">
        <div 
            class="flex items-center w-full bg-white border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors duration-200 rounded-xl py-3 px-4 outline-none text-sm text-[#0F172A] cursor-text focus-within:ring-2 focus-within:ring-[#2563EB]/20 focus-within:border-[#2563EB]"
            @click="openDropdown"
        >
            <input 
                type="text" 
                v-model="searchQuery" 
                :placeholder="placeholder"
                class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm placeholder-[#94A3B8]"
                @input="onInput"
                @focus="openDropdown"
            />
            <svg class="h-4 w-4 text-[#94A3B8] transition-transform duration-200 ml-2 shrink-0" :class="{'rotate-180': isOpen}" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>

        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <ul v-show="isOpen" class="absolute left-0 w-full mt-1 bg-white border border-[#E2E8F0] shadow-xl max-h-60 rounded-xl py-2 text-sm overflow-auto custom-scrollbar">
                <li v-if="filteredOptions.length === 0" class="px-4 py-3 text-[#94A3B8] italic text-center">
                    Tidak ditemukan
                </li>
                <li 
                    v-for="(option, index) in filteredOptions" 
                    :key="index"
                    @click.stop="selectOption(option)"
                    class="px-4 py-2.5 cursor-pointer transition-colors hover:bg-[#EFF6FF] hover:text-[#2563EB]"
                    :class="{'bg-[#EFF6FF] text-[#2563EB] font-bold border-l-2 border-[#2563EB]': option === modelValue, 'border-l-2 border-transparent': option !== modelValue}"
                >
                    {{ option }}
                </li>
            </ul>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    options: {
        type: Array,
        required: true
    },
    placeholder: {
        type: String,
        default: 'Pilih opsi...'
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref(props.modelValue);
const dropdownRef = ref(null);

watch(() => props.modelValue, (newVal) => {
    if (newVal !== searchQuery.value) {
        searchQuery.value = newVal;
    }
});

const filteredOptions = computed(() => {
    if (!searchQuery.value || searchQuery.value === props.modelValue) {
        return props.options;
    }
    const lowerQuery = searchQuery.value.toLowerCase();
    return props.options.filter(opt => opt.toLowerCase().includes(lowerQuery));
});

const openDropdown = () => {
    isOpen.value = true;
};

const closeDropdown = () => {
    isOpen.value = false;
    if (searchQuery.value !== props.modelValue) {
        searchQuery.value = props.modelValue;
    }
};

const onInput = () => {
    isOpen.value = true;
    emit('update:modelValue', '');
};

const selectOption = (option) => {
    searchQuery.value = option;
    emit('update:modelValue', option);
    isOpen.value = false;
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #CBD5E1;
    border-radius: 20px;
}
</style>
