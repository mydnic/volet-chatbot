<template>
    <div class="volet-chatbot">
        <div ref="scrollEl" class="vc-messages">
            <div
                v-for="(message, index) in messages"
                :key="index"
                :class="['vc-message', `vc-message-${message.role}`]"
            >
                <div class="vc-bubble">
                    <span v-if="message.pending" class="vc-typing">
                        <span></span><span></span><span></span>
                    </span>
                    <template v-else>{{ message.content }}</template>
                </div>
            </div>
            <div v-if="error" class="vc-error">{{ error }}</div>
        </div>

        <form class="vc-form" @submit.prevent="sendMessage">
            <div class="vc-input-wrap">
                <textarea
                    ref="inputEl"
                    v-model="input"
                    class="vc-input"
                    rows="1"
                    :placeholder="labels?.inputPlaceholder || 'Message...'"
                    :disabled="sending"
                    @keydown.enter.exact.prevent="sendMessage"
                    @input="autoGrow"
                ></textarea>
                <button type="submit" class="vc-send" :disabled="sending || !input.trim()" :aria-label="labels?.send || 'Send'">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 12L20 4L13 20L11 13L4 12Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
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
        // Single word, not "csrfToken": Volet's feature host sets string
        // config values via el.setAttribute(key, value), which the DOM
        // lowercases. Vue's custom-element attributeChangedCallback camelizes
        // it back, so a multi-word camelCase name never round-trips and the
        // prop stays undefined.
        token: {
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
            this.$nextTick(() => this.autoGrow())
            this.sending = true

            this.messages.push({ role: 'assistant', content: '', pending: true })
            const assistantIndex = this.messages.length - 1
            this.scrollToBottom()

            try {
                const response = await fetch(this.routes.send, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'text/event-stream',
                        'X-CSRF-TOKEN': this.token,
                    },
                    body: JSON.stringify({
                        message: content,
                        conversation_id: this.conversationId,
                    }),
                })

                if (!response.ok || !response.body) {
                    throw new Error(this.labels?.errorMessage || 'Something went wrong. Please try again.')
                }

                await this.consumeStream(response.body, assistantIndex)
            } catch (err) {
                this.error = err.message || this.labels?.errorMessage || 'Something went wrong. Please try again.'
                this.messages.pop()
            } finally {
                this.sending = false
                this.scrollToBottom()
            }
        },

        // Mutate through this.messages[index], not a captured object reference:
        // a plain object reference captured before Vue wraps it in the reactive
        // array proxy doesn't trigger re-renders on mutation.
        async consumeStream(body, assistantIndex) {
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
                    const message = this.messages[assistantIndex]

                    if (event.type === 'text_delta') {
                        message.pending = false
                        message.content += event.delta
                        this.scrollToBottom()
                    } else if (event.type === 'conversation') {
                        this.conversationId = event.conversation_id
                    } else if (event.type === 'error') {
                        message.pending = false
                        this.error = event.message
                        if (!message.content) this.messages.pop()
                    }
                }
            }
        },

        autoGrow() {
            const el = this.$refs.inputEl
            if (!el) return
            el.style.height = 'auto'
            el.style.height = Math.min(el.scrollHeight, 120) + 'px'
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
