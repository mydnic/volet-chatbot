import { defineCustomElement } from 'vue'
import Chatbot from './components/Chatbot.ce.vue'

const Element = defineCustomElement(Chatbot)

customElements.define('volet-chatbot', Element)
