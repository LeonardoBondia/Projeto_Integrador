// login.js

document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  if (!form) return;
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = form.querySelector('input[type="email"]').value;
    const senha = form.querySelector('input[type="password"]').value;
    try {
      const resp = await fetch('../api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, senha })
      });
      const data = await resp.json();
      if (resp.ok && data.sucesso) {
        // Redireciona para dashboard ou página principal
        window.location.href = 'index.html';
      } else {
        alert(data.erro || 'Usuário ou senha inválidos.');
      }
    } catch (err) {
      alert('Erro ao conectar ao servidor.');
    }
  });
});