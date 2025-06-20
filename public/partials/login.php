<!-- Login Screen -->
<div id="loginScreen" class="login-container">
    <div class="login-box">
        <div class="login-header">
            <i class="fas fa-tools"></i>
            <h1>FerreGest360</h1>
            <p>Sistema de Gestión para Ferreterías</p>
        </div>

        <form id="loginForm" class="login-form">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-container">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" required placeholder="tu@email.com">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-container">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required placeholder="Tu contraseña">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Iniciar Sesión
            </button>
        </form>
    </div>
</div>