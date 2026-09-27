(() => {
  const modal = document.querySelector('[data-auth-modal]');
  if (!modal) return;

  const genericRegistrationError = modal.dataset.authGenericError;
  const genericLoginError = modal.dataset.authLoginError;

  const panels = [...modal.querySelectorAll('[data-auth-panel]')];
  let lastTrigger = null;

  const showErrors = (selector, errors) => {
    const fields = modal.querySelectorAll(selector);
    fields.forEach(field => {
      const messages = errors[field.dataset.authLoginError || field.dataset.authRegistrationError] || [];
      field.replaceChildren(...messages.map(message => {
        const item = document.createElement('div');
        item.textContent = message;
        return item;
      }));
      field.hidden = messages.length === 0;
      const input = field.closest('.auth-form__field, .auth-form__check')?.querySelector('input');
      input?.classList.toggle('auth-form__input--error', messages.length > 0);
      input?.setAttribute('aria-invalid', messages.length > 0 ? 'true' : 'false');
    });
  };

  const showRegistrationErrors = errors => {
    showErrors('[data-auth-registration-error]', errors);
  };

  const showLoginErrors = errors => {
    showErrors('[data-auth-login-error]', errors);
    const formError = modal.querySelector('[data-auth-login-form-error]');
    const messages = errors.form || [];
    formError.replaceChildren(...messages.map(message => {
      const item = document.createElement('div');
      item.textContent = message;
      return item;
    }));
    formError.hidden = messages.length === 0;
  };

  const showSuccess = message => {
    const container = modal.querySelector('[data-auth-success]');
    if (!container) return;
    container.textContent = message;
    container.hidden = false;
  };

  const setPanel = mode => {
    panels.forEach(panel => { panel.hidden = panel.dataset.authPanel !== mode; });
    modal.hidden = false;
    document.body.classList.add('auth-modal-open');
    const panel = modal.querySelector(`[data-auth-panel="${mode}"]`);
    panel?.querySelector('input:not([type="hidden"])')?.focus();
  };

  const close = () => {
    modal.hidden = true;
    panels.forEach(panel => { panel.hidden = true; });
    document.body.classList.remove('auth-modal-open');
    lastTrigger?.focus();
  };

  document.querySelectorAll('[data-auth-open]').forEach(trigger => {
    trigger.addEventListener('click', () => { lastTrigger = trigger; setPanel(trigger.dataset.authOpen); });
  });
  modal.querySelectorAll('[data-auth-switch]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      showRegistrationErrors([]);
      showLoginErrors({ email: [], password: [], form: [] });
      setPanel(trigger.dataset.authSwitch);
    });
  });
  modal.querySelectorAll('[data-auth-close]').forEach(trigger => trigger.addEventListener('click', close));
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) close(); });

  const registrationForm = modal.querySelector('[data-auth-registration-form]');
  registrationForm?.addEventListener('submit', async event => {
    event.preventDefault();
    const submit = registrationForm.querySelector('button[type="submit"]');
    submit.disabled = true;
    showRegistrationErrors([]);
    try {
      const response = await fetch(registrationForm.action, {
        body: new FormData(registrationForm),
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        method: 'POST'
      });
      const result = await response.json();
      if (!response.ok) {
        showRegistrationErrors(result.errors || { form: [genericRegistrationError] });
        return;
      }
      registrationForm.reset();
      showSuccess(result.message);
      setPanel('login');
    } catch (error) {
      showRegistrationErrors([genericRegistrationError]);
    } finally {
      submit.disabled = false;
    }
  });

  const loginForm = modal.querySelector('[data-auth-login-form]');
  loginForm?.addEventListener('submit', async event => {
    event.preventDefault();
    showLoginErrors({ email: [], password: [], form: [] });
    const email = loginForm.querySelector('[name="_username"]');
    const password = loginForm.querySelector('[name="_password"]');
    const errors = { email: [], password: [], form: [] };
    if (!email.value.trim()) errors.email.push(email.dataset.requiredMessage);
    else if (!email.validity.valid) errors.email.push(email.dataset.invalidMessage);
    if (!password.value) errors.password.push(password.dataset.requiredMessage);
    if (errors.email.length || errors.password.length) {
      showLoginErrors(errors);
      (errors.email.length ? email : password).focus();
      return;
    }

    const submit = loginForm.querySelector('button[type="submit"]');
    submit.disabled = true;
    try {
      const response = await fetch(loginForm.action, {
        body: new FormData(loginForm),
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        method: 'POST'
      });
      const result = await response.json();
      if (!response.ok) {
        showLoginErrors(result.errors || { form: [genericLoginError] });
        return;
      }
      window.location.reload();
    } catch (error) {
      showLoginErrors({ form: [genericLoginError] });
    } finally {
      submit.disabled = false;
    }
  });

  document.querySelectorAll('[data-user-menu]').forEach(menu => {
    const toggle = menu.querySelector('[data-user-menu-toggle]');
    const panel = menu.querySelector('[data-user-menu-panel]');
    toggle.addEventListener('click', event => {
      event.stopPropagation();
      const open = !panel.hidden;
      document.querySelectorAll('[data-user-menu-panel]').forEach(item => { item.hidden = true; });
      document.querySelectorAll('[data-user-menu-toggle]').forEach(item => item.setAttribute('aria-expanded', 'false'));
      panel.hidden = open;
      toggle.setAttribute('aria-expanded', String(!open));
    });
  });
  document.addEventListener('click', event => {
    if (!event.target.closest('[data-user-menu]')) {
      document.querySelectorAll('[data-user-menu-panel]').forEach(item => { item.hidden = true; });
      document.querySelectorAll('[data-user-menu-toggle]').forEach(item => item.setAttribute('aria-expanded', 'false'));
    }
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      document.querySelectorAll('[data-user-menu-panel]').forEach(item => { item.hidden = true; });
      document.querySelectorAll('[data-user-menu-toggle]').forEach(item => item.setAttribute('aria-expanded', 'false'));
    }
  });

  const initial = modal.dataset.authInitial;
  if (initial) setPanel(initial);
})();