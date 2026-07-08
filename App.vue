<template>
    <div style="height: 480px;width: 360px;" class="border overflow-auto border-b-gray-500 p-2 m-20">
        <Chatbot
            :labels="{
                'inputPlaceholder': 'Type your message...',
                'send': 'Send',
                'errorMessage': 'Something went wrong. Please try again.'
            }"
            :routes="{
                'send': 'http://chatbot.test/volet/chatbot/messages',
            }"
            token="dev-csrf-token"
        />
    </div>
</template>

<script>
import Chatbot from "./resources/js/components/Chatbot.ce.vue";

export default {
    components: {
        Chatbot
    }
}
</script>
