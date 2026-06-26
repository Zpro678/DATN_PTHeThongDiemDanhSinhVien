@if (session('success') || session('error') || $errors->any())
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;">
	@php $toastId = \Illuminate\Support\Str::random(10); @endphp

	{{-- Success Messages --}}
	@if (session('success'))
	<div wire:key="toast-success-{{ $toastId }}" x-data="{ show: !sessionStorage.getItem('toast_{{ $toastId }}') }" x-show="show" x-init="if(show) { sessionStorage.setItem('toast_{{ $toastId }}', '1'); setTimeout(() => { show = false; setTimeout(() => $el.remove(), 500); }, 5000); }" x-transition:leave="hiding" class="custom-toast server-toast" style="background-color: #28a745; color: white;">
		<div class="toast-content">
			<div class="toast-icon">
				<svg style="flex-shrink: 0; min-width: 28px; min-height: 28px; display: block;" viewBox="0 0 24 24" width="28" height="28">
					<circle cx="12" cy="12" r="12" fill="#d4edda" />
					<path d="M7 13l3 3 7-7" stroke="#28a745" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
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
				<svg style="flex-shrink: 0; min-width: 28px; min-height: 28px; display: block;" viewBox="0 0 24 24" width="28" height="28">
					<circle cx="12" cy="12" r="12" fill="#f8d7da" />
					<path d="M15 9l-6 6m0-6l6 6" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
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
				<svg style="flex-shrink: 0; min-width: 28px; min-height: 28px; display: block;" viewBox="0 0 24 24" width="28" height="28">
					<circle cx="12" cy="12" r="12" fill="#f8d7da" />
					<path d="M15 9l-6 6m0-6l6 6" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
				</svg>
			</div>
			<div class="toast-message">
				<div style="font-weight: 600; margin-bottom: 4px;">Vui lòng kiểm tra lại:</div>
				<ul style="margin: 0; padding-left: 18px; line-height: 1.5;">
					@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		</div>
		<span class="toast-close" @click="show = false">&times;</span>
	</div>
	@endif

</div>
@endif

<style>
	.custom-toast.server-toast {
		pointer-events: auto;
		padding: 15px 20px;
		border-radius: 5px;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		min-width: 250px;
		max-width: 400px;
		word-break: break-word;
		position: relative;
		animation: slideInRight 0.4s ease-out forwards;
	}

	.custom-toast.server-toast.hiding {
		animation: slideOutRight 0.1s ease-in forwards !important;
	}

	@keyframes slideOutRight {
		0% {
			clip-path: inset(0 0 0 0);
			transform: translateX(0);
			opacity: 1;
		}

		100% {
			clip-path: inset(0 0 0 100%);
			transform: translateX(20px);
			opacity: 0;
		}
	}

	.toast-close {
		pointer-events: auto !important;
		user-select: none;
	}

	@keyframes slideInRight {
		0% {
			transform: translateX(100%);
			opacity: 0;
		}

		100% {
			transform: translateX(0);
			opacity: 1;
		}
	}

	.toast-error {
		background-color: #dc3545;
		color: white;
	}

	.toast-warning {
		background-color: #ffc107;
		color: #212529;
	}

	.toast-content {
		display: flex;
		align-items: center;
		gap: 12px;
		padding-right: 20px;
	}

	.toast-message {
		flex: 1;
		font-family: sans-serif;
		font-size: 15px;
		line-height: 1.4;
	}

	.toast-close {
		position: absolute;
		top: 3px;
		right: 12px;
		cursor: pointer;
		font-size: 24px;
		line-height: 1;
		opacity: 0.7;
		color: inherit;
	}

	.toast-close:hover {
		opacity: 1;
	}
</style>

