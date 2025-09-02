// src/js/adm.js
// Requer: feedback.js (window.showFeedback)

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formAdm');
  if (!form) return;

  const unmask = (v) => v.replace(/\D/g, '');

  const serialize = (formEl) => {
    const data = {};
    formEl.querySelectorAll('input, select, textarea').forEach((el) => {
      if (!el.name) return;
      let val = el.value.trim();

      // Campos com máscara
      if (['cpf_adm', 'contato_adm', 'cep_adm'].includes(el.name)) {
        val = unmask(val);
      }
      data[el.name] = val;
    });
    return data;
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!form.reportValidity()) return;

    const payload = serialize(form);

    try {
      const resp = await fetch('../api/criar_adm.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const data = await resp.json().catch(() => ({}));

      if (resp.ok && (data.sucesso || data.success)) {
        window.showFeedback({
          type: 'success',
          title: 'Cadastro realizado com sucesso!',
          message: data.mensagem || 'Administrador(a) cadastrado(a).',
          onClose: () => form.reset(),
        });
      } else {
        window.showFeedback({
          type: 'error',
          title: 'Dados inválidos. Verifique os dados digitados.',
          message: data.erro || data.error || 'Revise os campos e tente novamente.',
        });
      }
    } catch (err) {
      window.showFeedback({
        type: 'error',
        title: 'Falha de conexão',
        message: 'Não foi possível enviar os dados. Tente novamente.',
      });
    }
  });
});
