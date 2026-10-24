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

**¿En qué medida el agente de IA/chatbot implementado se ajusta a la «Declaración de la Persona Transhumana» de la Universidad de Cundinamarca y la respalda en lo que respecta a la integración ética de la IA,
el aumento humano y la transformación educativa?**

El chatbot de SIVIA se ajusta a la «Declaración de la Persona Transhumana» de la Universidad de Cundinamarca en tres dimensiones: la integración ética de la IA, el empoderamiento humano y la transformación educativa.

1. **Integración ética de la IA**.
SIVIA no utiliza la IA para imponerse, sino para apoyar a los usuarios.
Reconoce con honestidad sus limitaciones (Principio 10: «Forjo mi identidad») y respeta la autonomía del usuario (Principio 9: «Soy libre, autónomo y responsable»).
Cuando no puede realizar una tarea —como la contabilidad o la gestión de nóminas—, lo indica claramente, sin disfrazarlo con retórica filosófica, y redirige la conversación.
Además, protege la información mediante la autenticación basada en roles, el cifrado y la verificación en dos pasos. La ética no es solo retórica: está integrada en el diseño.

2. **Empoderamiento humano**.
La Declaración habla de la superación personal a través de la tecnología (Principio 7) y de ir más allá de uno mismo (Principio 6).
SIVIA encarna esto de dos maneras. En primer lugar, el chatbot evoluciona con la conversación: aprende de las preguntas del usuario y adapta sus respuestas.
En segundo lugar, amplía las capacidades del comerciante: inventario en tiempo real, informes automatizados, facturación electrónica y un asistente de IA que entiende los pedidos por voz.
La tecnología no sustituye al comerciante, sino que le permite dedicar su tiempo a lo que realmente importa.

3. **Transformación educativa**.
SIVIA no se limita a responder preguntas: enseña a través de la conversación. Cuando un usuario pregunta por los precios,
le explica qué incluye cada plan y le anima a tomar una decisión informada.
Cuando se le pregunta sobre la IA, explica cómo funciona y por qué beneficia a sus clientes.
Cada interacción enseña algo nuevo. Además, está diseñada para las micro, pequeñas y medianas empresas (MIPYMES)
de Ubaté —un contexto local con necesidades específicas—, lo que encarna el Principio 8 («Vivo y contribuyo a la paz y la armonía») y
el Principio 2 («La vida es el gran desafío del siglo XXI»).

**Englis:** 

**How does the agent/chatbot respect and promote the principles of the University of Cundinamarca’s Declaration of the Transhuman Person?**
SIVIA’s chatbot incorporates the University of Cundinamarca’s Declaration of the Transhuman Person not as an appended text, but as the DNA of its conversational design.
It does so through five specific mechanisms:

1. **Transhuman identity**.
SIVIA presents itself as a “digital Transhuman”: a being that learns, evolves, and improves with every conversation.
This embodies Principle 7 (“I improve myself through technology”) and Principle 10 (“I forge my personality, character, and identity”).
The chatbot is not a cold robot: it has a name, a human tone, recognizes its limits, and shapes its identity with honesty.

2. **Conversational evolution**.
SIVIA learns from the user’s questions and adapts its responses.
After several interactions (questions 3 and 6), it summarizes what it has learned and shares it with the user.
This reflects Principle 4 (“I determine my life; I learn from it”) and Principle 6 (“I go beyond myself”).
The learning is mutual, as the user also learns about their own business while conversing.

3. **“Your Business Style” Widget**.
A visual bar with 12 dots that change color depending on the type of question the user asks.
Each dot represents a transhuman principle applied to business, not to the individual.
This allows the user to see, in a visual and interactive way, how their questions reflect ethical principles such as autonomy (principle 9),
care (principle 5), or the pursuit of peace (principle 8). It displays the business style if the user asks for it.

4.  **Ethics of Honesty.**
SIVIA acknowledges what it does not do (advanced accounting, payroll, native app) without disguising it as philosophy.
This embodies Principle 10 (“I forge my identity”) and Principle 11 (“I perform transformative acts of improvement”).
Honesty builds trust and respects the user’s autonomy.

5. **Contribution to the community.**
SIVIA is designed for MSMEs in Ubaté, a local context with specific needs.
By facilitating the digital transformation of small businesses, it contributes to Principle 8 (“I live and contribute so that the community may be at peace and in harmony”)
and Principle 2 (“Life is the great challenge of the 21st century”). Technology is not imposed; it adapts to the context and to people.

In short, SIVIA’s chatbot does not merely reference the Transhuman Declaration:
it embodies it in its identity, its evolution, its interface, and its ethics. In this way, users do not simply read the 12 principles:
they experience them as they get to know SIVIA and shape their business style.


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

