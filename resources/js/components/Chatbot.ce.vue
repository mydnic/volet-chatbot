<template>
    <div class="volet-chatbot">
        <div ref="scrollEl" class="vc-messages">
            <div
                v-for="(message, index) in messages"
                :key="index"
                :class="['vc-message', `vc-message-${message.role}`]"
            >
                {{ message.content }}
            </div>
            <div v-if="error" class="vc-error">{{ error }}</div>
        </div>

        <form class="vc-form" @submit.prevent="sendMessage">
            <textarea
                v-model="input"
                class="vc-input"
                rows="1"
                :placeholder="labels?.inputPlaceholder || 'Type your message...'"
                :disabled="sending"
                @keydown.enter.exact.prevent="sendMessage"
            ></textarea>
            <button type="submit" class="vc-send" :disabled="sending || !input.trim()">
                {{ labels?.send || 'Send' }}
            </button>
        </form>
    </div>
</template>

<script>
export default {
    name: 'Chatbot',
    props: {
        routes: {
            type: Object,
            required: true,
        },
        labels: {
            type: Object,
            default: () => ({}),
        },
        csrfToken: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            messages: [],
            input: '',
            sending: false,
            error: null,
            conversationId: null,
        }
    },
    methods: {
        async sendMessage() {
            const content = this.input.trim()
            if (!content || this.sending) return

            this.error = null
            this.messages.push({ role: 'user', content })
            this.input = ''
            this.sending = true

            const assistantMessage = { role: 'assistant', content: '' }
            this.messages.push(assistantMessage)

            try {
                const response = await fetch(this.routes.send, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'text/event-stream',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        message: content,
                        conversation_id: this.conversationId,
                    }),
                })

                if (!response.ok || !response.body) {
                    throw new Error(this.labels?.errorMessage || 'Something went wrong. Please try again.')
                }

                await this.consumeStream(response.body, assistantMessage)
            } catch (err) {
                this.error = err.message || this.labels?.errorMessage || 'Something went wrong. Please try again.'
                this.messages.pop()
            } finally {
                this.sending = false
                this.scrollToBottom()
            }
        },

        async consumeStream(body, assistantMessage) {
            const reader = body.getReader()
            const decoder = new TextDecoder()
            let buffer = ''

            while (true) {
                const { value, done } = await reader.read()
                if (done) break

                buffer += decoder.decode(value, { stream: true })
                const frames = buffer.split('\n\n')
                buffer = frames.pop()

                for (const frame of frames) {
                    const raw = frame.replace(/^data: /, '').trim()
                    if (!raw || raw === '[DONE]') continue

                    const event = JSON.parse(raw)

                    if (event.type === 'text_delta') {
                        assistantMessage.content += event.delta
                        this.scrollToBottom()
                    } else if (event.type === 'conversation') {
                        this.conversationId = event.conversation_id
                    } else if (event.type === 'error') {
                        this.error = event.message
                    }
                }
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.scrollEl
                if (el) el.scrollTop = el.scrollHeight
            })
        },
    },
}
</script>

<style>
@import '../../css/volet-chatbot.css';
</style>
