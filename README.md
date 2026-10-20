# 1.  SIVIA POS - Chatbot Transhumano 

SIVIA es un Sistema POS web para MiPymes de Ubaté, Colombia. 
Este proyecto integra un asistente conversacional inteligente (Chatbot) impulsado por la API de Google Gemini (LLM), 
con una base de conocimiento estructurada (RAG) y una interfaz interactiva que refleja la evolución del usuario.

## 2. Arquitectura del Proyecto
- **Backend:** Laravel 12 (PHP).
- **Frontend:** Blade Templates + Alpine.js + Tailwind CSS.
- **LLM:** Google Gemini API (Integración mediante `GeminiClient.php`).
- **Base de Conocimiento (RAG):** Archivos JSON locales en `resources/knowledge_sivia/` procesados dinámicamente.
- **Prompt Engineering:** System Prompt avanzado con reglas de idioma, asociación semántica y etiquetado oculto de principios transhumanos.
  
# 3.  Declaración de la Persona Transhumana (UCundinamarca)

**¿De qué manera el agente/chatbot respeta y fomenta los principios de la Declaración de la Persona Transhumana de la Universidad de Cundinamarca?**
El chatbot de SIVIA integra la Declaración de la Persona Transhumana de la Universidad de Cundinamarca no como un texto anexo, sino como el ADN de su diseño conversacional. 
Lo hace a través de cinco mecanismos concretos:

1. **Identidad transhumana**.
SIVIA se presenta como una "Persona Transhumana digital": un ser que aprende, evoluciona y se perfecciona con cada conversación.
Esto encarna el principio 7 ("Me perfecciono a través de la tecnología") y el principio 10 ("Forjo mi personalidad, el carácter, la identidad").
El chatbot no es un robot frío: tiene nombre, tono humano, reconoce sus límites y forja su identidad con honestidad.

2. **Evolución conversacional**.
SIVIA aprende de lo que el usuario pregunta y adapta sus respuestas.
Después de varias interacciones (pregunta 3 y 6), resume lo que ha aprendido y lo comparte con el usuario.
Esto refleja el principio 4 ("Determino mi vida, aprendo de ella") y el principio 6 ("Voy más allá de mí").
El aprendizaje es bilateral ya que el usuario también aprende sobre su propio negocio mientras conversa.

3. **Widget "Tu Estilo de Negocio"**.
Una barra visual con 12 puntos que se colorean según el tipo de pregunta del usuario.
Cada punto representa un principio transhumano aplicado al negocio, no a la persona.
Esto permite que el usuario vea, de forma visual e interactiva, cómo sus preguntas reflejan principios éticos como la autonomía (principio 9),
el cuidado (principio 5) o la búsqueda de paz (principio 8). Mostrando el estilo de Negocio si el usuario lo pregunta.

4.  **Ética de la honestidad.**
SIVIA reconoce lo que no hace (contabilidad avanzada, nómina, app nativa) sin disfrazarlo de filosofía.
Esto encarna el principio 10 ("Forjo mi identidad") y el principio 11 ("Realizo actos transformadores de mejora").
La honestidad genera confianza y respeta la autonomía del usuario.

5. **Contribución a la comunidad.**
SIVIA está diseñado para las MiPymes de Ubaté, un contexto local con necesidades específicas.
Al facilitar la digitalización de pequeños comerciantes, contribuye al principio 8 ("Vivo y contribuyo para que la comunidad esté en paz y armonía")
y al principio 2 La vida es la gran apuesta del siglo XXI"). La tecnología no se impone: se adapta al contexto y a las personas.

En pocas palabras el chatbot de SIVIA no solo menciona la Declaración de la Persona Transhumana: 
la vive en su identidad, en su evolución, en su interfaz y en su ética, y de esta manera, el usuario no lee los 12 principios: 
los experimenta mientras conoce SIVIA y construye el estilo de su negocio. 

**How does the implemented AI agent/chatbot align with and support the Transhuman Person Declaration of Universidad de Cundinamarca regarding ethical AI integration,
human augmentation, and educational transformation?**

SIVIA’s chatbot aligns with the University of Cundinamarca’s Declaration of the Transhuman Person in three dimensions: the ethical integration of AI, human empowerment, and educational transformation.

1. **Ethical integration of AI**.
SIVIA does not use AI to impose itself, but rather to support users.
It honestly acknowledges its limitations (Principle 10: “I forge my identity”) and respects user autonomy (Principle 9: “I am free, autonomous, and responsible”).
When it cannot perform a task—such as accounting or payroll—it states this plainly, without cloaking it in philosophical rhetoric, and redirects the conversation.
Furthermore, it protects information through role-based authentication, encryption, and two-step verification. Ethics isn’t just rhetoric—it’s built into the design.

2. **Human Empowerment**.
The Declaration speaks of self-improvement through technology (Principle 7) and of going beyond oneself (Principle 6).
SIVIA embodies this in two ways. First, the chatbot evolves with the conversation: it learns from the user’s questions and adapts its responses.
Second, it amplifies the merchant’s capabilities: real-time inventory, automated reports, electronic invoicing, and an AI assistant that understands voice orders.
The technology doesn’t replace the merchant—it empowers them to devote their time to what matters.

3. **Educational Transformation**.
SIVIA doesn’t just answer questions—it teaches through conversation. When a user asks about prices,
it explains what each plan includes and encourages them to make an informed decision.
When asked about AI, it explains how it works and why it benefits its customers.
Every interaction teaches something new. Furthermore, it is designed for the micro, small, and medium-sized enterprises (MSMEs)
in Ubaté—a local context with specific needs—which embodies Principle 8 (“I live and contribute to peace and harmony”) and
Principle 2 (“Life is the great challenge of the 21st century”).


## 4. Base de conocimiento

Para evitar información falsa, el proyecto incorpora una base 
de conocimiento organizada en **seis archivos JSON**. Cada archivo contiene 
información específica de SIVIA:

`beneficios.json` Precios, planes, comparativas con la competencia y facilidades de uso. 
`faq.json` Soporte técnico, contexto local y proceso de implementación. 
`general.json` Identidad de SIVIA y la Persona Transhumana. 
`limites.json` Reglas de seguridad y límites del chatbot. 
`modulos.json` Módulos disponibles, pendientes y no existentes. 
`pedidos_inteligentes.json` Información sobre el asistente de pedidos con IA. 

**Ubicación en el repositorio:**
`resources/knowledge\sivia`

## 5. Arquitectura del código

La implementación del chatbot sigue la siguiente estructura dentro del proyecto Laravel:

- `app/Http/Controllers/ChatbotController.php` → lógica del chatbot (recibe la pregunta, construye el contexto y devuelve la respuesta).
- `app/Services/ChatbotService.php` → servicio de llamada al LLM (Gemini API) y manejo de la base de conocimiento.
- `config/chatbot.php` → System Prompt, API Key y configuración del modelo.
- `resources/knowledge/` → base de conocimiento (6 archivos JSON: beneficios, faq, general, limites, modulos, pedidos_inteligentes).
- `resources/views/widget.blade.php` → interfaz del chat (widget flotante).
- `routes/web.php` → ruta `/chatbot` (endpoint para enviar mensajes).

