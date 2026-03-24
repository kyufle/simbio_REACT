// ==========================
// VARIABLES GLOBALES
// ==========================

const messagesContainer = document.getElementById('messagesContainer');
const messageForm = document.getElementById('messageForm');
const messageInput = document.getElementById('messageInput');

let lastMessageId = 0;
let pollingInterval = null;

// ==========================
// CARGAR MENSAJES
// ==========================

async function loadMessages() {
    try {
        const params = new URLSearchParams({
            action: 'get',
            user_id: otherUserId,
            last_message_id: lastMessageId
        });

        const response = await fetch(`api_chat.php?${params}`);
        const data = await response.json();

        if (data.success && data.messages.length > 0) {
            data.messages.forEach(message => {
                renderMessage(message);
                lastMessageId = message.message_id;
            });

            scrollToBottom();
        }
    } catch (error) {
        console.error('Error cargando mensajes', error);
    }
}

// ==========================
// ENVIAR MENSAJE
// ==========================

async function sendMessage(text) {
    try {
        const response = await fetch('api_chat.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                text: text,
                to_user_id: otherUserId
            })
        });

        const data = await response.json();

        if (!data.success) {
            alert('Error al enviar el mensaje');
            return;
        }

        // 👇 Render inmediato
        renderMessage({
            message_id: data.message_id,
            text: data.text,
            sent_at: data.sent_at,
            user_from_id: currentUserId
        });

        // 👇 Actualizamos lastMessageId
        lastMessageId = data.message_id;

        scrollToBottom();

    } catch (error) {
        console.error('Error enviando mensaje', error);
    }
}


// ==========================
// RENDERIZAR MENSAJE
// ==========================

function renderMessage(message) {
    // Evitar duplicados
    if (document.getElementById('msg_' + message.message_id)) return;

    const div = document.createElement('div');
    div.id = 'msg_' + message.message_id;

    const isMine = message.user_from_id == currentUserId;
    div.className = 'message-bubble ' + (isMine ? 'sent' : 'received');

    div.innerHTML = `
        <div class="message-content">${message.text}</div>
        <div class="message-time">${formatTime(message.sent_at)}</div>
    `;

    messagesContainer.appendChild(div);
}

// ==========================
// FORMATEAR HORA
// ==========================

function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('ca-ES', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// ==========================
// SCROLL
// ==========================

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// ==========================
// EVENTOS
// ==========================

messageForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const text = messageInput.value.trim();
    if (!text) return;

    sendMessage(text);
    messageInput.value = '';
});

// ==========================
// INICIAR CHAT
// ==========================

document.addEventListener('DOMContentLoaded', function () {
    loadMessages();

    pollingInterval = setInterval(loadMessages, 2000);
});

// ==========================
// LIMPIAR AL SALIR
// ==========================

window.addEventListener('beforeunload', function () {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});