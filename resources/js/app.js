import './bootstrap';

const profileIcon = document.getElementById('profile');
const profileContainer = document.querySelector('.profile-container');

let profileModal;

profileIcon.addEventListener('click', function(event) {
    event.stopPropagation();

    if (profileModal) {
        profileModal.style.display = profileModal.style.display === 'block' ? 'none' : 'block';
        return;
    }

    profileModal = document.createElement('div');
    profileModal.classList.add('profile-modal');

    // Monta o conteúdo usando o primeiro usuário da sessão
    const u = usuario[0]; // ou itere se quiser todos
    profileModal.innerHTML = `
        <p><strong>Usuário:</strong> ${u.id_usuario_clinica}</p>
        <p><strong>Clínica:</strong> ${u.id_clinica}</p>
        <p><strong>Status:</strong> ${u.sit_usuario}</p>
        <p><a href="/logout">Sair</a></p>
    `;

    profileContainer.appendChild(profileModal);
    profileModal.style.display = 'block';

    profileModal.addEventListener('click', function(event) {
        event.stopPropagation();
    });
});

// Fecha o modal ao clicar fora
document.addEventListener('click', function() {
    if (profileModal) profileModal.style.display = 'none';
});