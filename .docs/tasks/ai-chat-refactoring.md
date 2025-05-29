# AI Chat Implementation Tasks

1. **Implement non-streaming response mode**  
   - Add support for returning the full AI response at once instead of streaming it chunk-by-chunk.  
   - Make this mode configurable via backend config or API parameter.

2. **Add saving AI chat messages to the database**  
   - Design and create database schema for storing messages (user messages and AI responses).  
   - Persist all incoming and outgoing messages with relevant metadata (user_id, conversation_id, timestamps, roles).  
   - Ensure efficient retrieval for chat history.

3. **Add "no history" mode for users**  
   - Allow users to opt-out of saving chat history.  
   - Implement this as a user setting or API parameter that disables message persistence.

4. **Add temporary message IDs on frontend and handle them on backend**  
   - Generate temporary UUIDs for outgoing messages on frontend before sending to backend.  
   - Backend should accept messages without permanent IDs and return final IDs for updates.  
   - Implement logic to reconcile temporary IDs with server-generated IDs in the frontend.

5. **Add STOP button on frontend to cancel streaming**  
   - Allow users to manually stop streaming AI response.  
   - Handle cancellation gracefully on backend and frontend.

6. **Add loading state for streaming messages**  
   - Show a visible indicator (spinner, animation, etc.) on frontend while the AI response is still streaming.  
   - Hide loading state once the full message is received or streaming is stopped.


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