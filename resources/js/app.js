const pendingActions = new Set();
const pendingTimers = new WeakMap();

function isActionButton(element) {
    return element instanceof HTMLElement
        && element.matches('button, [role="button"], input[type="submit"], input[type="button"], [wire\\:click]');
}

function closestActionButton(element) {
    if (!(element instanceof HTMLElement)) {
        return null;
    }

    return element.closest('button, [role="button"], input[type="submit"], input[type="button"], [wire\\:click]');
}

function hasLivewireAction(element) {
    return element instanceof HTMLElement
        && (element.hasAttribute('wire:click') || element.closest('form[wire\\:submit]') !== null);
}

function beginAction(element) {
    if (!isActionButton(element) || element.hasAttribute('disabled') || element.getAttribute('aria-disabled') === 'true') {
        return;
    }

    pendingActions.add(element);
    document.body.classList.add('sr-action-pending');

    element.dataset.srActionPending = 'true';
    element.setAttribute('aria-busy', 'true');

    if (element instanceof HTMLButtonElement || element instanceof HTMLInputElement) {
        element.disabled = true;
    } else {
        element.setAttribute('aria-disabled', 'true');
    }

    clearTimeout(pendingTimers.get(element));
    pendingTimers.set(element, setTimeout(() => endAction(element), 15000));
}

function endAction(element) {
    if (!(element instanceof HTMLElement)) {
        return;
    }

    pendingActions.delete(element);
    clearTimeout(pendingTimers.get(element));
    pendingTimers.delete(element);
    element.removeAttribute('data-sr-action-pending');
    element.removeAttribute('aria-busy');

    if (element instanceof HTMLButtonElement || element instanceof HTMLInputElement) {
        element.disabled = false;
    } else {
        element.removeAttribute('aria-disabled');
    }

    if (pendingActions.size === 0) {
        document.body.classList.remove('sr-action-pending');
    }
}

function endAllActions() {
    Array.from(pendingActions).forEach(endAction);
    document.body.classList.remove('sr-action-pending');
}

document.addEventListener('click', (event) => {
    const button = closestActionButton(event.target);

    if (!button || !button.hasAttribute('wire:click')) {
        return;
    }

    beginAction(button);
}, true);

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const submitter = event.submitter instanceof HTMLElement
        ? event.submitter
        : form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');

    if (submitter) {
        setTimeout(() => beginAction(submitter), 0);
    }
}, true);

document.addEventListener('livewire:init', () => {
    if (!window.Livewire?.hook) {
        return;
    }

    window.Livewire.hook('request', ({ respond, succeed, fail }) => {
        respond?.(() => endAllActions());
        succeed?.(() => endAllActions());
        fail?.(() => endAllActions());
    });

    window.Livewire.hook('commit', ({ respond, succeed, fail }) => {
        respond?.(() => endAllActions());
        succeed?.(() => endAllActions());
        fail?.(() => endAllActions());
    });
});

document.addEventListener('livewire:navigated', endAllActions);
document.addEventListener('livewire:navigate', endAllActions);
document.addEventListener('livewire:error', endAllActions);
document.addEventListener('livewire:exception', endAllActions);

window.addEventListener('pageshow', endAllActions);

function registerMarketplaceStatsSlider() {
    if (!window.Alpine || window.Alpine.__marketplaceStatsSliderRegistered) {
        return;
    }

    window.Alpine.__marketplaceStatsSliderRegistered = true;

    window.Alpine.data('marketplaceStatsSlider', () => ({
        active: 0,
        total: 2,
        timer: null,

        init() {
            this.start();
        },

        go(index) {
            this.active = index;
        },

        next() {
            this.active = (this.active + 1) % this.total;
        },

        prev() {
            this.active = (this.active + this.total - 1) % this.total;
        },

        start() {
            this.stop();
            this.timer = window.setInterval(() => this.next(), 5200);
        },

        stop() {
            if (this.timer !== null) {
                window.clearInterval(this.timer);
                this.timer = null;
            }
        },
    }));

    queueMicrotask(() => {
        document.querySelectorAll('[data-marketplace-stats-slider]').forEach((element) => {
            if (!element._x_dataStack && typeof window.Alpine.initTree === 'function') {
                window.Alpine.initTree(element);
            }
        });
    });
}

document.addEventListener('alpine:init', registerMarketplaceStatsSlider);
document.addEventListener('livewire:init', registerMarketplaceStatsSlider);
registerMarketplaceStatsSlider();
