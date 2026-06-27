<div id="toast-container" 
	style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"
	x-data="{
		toasts: [],
		addToast(message, type = 'success') {
			const id = Math.random().toString(36).substring(2, 9);
			this.toasts.push({ id, message, type });
			setTimeout(() => {
				this.removeToast(id);
			}, 5000);
		},
		removeToast(id) {
			const index = this.toasts.findIndex(t => t.id === id);
			if (index !== -1) {
				this.toasts.splice(index, 1);
			}
		}
	}"
	@toast.window="addToast($event.detail.message, $event.detail.type || 'success')"
>
	@php $toastId = \Illuminate\Support\Str::random(10); @endphp

	{{-- Success Messages --}}
	@if (session('success'))
	<div wire:key="toast-success-{{ $toastId }}" x-data="{ show: !sessionStorage.getItem('toast_{{ $toastId }}') }" x-show="show" x-init="if(show) { sessionStorage.setItem('toast_{{ $toastId }}', '1'); setTimeout(() => { show = false; setTimeout(() => $el.remove(), 500); }, 5000); }" x-transition:leave="hiding" class="custom-toast server-toast toast-success">
		<div class="toast-content">
			<div class="toast-icon">
				<svg style="flex-shrink: 0; min-width: 24px; min-height: 24px; display: block;" viewBox="0 0 24 24" width="24" height="24">
					<circle cx="12" cy="12" r="11" fill="#ffffff" />
					<path d="M7.5 12.5l3 3 6-6" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
				</svg>
			</div>
			<div class="toast-message">
				{{ session('success') }}
			</div>
		</div>
		<span class="toast-close" @click="show = false">&times;</span>
	</div>
	@endif

	{{-- Error Messages --}}
	@if (session('error'))
	<div wire:key="toast-error-{{ $toastId }}" x-data="{ show: !sessionStorage.getItem('toast_{{ $toastId }}') }" x-show="show" x-init="if(show) { sessionStorage.setItem('toast_{{ $toastId }}', '1'); setTimeout(() => { show = false; setTimeout(() => $el.remove(), 500); }, 5000); }" x-transition:leave="hiding" class="custom-toast server-toast toast-error">
		<div class="toast-content">
			<div class="toast-icon">
				<svg style="flex-shrink: 0; min-width: 24px; min-height: 24px; display: block;" viewBox="0 0 24 24" width="24" height="24">
					<circle cx="12" cy="12" r="11" fill="#ffffff" />
					<path d="M15 9l-6 6m0-6l6 6" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
				</svg>
			</div>
			<div class="toast-message">
				{{ session('error') }}
			</div>
		</div>
		<span class="toast-close" @click="show = false">&times;</span>
	</div>
	@endif

	{{-- Validation Errors --}}
	@if ($errors->any())
	<div wire:key="toast-validation-{{ $toastId }}" x-data="{ show: !sessionStorage.getItem('toast_{{ $toastId }}') }" x-show="show" x-init="if(show) { sessionStorage.setItem('toast_{{ $toastId }}', '1'); setTimeout(() => { show = false; setTimeout(() => $el.remove(), 500); }, 5000); }" x-transition:leave="hiding" class="custom-toast server-toast toast-error">
		<div class="toast-content">
			<div class="toast-icon">
				<svg style="flex-shrink: 0; min-width: 24px; min-height: 24px; display: block;" viewBox="0 0 24 24" width="24" height="24">
					<circle cx="12" cy="12" r="11" fill="#ffffff" />
					<path d="M15 9l-6 6m0-6l6 6" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
				</svg>
			</div>
			<div class="toast-message">
				<div style="font-weight: 600; margin-bottom: 4px;">Vui lòng kiểm tra lại:</div>
				<ul style="margin: 0; padding-left: 18px; line-height: 1.5; font-size: 14px;">
					@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		</div>
		<span class="toast-close" @click="show = false">&times;</span>
	</div>
	@endif

	{{-- Dynamic Client-side Toasts --}}
	<template x-for="toast in toasts" :key="toast.id">
		<div x-transition:leave="hiding" class="custom-toast server-toast" :class="toast.type === 'success' ? 'toast-success' : 'toast-error'">
			<div class="toast-content">
				<div class="toast-icon">
					<svg style="flex-shrink: 0; min-width: 24px; min-height: 24px; display: block;" viewBox="0 0 24 24" width="24" height="24">
						<circle cx="12" cy="12" r="11" fill="#ffffff" />
						<path x-show="toast.type === 'success'" d="M7.5 12.5l3 3 6-6" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
						<path x-show="toast.type !== 'success'" d="M15 9l-6 6m0-6l6 6" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
					</svg>
				</div>
				<div class="toast-message" x-text="toast.message"></div>
			</div>
			<span class="toast-close" @click="removeToast(toast.id)">&times;</span>
		</div>
	</template>

</div>

<style>
	.custom-toast.server-toast {
		pointer-events: auto;
		padding: 14px 20px;
		border-radius: 12px;
		box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
		min-width: 320px;
		max-width: 450px;
		word-break: break-word;
		position: relative;
		animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
		font-family: 'Inter', system-ui, -apple-system, sans-serif;
	}

	.toast-success {
		background-color: #15803d;
		color: #ffffff;
	}

	.toast-error {
		background-color: #dc2626;
		color: #ffffff;
	}

	.custom-toast.server-toast.hiding {
		animation: slideOutRight 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
	}

	@keyframes slideInRight {
		0% {
			transform: translateX(100%) translateY(-10px);
			opacity: 0;
		}
		100% {
			transform: translateX(0) translateY(0);
			opacity: 1;
		}
	}

	@keyframes slideOutRight {
		0% {
			transform: translateX(0);
			opacity: 1;
		}
		100% {
			transform: translateX(100%);
			opacity: 0;
		}
	}

	.toast-content {
		display: flex;
		align-items: center;
		gap: 12px;
		padding-right: 15px;
	}

	.toast-message {
		flex: 1;
		font-size: 14px;
		font-weight: 500;
		line-height: 1.4;
	}

	.toast-close {
		position: absolute;
		top: 50%;
		right: 12px;
		transform: translateY(-50%);
		cursor: pointer;
		font-size: 20px;
		line-height: 1;
		opacity: 0.7;
		color: #ffffff;
		transition: opacity 0.15s ease;
		pointer-events: auto !important;
		user-select: none;
	}

	.toast-close:hover {
		opacity: 1;
	}
</style>

