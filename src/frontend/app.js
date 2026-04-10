const API_BASE = window.APP_CONFIG?.API_BASE || "http://localhost:8000/api.php";

const loginSection = document.getElementById("loginSection");
const appSection = document.getElementById("appSection");
const loginForm = document.getElementById("loginForm");
const alumnoForm = document.getElementById("alumnoForm");
const logoutButton = document.getElementById("logoutButton");
const exportButton = document.getElementById("exportButton");
const cancelEditButton = document.getElementById("cancelEditButton");
const saveButton = document.getElementById("saveButton");
const welcomeText = document.getElementById("welcomeText");
const alumnosTbody = document.getElementById("alumnosTbody");
const formTitle = document.getElementById("formTitle");
const alumnoIdInput = document.getElementById("alumnoId");
const loginMessage = document.getElementById("loginMessage");
const formMessage = document.getElementById("formMessage");
const tableMessage = document.getElementById("tableMessage");

/**
 * Escapa texto para insertarlo de forma segura en HTML.
 */
function escapeHtml(value) {
    const text = String(value ?? "");
    return text
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll("\"", "&quot;")
        .replaceAll("'", "&#039;");
}

/**
 * Muestra mensajes de estado y aplica estilo por tipo.
 */
function showMessage(element, message, type = "") {
    element.textContent = message;
    element.className = "message";
    if (type) {
        element.classList.add(type);
    }
}

/**
 * Realiza una llamada a la API manteniendo cookies de sesion.
 */
async function apiRequest(action, { method = "GET", params = {}, body = null } = {}) {
    const url = new URL(API_BASE);
    url.searchParams.set("action", action);

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== "") {
            url.searchParams.set(key, String(value));
        }
    });

    const options = {
        method,
        credentials: "include",
        headers: {}
    };

    if (body !== null) {
        options.headers["Content-Type"] = "application/json";
        options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({ message: "Respuesta invalida del servidor." }));

    if (!response.ok) {
        const error = new Error(data.message || "Ocurrio un error en la API.");
        error.status = response.status;
        error.payload = data;
        throw error;
    }

    return data;
}

/**
 * Alterna la UI para mostrar formulario de login.
 */
function showLogin() {
    loginSection.classList.remove("hidden");
    appSection.classList.add("hidden");
    showMessage(tableMessage, "", "");
    alumnosTbody.innerHTML = "";
    resetAlumnoForm();
}

/**
 * Alterna la UI para mostrar el panel principal.
 */
function showApp(user) {
    loginSection.classList.add("hidden");
    appSection.classList.remove("hidden");
    welcomeText.textContent = `Sesion activa: ${user.display_name} (${user.username})`;
}

/**
 * Limpia el formulario de alumnos y lo deja en modo "crear".
 */
function resetAlumnoForm() {
    alumnoForm.reset();
    alumnoIdInput.value = "";
    formTitle.textContent = "Registrar alumno";
    saveButton.textContent = "Registrar alumno";
    cancelEditButton.classList.add("hidden");
}

/**
 * Convierte los campos del formulario en payload para la API.
 */
function getAlumnoPayload() {
    return {
        primer_apellido: document.getElementById("primer_apellido").value.trim(),
        segundo_apellido: document.getElementById("segundo_apellido").value.trim(),
        nombres: document.getElementById("nombres").value.trim(),
        no_cuenta: document.getElementById("no_cuenta").value.trim(),
        correo: document.getElementById("correo").value.trim(),
        contacto: document.getElementById("contacto").value.trim()
    };
}

/**
 * Rellena formulario para editar un alumno existente.
 */
function fillAlumnoForm(alumno) {
    alumnoIdInput.value = alumno.id_alumno;
    document.getElementById("primer_apellido").value = alumno.primer_apellido || "";
    document.getElementById("segundo_apellido").value = alumno.segundo_apellido || "";
    document.getElementById("nombres").value = alumno.nombres || "";
    document.getElementById("no_cuenta").value = alumno.no_cuenta || "";
    document.getElementById("correo").value = alumno.correo || "";
    document.getElementById("contacto").value = alumno.contacto || "";
    formTitle.textContent = `Editar alumno #${alumno.id_alumno}`;
    saveButton.textContent = "Actualizar alumno";
    cancelEditButton.classList.remove("hidden");
    showMessage(formMessage, "", "");
}

/**
 * Dibuja la tabla con los alumnos obtenidos del backend.
 */
function renderAlumnos(alumnos) {
    alumnosTbody.innerHTML = "";

    if (alumnos.length === 0) {
        const emptyRow = document.createElement("tr");
        emptyRow.innerHTML = `<td colspan="8">No hay alumnos registrados.</td>`;
        alumnosTbody.appendChild(emptyRow);
        return;
    }

    alumnos.forEach((alumno) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${escapeHtml(alumno.id_alumno)}</td>
            <td>${escapeHtml(alumno.primer_apellido)}</td>
            <td>${escapeHtml(alumno.segundo_apellido)}</td>
            <td>${escapeHtml(alumno.nombres)}</td>
            <td>${escapeHtml(alumno.no_cuenta)}</td>
            <td>${escapeHtml(alumno.correo)}</td>
            <td>${escapeHtml(alumno.contacto)}</td>
            <td class="text-nowrap">
                <div class="d-inline-flex gap-1">
                    <button type="button" class="btn btn-sm btn-warning" data-action="edit">Modificar</button>
                    <button type="button" class="btn btn-sm btn-danger" data-action="delete">Eliminar</button>
                </div>
            </td>
        `;

        tr.querySelector('[data-action="edit"]').addEventListener("click", () => fillAlumnoForm(alumno));
        tr.querySelector('[data-action="delete"]').addEventListener("click", () => deleteAlumno(alumno.id_alumno));

        alumnosTbody.appendChild(tr);
    });
}

/**
 * Carga alumnos para refrescar tabla luego de cualquier cambio.
 */
async function loadAlumnos() {
    const response = await apiRequest("alumnos");
    renderAlumnos(response.data || []);
}

/**
 * Elimina un alumno por id y luego actualiza tabla.
 */
async function deleteAlumno(id) {
    const confirmed = window.confirm("Seguro que deseas eliminar este alumno?");
    if (!confirmed) {
        return;
    }

    try {
        await apiRequest("alumnos_delete", { method: "DELETE", params: { id } });
        showMessage(tableMessage, "Alumno eliminado correctamente.", "success");
        await loadAlumnos();
    } catch (error) {
        showMessage(tableMessage, error.message, "error");
    }
}

/**
 * Arranca la aplicacion verificando si ya existe sesion activa.
 */
async function bootstrap() {
    try {
        const meResponse = await apiRequest("me");
        showApp(meResponse.user);
        await loadAlumnos();
    } catch (_error) {
        showLogin();
    }
}

loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    showMessage(loginMessage, "", "");

    const formData = new FormData(loginForm);
    const username = String(formData.get("username") || "").trim();
    const password = String(formData.get("password") || "");

    try {
        const response = await apiRequest("login", {
            method: "POST",
            body: { username, password }
        });
        showMessage(loginMessage, response.message, "success");
        showApp(response.user);
        await loadAlumnos();
    } catch (error) {
        showMessage(loginMessage, error.message, "error");
    }
});

exportButton.addEventListener("click", () => {
    const exportUrl = new URL(API_BASE);
    exportUrl.searchParams.set("action", "export");
    window.open(exportUrl.toString(), "_blank");
});

logoutButton.addEventListener("click", async () => {
    try {
        await apiRequest("logout", { method: "POST" });
    } finally {
        showLogin();
        showMessage(loginMessage, "Sesion cerrada.", "success");
    }
});

alumnoForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    showMessage(formMessage, "", "");

    const payload = getAlumnoPayload();
    const id = alumnoIdInput.value.trim();
    const action = id ? "alumnos_update" : "alumnos_create";
    const method = id ? "PUT" : "POST";
    const params = id ? { id } : {};

    try {
        const response = await apiRequest(action, { method, params, body: payload });
        showMessage(formMessage, response.message, "success");
        resetAlumnoForm();
        await loadAlumnos();
    } catch (error) {
        const fieldErrors = error.payload?.errors;
        if (fieldErrors && typeof fieldErrors === "object") {
            const details = Object.values(fieldErrors).join(" | ");
            showMessage(formMessage, details || error.message, "error");
            return;
        }

        showMessage(formMessage, error.message, "error");
    }
});

cancelEditButton.addEventListener("click", () => {
    resetAlumnoForm();
    showMessage(formMessage, "Edicion cancelada.", "success");
});

bootstrap();
