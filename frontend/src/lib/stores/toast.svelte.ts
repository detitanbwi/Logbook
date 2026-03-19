export type ToastType = 'info' | 'success' | 'warning' | 'error';

export interface ToastMessage {
	id: string;
	type: ToastType;
	message: string;
	duration?: number;
}

function createToastStore() {
	let toasts = $state<ToastMessage[]>([]);

	function add(message: string, type: ToastType = 'info', duration: number = 3000) {
		const id = crypto.randomUUID();
		const toast = { id, message, type, duration };
		toasts = [...toasts, toast];

		if (duration > 0) {
			setTimeout(() => {
				remove(id);
			}, duration);
		}
		
		return id;
	}

	function remove(id: string) {
		toasts = toasts.filter((t) => t.id !== id);
	}

	return {
		get toasts() {
			return toasts;
		},
		add,
		remove,
		success: (msg: string, duration?: number) => add(msg, 'success', duration),
		error: (msg: string, duration?: number) => add(msg, 'error', duration),
		warning: (msg: string, duration?: number) => add(msg, 'warning', duration),
		info: (msg: string, duration?: number) => add(msg, 'info', duration)
	};
}

export const toastStore = createToastStore();
