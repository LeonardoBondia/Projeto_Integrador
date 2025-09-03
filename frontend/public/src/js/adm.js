import { API_BASE } from "./config.js";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formAdm");
  const modal = document.getElementById("feedbackModal");
  const fbTitle = document.getElementById("fbTitle");
  const fbMessage = document.getElementById("fbMessage");
  const fbIcon = document.getElementById("fbIcon");
  const fbOk = document.getElementById("fbOk");
 
  if (!form) return;


   function abrirModal(titulo, mensagem, sucesso = true) {
    fbTitle.textContent = titulo;
    fbMessage.textContent = mensagem;
    fbIcon.textContent = sucesso ? "✓" : "⚠️";

    modal.setAttribute("aria-hidden", "false");
    modal.style.display = "flex";
   }

   fbOk.addEventListener("click", () => {
    modal.setAttribute("aria-hidden", "true");
    modal.style.display = "none";
  });
  modal.querySelector(".fb-backdrop").addEventListener("click", () => {
    modal.setAttribute("aria-hidden", "true");
    modal.style.display = "none";
  }); 


  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const dados = {};
    form.querySelectorAll("input, select, textarea").forEach((el) => {
      if (el.name) dados[el.name] = el.value.trim();
    });

    try {
      const resp = await fetch(`${API_BASE}/criar_admin.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(dados),
      });
      const data = await resp.json().catch(() => ({}));

      if (!resp.ok || data.error) {
         abrirModal("Erro", data.message || "Dados inválidos. Revise os campos.", false);
        return;
      }

      abrirModal("Tudo certo!", "Cadastro realizado com sucesso!", true);
      form.reset();
    } catch (err) {
      alert("Falha de conexão. Tente novamente.");
    }
  });
});
