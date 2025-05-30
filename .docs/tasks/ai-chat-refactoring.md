# AI Chat Implementation Tasks

1. **Implement non-streaming response mode**  
   - [ ] Add support for returning the full AI response at once instead of streaming it chunk-by-chunk.  
   - [ ] Make this mode configurable via backend config or API parameter.

2. **Add saving AI chat messages to the database**  
   - [ ] Design and create database schema for storing messages (user messages and AI responses).  
   - [ ] Persist all incoming and outgoing messages with relevant metadata (user_id, conversation_id, timestamps, roles).  
   - [ ] Ensure efficient retrieval for chat history.

3. **Add "no history" mode for users**  
   - [ ] Allow users to opt-out of saving chat history.  
   - [ ] Implement this as a user setting or API parameter that disables message persistence.

4. **Add temporary message IDs on frontend and handle them on backend**  
   - [ ] Generate temporary UUIDs for outgoing messages on frontend before sending to backend.  
   - [ ] Backend should accept messages without permanent IDs and return final IDs for updates.  
   - [ ] Implement logic to reconcile temporary IDs with server-generated IDs in the frontend.

5. **Add STOP button on frontend to cancel streaming**  
   - [ ] Allow users to manually stop streaming AI response.  
   - [ ] Handle cancellation gracefully on backend and frontend.

6. **Add loading state for streaming messages**  
   - [ ] Show a visible indicator (spinner, animation, etc.) on frontend while the AI response is still streaming.  
   - [ ] Hide loading state once the full message is received or streaming is stopped.


## Vue Floating Chat Ai Widget component DRAFT
```
const sendMessage = async () => {
  try {
    isSendingMessage.value = true

    // Wiadomość użytkownika
    messages.value.push({
      tempId: v4(),
      userId: authStore.userData?.id ?? '',
      // TODO: Refactor this 
      user: {
        id: authStore.userData?.id ?? '',
        name: `${authStore.userData?.firstName} ${authStore.userData?.lastName}`,
        email: authStore.userData?.email ?? '',
        createdAt: authStore.userData?.createdAt ?? new Date().toISOString(),
      },
      content: message.value,
      createdAt: new Date().toISOString(),
    })

    // AI wiadomość (pusta na start)
    startNewResponse()

    if (streamingEnabled) {
      await aiChatService.sendMessage(message.value)
    } else {
      // Return message with id, tempId, content 
      const response = await aiChatService.sendMessage(message.value) // musi zwracać treść AI
      currentAiMessage.value.content = response.content
      currentAiMessage.value.id = response.id
    }

    message.value = ''
  } catch (error) {
    handleErrorWithToast('Error sending message', error)
  } finally {
    isSendingMessage.value = false
  }
}
```

---

These tasks will improve the chat experience by adding flexible response modes, reliable message handling, user control over history, and better UI feedback during AI interactions.

---

# AI Chat Implementation Plan

## **1. Overview**

The AI Chat will be implemented as an extension of the existing Chat domain, treating AI as a special participant in conversations. This approach maintains consistency and reuses existing infrastructure while adding AI-specific features.

---

## **2. Goals**

- Support both streaming and non-streaming AI responses (configurable per request)
- Persist all chat messages (user and AI) using existing Chat domain models
- Allow users to opt out of chat history ("no history" mode)
- Handle temporary message IDs from the frontend and reconcile with permanent IDs
- Support cancellation of streaming responses
- Ensure robust error handling and clear API responses
- Follow project conventions (camelCase, domain structure, etc.)

---

## **3. Architecture & Components**

### **3.1. Domain Structure**

- `app/Domain/Chat/` (existing)
  - `Models/` – Reuse `ChatRoom`, `ChatMessage`, `ChatParticipant`
  - `Controllers/` – Add AI-specific endpoints
  - `Services/` – Add AI chat service
  - `DTOs/` – Add AI-specific DTOs
  - `Requests/` – Add AI chat request validation

### **3.2. Database Schema Updates**

**Add to existing `chat_messages` table:**
- `temp_id` (string, nullable) – For frontend message reconciliation
- `role` (enum: 'user', 'assistant') – To distinguish AI messages

**Add to existing `chat_rooms` table:**
- `type` (enum: 'user', 'ai') – To distinguish AI conversations

**AI User:**
- Create a system user record for AI in the `users` table

---

## **4. API Endpoints**

| Endpoint                | Method | Description                                      |
|-------------------------|--------|--------------------------------------------------|
| `/api/ai/chat`          | POST   | Send a message, receive AI response (stream/non-stream) |
| `/api/ai/cancel`        | POST   | Cancel an ongoing streaming response             |
| `/api/ai/history`       | GET    | Retrieve chat history for a conversation         |

**Request/Response Conventions:**
- Use camelCase for all fields
- Accept and return `tempId` for messages
- Support `stream` and `noHistory` flags in requests

---

## **5. Core Features & Steps**

### **5.1. Streaming & Non-Streaming Responses**
- Allow client to specify `stream: true|false` in the request
- Fallback to config default if not provided
- Ensure both modes return a consistent message object (with id, content, etc.)

### **5.2. Message Persistence**
- Use existing `ChatMessage` model for both user and AI messages
- Set appropriate `role` ('user' or 'assistant')
- Store `temp_id` for frontend reconciliation
- Skip persistence if `noHistory` is set

### **5.3. "No History" Mode**
- If `noHistory: true` is set, skip message persistence for both user and AI messages
- Still maintain conversation context for the current session

### **5.4. Temporary Message IDs**
- Accept `tempId` from frontend for user messages
- When saving, return both `tempId` and permanent `id` in the response for reconciliation

### **5.5. Cancellation**
- Provide `/api/ai/cancel` endpoint
- On cancel, mark conversation as cancelled (via cache or DB)
- Streaming logic should check for cancellation and stop if triggered

### **5.6. Error Handling**
- Log all exceptions
- Return meaningful error messages and HTTP status codes

---

## **6. Implementation Steps**

1. **Database Updates:**
   - Add `temp_id` and `role` columns to `chat_messages`
   - Add `type` column to `chat_rooms`
   - Create AI system user record

2. **Model Updates:**
   - Update `ChatMessage` model with new fillable fields
   - Add AI-specific scopes and methods to `ChatRoom`

3. **Service Layer:**
   - Create `AiChatService` to handle AI interactions
   - Integrate with existing `ChatRoom` and `ChatMessage` models
   - Implement streaming and non-streaming response handling

4. **API Layer:**
   - Add AI-specific endpoints to existing chat controllers or create new ones
   - Implement request validation and response formatting
   - Add cancellation endpoint

5. **Testing:**
   - Write feature tests for all new endpoints
   - Test integration with existing chat functionality
   - Test edge cases (cancellation, no history, temp IDs)

---

## **7. Example API Request/Response**

**Request:**
```json
{
  "message": "Hello, AI!",
  "history": [...],
  "stream": false,
  "noHistory": false,
  "tempId": "uuid-from-frontend"
}
```

**Response:**
```json
{
  "message": {
    "id": "permanent-uuid",
    "tempId": "uuid-from-frontend",
    "content": "Hello, how can I help you?",
    "role": "assistant",
    "createdAt": "2024-06-01T12:00:00Z"
  }
}
```

---

## **8. Testing**

- Write feature tests for all endpoints and modes
- Test integration with existing chat functionality
- Test edge cases: cancellation, no history, temp ID reconciliation, error scenarios

---

## **9. Future Enhancements**

- Add support for multi-turn conversations and context windows
- Implement user settings for default chat preferences
- Add analytics and monitoring for AI usage
- Consider adding AI-specific features to the chat UI

---

**End of Plan**