document.addEventListener('DOMContentLoaded', function () {
    const mainContentArea = document.querySelector('.content-area');
    const navLinks = document.querySelectorAll('.nav-link');

    function loadContent(url, targetElementId) {
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                mainContentArea.innerHTML = html;
                // Re-ejecutar scripts si es necesario
                const scripts = mainContentArea.querySelectorAll('script');
                scripts.forEach(script => {
                    const newScript = document.createElement('script');
                    Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(script.innerHTML));
                    script.parentNode.replaceChild(newScript, script);
                });
            })
            .catch(error => {
                console.error('Error al cargar el contenido:', error);
                mainContentArea.innerHTML = `<p style="color: red;">Error al cargar la sección. Por favor, intente de nuevo.</p>`;
            });
    }

    // Manejar clics en los enlaces de la barra lateral
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetUrl = this.getAttribute('data-target');
            if (targetUrl) {
                loadContent(targetUrl);

                // Actualizar clase activa en la barra lateral
                navLinks.forEach(item => item.parentElement.classList.remove('active'));
                this.parentElement.classList.add('active');

                // Actualizar URL en la barra de direcciones (opcional)
                history.pushState(null, '', 'dashboard.php?page=' + targetUrl.replace('.php', ''));
            }
        });
    });

    // Cargar contenido inicial basado en la URL si hay un parámetro 'page'
    const urlParams = new URLSearchParams(window.location.search);
    const initialPage = urlParams.get('page');
    if (initialPage) {
        loadContent(initialPage + '.php');
        // Actualizar clase activa al cargar la página inicialmente
        navLinks.forEach(link => {
            if (link.getAttribute('data-target') === initialPage + '.php') {
                link.parentElement.classList.add('active');
            } else {
                link.parentElement.classList.remove('active');
            }
        });
    } else {
        // Cargar el dashboard por defecto si no hay parámetro 'page'
        loadContent('dashboard_content.php'); // Asume que el contenido del dashboard principal estará en dashboard_content.php
        // Asegurarse de que el dashboard esté activo
        document.querySelector('.nav-item[data-section="dashboard"]').classList.add('active');
    }
});

function showSection(sectionName) {
    const mainContentArea = document.querySelector('.content-area');
    loadContent(sectionName + '.php', mainContentArea);

    // Opcional: actualizar el estado de la URL y la navegación
    history.pushState(null, '', 'dashboard.php?page=' + sectionName);
    document.querySelectorAll('.nav-link').forEach(link => {
        link.parentElement.classList.remove('active');
        if (link.getAttribute('data-target') === sectionName + '.php') {
            link.parentElement.classList.add('active');
        }
    });
}

function showConfirmationModal(title, text, onConfirm) {
    document.getElementById('confirmationModalTitle').textContent = title;
    document.getElementById('confirmationModalText').textContent = text;

    const confirmBtn = document.getElementById('confirmActionBtn');

    // Es una buena práctica clonar y reemplazar el botón para evitar acumular listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    newConfirmBtn.onclick = function () {
        onConfirm();
        closeModal('confirmationModal');
    };

    document.getElementById('confirmationModal').classList.remove('hidden');
}

function logout() {
    showConfirmationModal(
        'Cerrar Sesión',
        '¿Estás seguro de que quieres cerrar sesión?',
        () => {
            window.location.href = 'logout.php';
        }
    );
} 