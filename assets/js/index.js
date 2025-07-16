let ultimaMatricula = null; // la ultima matricula para evitar repeticiones
let escaneando = true; // para detener el escaneo mientras se procesa una matrícula
let intentosConsecutivosSinQR = 0; // contador para evitar errores cuando no hay QR
const MAX_INTENTOS_SIN_QR = 30; // máximo de intentos consecutivos sin QR antes de pausar

// captura de video
const video = document.createElement("video"); // crea un elemento de video donde se ve la camara
const canvasElement = document.createElement("canvas");
const canvas = canvasElement.getContext("2d"); // para dibujar la imagen del video y escanearla

// agregamos los elementos al DOM
const readerContainer = document.getElementById("reader");
readerContainer.appendChild(video); // agregamos el video al div con id "reader"
canvasElement.style.display = "none"; 
document.body.appendChild(canvasElement); // agregamos el canvas al body para poder usarlo

// Elementos de la interfaz
const resultElement = document.getElementById("result");
const cameraStatus = document.querySelector(".camera-status");

// Función para actualizar el estado visual del resultado
function updateResultState(state, message) {
    resultElement.className = state;
    resultElement.textContent = message;
}

// pedimos acesso a la camara
navigator.mediaDevices.getUserMedia({ 
    video: { 
        facingMode: "environment",
        width: { ideal: 1280 },
        height: { ideal: 720 }
    } 
})
.then(function (stream) {
    video.srcObject = stream;
    video.setAttribute("playsinline", true); // necesario para iOS
    video.style.width = "100%";
    video.style.height = "auto";
    video.play(); // iniciamos el video
    
    // Indicar que la cámara está activa
    if (cameraStatus) {
        cameraStatus.style.background = "var(--success-green)";
    }
    
    requestAnimationFrame(scanQRCode); // llamamos a la funcion de escaneo
})
.catch(function(err) {
    console.error("Error al acceder a la cámara:", err);
    updateResultState("error", "❌ Error al acceder a la cámara");
    
    // Indicar error en la cámara
    if (cameraStatus) {
        cameraStatus.style.background = "var(--error-red)";
    }
});

// funcion de escaneo de QR (se ejecuta en bucle)
function scanQRCode() {
    if (!escaneando) {
        requestAnimationFrame(scanQRCode);
        return;
    }

    if (video.readyState === video.HAVE_ENOUGH_DATA) { // verificamos si el video muestra imagen
        try {
            canvasElement.width = video.videoWidth;
            canvasElement.height = video.videoHeight;
            canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height); // tomamos una captura del video y la dibujamos en el canvas

            // obtenemos los datos de pixeles del canvas
            const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);

            // analizamos los px si hay un QR (si hay lo decodifica y lo vuelve texto) Ejemplo: code.data = "ALUMNO:Z011"
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });

            if (code && code.data) { // si se detecta un QR válido
                intentosConsecutivosSinQR = 0; // reiniciamos el contador
                onScanSuccess(code.data);
            } else {
                // Incrementamos el contador de intentos sin QR
                intentosConsecutivosSinQR++;
                
                // Si hay muchos intentos consecutivos sin QR, reducimos la frecuencia de escaneo
                if (intentosConsecutivosSinQR > MAX_INTENTOS_SIN_QR) {
                    setTimeout(() => {
                        requestAnimationFrame(scanQRCode);
                    }, 100); // pausa de 100ms para reducir carga del procesador
                    return;
                }
            }
        } catch (error) {
            console.error("Error durante el escaneo:", error);
            // No mostramos el error al usuario para evitar confusión
        }
    }
    
    requestAnimationFrame(scanQRCode); // vuelve a llamar a la funcion para seguir escaneando
}

// funcion si detecta un qr
function onScanSuccess(decodedText) {
    if (!escaneando || !decodedText) return;

    // verifica si el texto decodificado comienza con "ALUMNO:" ose un formato valido
    if (decodedText.startsWith("ALUMNO:")) {
        const matricula = decodedText.split(":")[1]?.trim(); // sacamos la matricula del qr
        
        if (!matricula) {
            updateResultState("error", "❌ Código QR malformado");
            mostrarPantallaso("pantallaso-error");
            reproducirAudio("audio-error");
            return;
        }

        if (matricula === ultimaMatricula) return;

        ultimaMatricula = matricula;
        escaneando = false; // pausa el escaneo temporalmente

        updateResultState("processing", "⏳ Procesando matrícula " + matricula + "...");

        // peticion http al registrar asistencia 
        fetch("../procesadores/registrar_asistencia.php", {
            method: "POST", // metodo de envio POST
            headers: { "Content-Type": "application/x-www-form-urlencoded" }, // le decimos al servidor el tipo de datos que estamos enviando que es texto codificado (como si fuera formulario html)
            body: "matricula=" + encodeURIComponent(matricula) // la parte del contenido que se va enviar (matricula= "seria el nombre del campo") (encdodeURIComponent es para codificar la matricula en caso de que tenga caracteres especiales como espacios o acentos) matricula= "z0 11" -> "z0%2011"
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.registrado) {
                updateResultState("success", "✅ Asistencia registrada para " + matricula);
                mostrarPantallaso("pantallaso-correcto"); // llama funcion de mostrar pantallaso
                reproducirAudio("audio-exito");  // reproduce el audio de estado
                
                // Efecto de vibración si está disponible
                if (navigator.vibrate) {
                    navigator.vibrate([100, 50, 100]);
                }
            } else if (data.error === "Ya tiene asistencia registrada hoy") {
                updateResultState("error", "⚠️ " + data.error);
                mostrarPantallaso("pantallaso-ya-registrado");
                reproducirAudio("audio-ya-registrado");
                
                if (navigator.vibrate) {
                    navigator.vibrate([200, 100, 200]);
                }
            } else {
                updateResultState("error", "❌ " + (data.error || "Error desconocido"));
                mostrarPantallaso("pantallaso-error");
                reproducirAudio("audio-error");
                
                if (navigator.vibrate) {
                    navigator.vibrate([300]);
                }
            }

            // reiniciamos el escaneo
            setTimeout(() => {
                escaneando = true; // volvemos a activar el escaneo
                ultimaMatricula = null;
                intentosConsecutivosSinQR = 0; // reiniciamos el contador
                updateResultState("", "Esperando escaneo...");
            }, 3000);
        })
        .catch(error => { // si hay un error en la peticion fetch
            console.error("Error al registrar:", error);
            updateResultState("error", "❌ Error de conexión");
            mostrarPantallaso("pantallaso-error");
            reproducirAudio("audio-error");
            
            if (navigator.vibrate) {
                navigator.vibrate([300]);
            }
            
            // reiniciamos el escaneo después del error
            setTimeout(() => {
                escaneando = true;
                ultimaMatricula = null;
                intentosConsecutivosSinQR = 0;
                updateResultState("", "Esperando escaneo...");
            }, 3000);
        });

    } else { // si el texto decodificado no es valido
        updateResultState("error", "❌ Código no válido");
        mostrarPantallaso("pantallaso-error");
        reproducirAudio("audio-error");
        
        if (navigator.vibrate) {
            navigator.vibrate([300]);
        }
        
        // Breve pausa antes de continuar escaneando
        setTimeout(() => {
            updateResultState("", "Esperando escaneo...");
        }, 2000);
    }
}

// funcion para mostrar un pantallaso temporal con animación mejorada
function mostrarPantallaso(idDiv) {
    const div = document.getElementById(idDiv);
    if (div) {
        div.classList.add("show");
        div.style.display = "block";
        
        // Animación de entrada
        setTimeout(() => {
            div.style.opacity = "0.85";
        }, 10);
        
        setTimeout(() => {
            // Animación de salida
            div.style.opacity = "0";
            setTimeout(() => {
                div.style.display = "none";
                div.classList.remove("show");
            }, 300);
        }, 2000);
    }
}

// Función mejorada para reproducir audio
function reproducirAudio(audioId) {
    const audio = document.getElementById(audioId);
    if (audio) {
        audio.currentTime = 0; // Reiniciar el audio
        audio.play().catch(error => {
            console.log("No se pudo reproducir el audio:", error);
        });
    }
}

// Mejorar la reproducción de audio con manejo de errores
document.getElementById("audio-exito").addEventListener('error', () => {
    console.log("Error al cargar audio de éxito");
});

document.getElementById("audio-ya-registrado").addEventListener('error', () => {
    console.log("Error al cargar audio de ya registrado");
});

document.getElementById("audio-error").addEventListener('error', () => {
    console.log("Error al cargar audio de error");
});

// Función para manejar la visibilidad de la página
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Pausar el escaneo cuando la página no está visible
        escaneando = false;
    } else {
        // Reanudar el escaneo cuando la página vuelve a estar visible
        setTimeout(() => {
            escaneando = true;
            ultimaMatricula = null;
            intentosConsecutivosSinQR = 0;
            updateResultState("", "Esperando escaneo...");
        }, 500);
    }
});

// Prevenir el zoom en dispositivos móviles
document.addEventListener('touchstart', function(event) {
    if (event.touches.length > 1) {
        event.preventDefault();
    }
});

let lastTouchEnd = 0;
document.addEventListener('touchend', function(event) {
    const now = (new Date()).getTime();
    if (now - lastTouchEnd <= 300) {
        event.preventDefault();
    }
    lastTouchEnd = now;
}, false);

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Añadir clases de animación
    const mainContainer = document.querySelector('.main-container');
    if (mainContainer) {
        mainContainer.style.animation = 'fadeInUp 0.8s ease-out';
    }
    
    // Configurar el estado inicial
    updateResultState("", "Inicializando cámara...");
});

