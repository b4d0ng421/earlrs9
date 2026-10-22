(() => {
  'use strict';

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const toast = document.getElementById('site-toast');
  let toastTimer = null;

  function showToast(message, isError = false) {
    if (!toast) return;

    toast.textContent = message;
    toast.classList.toggle('is-error', isError);
    toast.hidden = false;

    if (toastTimer) {
      window.clearTimeout(toastTimer);
    }

    toastTimer = window.setTimeout(() => {
      toast.hidden = true;
    }, 6000);
  }

  function hideToast() {
    if (!toast) return;
    toast.hidden = true;
  }

  function updateCartCounter(totalItems) {
    const counter = document.getElementById('cart-counter');
    if (counter) {
      counter.textContent = String(totalItems);
    }
  }

  // Track in-flight add-to-cart requests so a fast recorded replay
  // (click "Add to Cart" then immediately click a link) never navigates
  // away before the cart has actually been updated.
  let pendingCartRequests = 0;
  let queuedNavigation = null;

  function finishCartRequest() {
    pendingCartRequests = Math.max(0, pendingCartRequests - 1);
    if (pendingCartRequests === 0 && queuedNavigation) {
      const url = queuedNavigation;
      queuedNavigation = null;
      window.location.href = url;
    }
  }

  document.addEventListener('click', (event) => {
    if (pendingCartRequests === 0 || event.defaultPrevented) return;
    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
    const href = link.getAttribute('href') || '';
    if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
    event.preventDefault();
    queuedNavigation = link.href;
  });

  const navToggle = document.getElementById('nav-toggle');
  const siteNav = document.getElementById('site-nav');

  if (navToggle && siteNav) {
    navToggle.addEventListener('click', () => {
      const isOpen = siteNav.classList.toggle('is-open');
      navToggle.classList.toggle('is-open', isOpen);
      navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    siteNav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        siteNav.classList.remove('is-open');
        navToggle.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  document.querySelectorAll('.details-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('aria-controls');
      const details = targetId
        ? document.getElementById(targetId)
        : button.closest('.product-card')?.querySelector('.details-content');

      if (!details) return;

      const isOpen = details.classList.toggle('is-open');
      button.setAttribute('aria-expanded', String(isOpen));
      button.textContent = isOpen ? 'Hide Details' : 'View Details';
    });
  });

  document.querySelectorAll('.add-to-cart-btn').forEach((button) => {
    button.addEventListener('click', async () => {
      const productId = button.dataset.id || '';
      const card = button.closest('.product-card');
      const quantityInput = card?.querySelector('.qty-input');
      const feedback = card?.querySelector('.cart-feedback');

      let quantity = parseInt(quantityInput?.value || '1', 10);
      if (!Number.isFinite(quantity) || quantity < 1) quantity = 1;
      if (quantity > 99) quantity = 99;
      if (quantityInput) quantityInput.value = String(quantity);

      const originalText = button.textContent;
      button.disabled = true;
      button.textContent = 'Adding...';
      pendingCartRequests += 1;
      hideToast();

      if (feedback) {
        feedback.textContent = '';
        feedback.classList.remove('is-error');
      }

      try {
        const body = new URLSearchParams({
          action: 'add',
          id: productId,
          quantity: String(quantity),
          csrf_token: csrfToken,
        });

        const response = await fetch('cart_action.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
          },
          body,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok || data.status !== 'success') {
          throw new Error(data.message || 'Could not add this item to your cart.');
        }

        updateCartCounter(data.totalItems);

        const message = `${quantity} x ${data.item?.name || 'item'} added to cart.`;
        if (feedback) {
          feedback.textContent = message;
          feedback.classList.remove('is-error');
        }
        showToast(message);
      } catch (error) {
        const message = error instanceof Error
          ? error.message
          : 'Could not add this item to your cart.';

        if (feedback) {
          feedback.textContent = message;
          feedback.classList.add('is-error');
        }
        showToast(message, true);
      } finally {
        button.disabled = false;
        button.textContent = originalText;
        finishCartRequest();
      }
    });
  });

  const confirmDialog = document.getElementById('confirm-dialog');
  const confirmMessage = document.getElementById('confirm-message');
  const confirmOk = document.getElementById('confirm-ok');
  const confirmCancel = document.getElementById('confirm-cancel');
  let pendingForm = null;

  function hideConfirmDialog() {
    if (confirmDialog) {
      confirmDialog.hidden = true;
    }
    pendingForm = null;
  }

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      pendingForm = form;

      if (confirmMessage) {
        confirmMessage.textContent = form.dataset.confirm || 'Are you sure?';
      }
      if (confirmDialog) {
        confirmDialog.hidden = false;
      }
      if (confirmCancel) {
        confirmCancel.focus();
      }
    });
  });

  if (confirmOk) {
    confirmOk.addEventListener('click', () => {
      if (!pendingForm) return;

      const form = pendingForm;
      hideConfirmDialog();
      form.submit();
    });
  }

  if (confirmCancel) {
    confirmCancel.addEventListener('click', hideConfirmDialog);
  }

  if (confirmDialog) {
    confirmDialog.addEventListener('click', (event) => {
      if (event.target === confirmDialog) {
        hideConfirmDialog();
      }
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && confirmDialog && !confirmDialog.hidden) {
      hideConfirmDialog();
    }
  });
})();
