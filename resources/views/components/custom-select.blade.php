@props(['options' => [], 'placeholder' => 'Chọn...', 'value' => null])

<div x-data="{
    open: false,
    value: '',
    options: {{ json_encode($options) }},
    get selectedOption() {
        return this.options.find(o => o.value == this.value);
    },
    select(val) {
        this.value = val;
        this.open = false;
        $refs.hiddenSelect.value = val;
        $refs.hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
}" 
x-init="
    value = $refs.hiddenSelect.value;
    $watch('value', val => {
        if ($refs.hiddenSelect.value !== val) {
            $refs.hiddenSelect.value = val;
            $refs.hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    // Listen to livewire changes if hidden select is updated externally
    $refs.hiddenSelect.addEventListener('change', (e) => {
        if(value !== e.target.value) value = e.target.value;
    });
"
@click.away="open = false"
class="relative w-full"
:class="open ? 'z-40' : ''">

    <select x-ref="hiddenSelect" {{ $attributes->merge(['class' => 'hidden']) }}>
        @if($placeholder)
            <option value="" @selected($value === '' || $value === null)>{{ $placeholder }}</option>
        @endif
        @foreach($options as $opt)
            <option value="{{ $opt['value'] }}" @selected((string) $value === (string) $opt['value'])>{{ $opt['label'] }}</option>
        @endforeach
    </select>

    <!-- Custom UI -->
    <div @click="if(!$el.hasAttribute('disabled')) open = !open" 
         class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium transition-colors flex justify-between items-center shadow-sm h-11"
         :class="[
            open ? 'border-blue-500 ring-2 ring-blue-500 bg-white text-slate-900' : '',
            $el.hasAttribute('disabled') ? 'bg-slate-50 text-slate-500 cursor-not-allowed opacity-75' : 'cursor-pointer bg-white text-slate-900 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500'
         ]"
         {{ $attributes->has('disabled') ? 'disabled' : '' }}>
        <span x-text="selectedOption ? selectedOption.label : '{{ $placeholder }}'" class="truncate"></span>
        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </div>

    <!-- Dropdown menu -->
    <div x-show="open" 
         x-transition.opacity.duration.200ms
         class="scrollbar-custom absolute z-50 mt-2 w-full rounded-xl border border-slate-100 bg-white shadow-xl max-h-60 overflow-y-auto p-1.5"
         style="display: none;">
         
        @if($placeholder)
        <div @click="select('')"
             class="cursor-pointer rounded-lg px-3 py-2.5 transition-colors flex justify-between items-center mb-1"
             :class="value == '' ? 'bg-blue-50 text-blue-900 font-bold' : 'hover:bg-slate-50 text-slate-700 font-medium'">
             <span class="text-[15px]">{{ $placeholder }}</span>
             <div x-show="value == ''" class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></div>
        </div>
        @endif

        <template x-for="opt in options" :key="opt.value">
            <div @click="select(opt.value)"
                 class="cursor-pointer rounded-lg px-3 py-2.5 transition-colors flex justify-between items-center mb-1 last:mb-0"
                 :class="value == opt.value ? 'bg-blue-50' : 'hover:bg-slate-50'">
                 
                 <div class="flex flex-col truncate pr-2">
                     <span x-text="opt.label" class="text-[15px]" :class="value == opt.value ? 'text-blue-900 font-bold' : 'text-slate-800 font-medium'"></span>
                     <span x-show="opt.sub_label" x-text="opt.sub_label" class="text-xs mt-0.5 truncate" :class="value == opt.value ? 'text-blue-600/80 font-medium' : 'text-slate-500'"></span>
                 </div>
                 
                 <div x-show="value == opt.value" class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></div>
            </div>
        </template>
        <template x-if="options.length === 0">
            <div class="px-3 py-2 text-sm text-slate-500 text-center">Không có dữ liệu</div>
        </template>
    </div>
</div>
