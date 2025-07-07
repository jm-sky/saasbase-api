<!-- InvoiceTemplateEditor.vue -->
<template>
  <div class="invoice-template-editor">
    <div class="container mx-auto px-4 py-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900">{{ $t('invoices.template_editor') }}</h1>
          <div class="flex space-x-4">
            <button
              @click="previewTemplate"
              :disabled="isLoading"
              class="bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white px-4 py-2 rounded flex items-center space-x-2"
            >
              <EyeIcon class="w-4 h-4" />
              <span>{{ $t('common.preview') }}</span>
            </button>
            <button
              @click="saveTemplate"
              :disabled="isLoading || !canSave"
              class="bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white px-4 py-2 rounded flex items-center space-x-2"
            >
              <SaveIcon class="w-4 h-4" />
              <span>{{ $t('common.save') }}</span>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Template Editor Panel -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="space-y-4 mb-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('invoices.template_name') }}
                </label>
                <input
                  v-model="template.name"
                  type="text"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :placeholder="$t('invoices.template_name_placeholder')"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('common.description') }}
                </label>
                <textarea
                  v-model="template.description"
                  rows="2"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :placeholder="$t('invoices.template_description_placeholder')"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('invoices.load_template') }}
                </label>
                <select
                  v-model="selectedTemplateId"
                  @change="loadTemplate"
                  class="w-full border border-gray-300 rounded-md px-3 py-2"
                >
                  <option value="">{{ $t('invoices.select_template') }}</option>
                  <optgroup :label="$t('invoices.system_templates')">
                    <option
                      v-for="tmpl in systemTemplates"
                      :key="tmpl.id"
                      :value="tmpl.id"
                    >
                      {{ tmpl.name }}
                    </option>
                  </optgroup>
                  <optgroup :label="$t('invoices.user_templates')" v-if="userTemplates.length">
                    <option
                      v-for="tmpl in userTemplates"
                      :key="tmpl.id"
                      :value="tmpl.id"
                    >
                      {{ tmpl.name }}
                    </option>
                  </optgroup>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $t('invoices.template_content') }}
              </label>
              <div class="border border-gray-300 rounded-md">
                <textarea
                  v-model="template.content"
                  class="w-full h-96 font-mono text-sm p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                  :placeholder="$t('invoices.template_content_placeholder')"
                />
              </div>
            </div>

            <!-- Helper Functions Reference -->
            <TemplateHelperReference />
          </div>

          <!-- Preview Panel -->
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="mb-4 flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-900">{{ $t('common.preview') }}</h2>
              <div class="flex space-x-2">
                <button
                  @click="showPreviewOptions = !showPreviewOptions"
                  class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded"
                >
                  {{ $t('invoices.preview_options') }}
                </button>
              </div>
            </div>

            <!-- Preview Options -->
            <PreviewOptions
              v-if="showPreviewOptions"
              v-model="previewOptions"
              class="mb-4"
            />

            <!-- Preview Content -->
            <div
              class="border border-gray-300 rounded-md p-4 bg-gray-50 overflow-auto"
              style="height: 600px;"
            >
              <div
                v-if="previewHtml"
                class="bg-white p-4 shadow-sm preview-content"
                v-html="previewHtml"
              />
              <div
                v-else-if="isLoading"
                class="text-center text-gray-500 py-8"
              >
                <LoadingSpinner class="mx-auto mb-2" />
                {{ $t('common.loading') }}
              </div>
              <div
                v-else
                class="text-center text-gray-500 py-8"
              >
                {{ $t('invoices.click_preview') }}
              </div>
            </div>

            <!-- Error Display -->
            <div
              v-if="previewError"
              class="mt-4 bg-red-50 border border-red-200 rounded-md p-4"
            >
              <div class="flex">
                <ExclamationTriangleIcon class="h-5 w-5 text-red-400 mr-2" />
                <div class="text-sm text-red-700">
                  {{ previewError }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Advanced Features Documentation -->
        <AdvancedFeaturesReference class="mt-8" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { EyeIcon, SaveIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import TemplateHelperReference from './TemplateHelperReference.vue'
import PreviewOptions from './PreviewOptions.vue'
import AdvancedFeaturesReference from './AdvancedFeaturesReference.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import { templateApi } from '@/api/templates'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const { showToast } = useToast()

// Reactive data
const template = ref({
  name: '',
  description: '',
  content: '',
  category: 'invoice'
})

const selectedTemplateId = ref('')
const systemTemplates = ref([])
const userTemplates = ref([])
const previewHtml = ref('')
const previewError = ref('')
const isLoading = ref(false)
const showPreviewOptions = ref(false)
const draftTimeout = ref(null)

const previewOptions = ref({
  language: 'en',
  currency: 'USD',
  accentColor: '#3B82F6',
  secondaryColor: '#6B7280',
  includeLogo: true,
  includeSignatures: false,
  dateFormat: 'Y-m-d',
  timezone: 'UTC'
})

// Computed properties
const canSave = computed(() => {
  return template.value.name.trim() && template.value.content.trim()
})

// Methods
const loadTemplates = async () => {
  try {
    const response = await templateApi.getTemplates()
    systemTemplates.value = response.data.system || []
    userTemplates.value = response.data.user || []
  } catch (error) {
    showToast(t('errors.failed_to_load_templates'), 'error')
  }
}

const loadTemplate = async () => {
  if (!selectedTemplateId.value) return

  try {
    isLoading.value = true
    const response = await templateApi.getTemplate(selectedTemplateId.value)
    const templateData = response.data

    template.value = {
      name: templateData.name,
      description: templateData.description || '',
      content: templateData.content,
      category: templateData.category || 'invoice'
    }
  } catch (error) {
    showToast(t('errors.failed_to_load_template'), 'error')
  } finally {
    isLoading.value = false
  }
}

const previewTemplate = async () => {
  if (!template.value.content.trim()) {
    showToast(t('invoices.enter_template_content'), 'warning')
    return
  }

  try {
    isLoading.value = true
    previewError.value = ''

    const response = await templateApi.previewTemplate({
      content: template.value.content,
      options: previewOptions.value,
      previewData: getSampleData()
    })

    previewHtml.value = response.data.html
  } catch (error) {
    previewError.value = error.response?.data?.error || t('errors.preview_failed')
    previewHtml.value = ''
  } finally {
    isLoading.value = false
  }
}

const saveTemplate = async () => {
  if (!canSave.value) return

  try {
    isLoading.value = true

    const templateData = {
      ...template.value,
      previewData: getSampleData()
    }

    const response = await templateApi.saveTemplate(templateData)
    
    showToast(t('invoices.template_saved_successfully'), 'success')
    
    // Refresh template list
    await loadTemplates()
    
    // Clear form or update with saved data
    if (response.data.id) {
      selectedTemplateId.value = response.data.id
    }
  } catch (error) {
    const errorMessage = error.response?.data?.error || t('errors.save_failed')
    showToast(errorMessage, 'error')
  } finally {
    isLoading.value = false
  }
}

const getSampleData = () => {
  return {
    invoice: {
      id: '01HZ123456789',
      number: 'PREVIEW-001',
      type: 'invoice',
      status: 'issued',
      formattedTotalNet: formatCurrency(1000.00, previewOptions.value.currency),
      formattedTotalTax: formatCurrency(230.00, previewOptions.value.currency),
      formattedTotalGross: formatCurrency(1230.00, previewOptions.value.currency),
      currency: previewOptions.value.currency,
      currencySymbol: getCurrencySymbol(previewOptions.value.currency),
      issueDate: '2024-07-06',
      dueDate: '2024-08-05',
      seller: {
        name: 'Example Company Sp. z o.o.',
        address: 'ul. Przykładowa 123, 00-001 Warszawa',
        country: 'Polska',
        taxId: '1234567890',
        email: 'kontakt@example.com',
        logoUrl: previewOptions.value.includeLogo ? '/sample-logo.png' : null
      },
      buyer: {
        name: 'Client Company Ltd.',
        address: '456 Client Street, Warsaw',
        country: 'Poland',
        taxId: '0987654321',
        email: 'client@example.com'
      },
      lines: [
        {
          id: '01HZ123456790',
          description: 'Web Development Services',
          formattedQuantity: '40.00',
          formattedUnitPrice: formatCurrency(20.00, previewOptions.value.currency),
          formattedTotalNet: formatCurrency(800.00, previewOptions.value.currency),
          formattedTotalVat: formatCurrency(184.00, previewOptions.value.currency),
          formattedTotalGross: formatCurrency(984.00, previewOptions.value.currency),
          vatRateName: 'Standard VAT',
          vatRateValue: 23.0
        },
        {
          id: '01HZ123456791',
          description: 'Consulting Services',
          formattedQuantity: '10.00',
          formattedUnitPrice: formatCurrency(20.00, previewOptions.value.currency),
          formattedTotalNet: formatCurrency(200.00, previewOptions.value.currency),
          formattedTotalVat: formatCurrency(46.00, previewOptions.value.currency),
          formattedTotalGross: formatCurrency(246.00, previewOptions.value.currency),
          vatRateName: 'Standard VAT',
          vatRateValue: 23.0
        }
      ],
      vatSummary: [
        {
          vatRateName: 'Standard VAT',
          vatRateValue: 23.0,
          formattedNet: formatCurrency(1000.00, previewOptions.value.currency),
          formattedVat: formatCurrency(230.00, previewOptions.value.currency),
          formattedGross: formatCurrency(1230.00, previewOptions.value.currency)
        }
      ],
      payment: {
        status: 'pending',
        dueDate: '2024-08-05',
        method: 'bank_transfer',
        terms: 'Payment due within 30 days',
        bankAccount: {
          iban: 'PL61109010140000071219812874',
          swift: 'WBKPPLPP',
          bankName: 'Santander Bank Polska'
        }
      }
    },
    options: {
      ...previewOptions.value,
      issuerSignature: previewOptions.value.includeSignatures ? {
        name: 'Jane Smith',
        title: 'Project Manager',
        date: '2024-07-06',
        imageUrl: '/sample-signature.png'
      } : null,
      receiverSignature: previewOptions.value.includeSignatures ? {
        name: 'John Doe',
        title: 'CEO',
        date: null,
        imageUrl: null
      } : null
    }
  }
}

const getCurrencySymbol = (currency) => {
  const symbols = {
    PLN: 'zł',
    USD: '$',
    EUR: '€',
    UAH: '₴',
    RUB: '₽'
  }
  return symbols[currency] || currency
}

const formatCurrency = (amount, currency) => {
  const symbol = getCurrencySymbol(currency)
  const formatted = amount.toFixed(2)
  
  return currency === 'USD' ? `${symbol}${formatted}` : `${formatted} ${symbol}`
}

// Auto-save draft functionality
const saveDraft = () => {
  if (!template.value.content.trim()) return
  
  const draftKey = `template_draft_${Date.now()}`
  localStorage.setItem(draftKey, JSON.stringify({
    ...template.value,
    savedAt: new Date().toISOString()
  }))
  
  // Keep only last 5 drafts
  const drafts = Object.keys(localStorage)
    .filter(key => key.startsWith('template_draft_'))
    .sort()
  
  while (drafts.length > 5) {
    localStorage.removeItem(drafts.shift())
  }
}

const loadDraft = () => {
  const drafts = Object.keys(localStorage)
    .filter(key => key.startsWith('template_draft_'))
    .sort()
    .reverse()
  
  if (drafts.length > 0) {
    const latestDraft = JSON.parse(localStorage.getItem(drafts[0]))
    return latestDraft
  }
  
  return null
}

// Keyboard shortcuts
const handleKeydown = (event) => {
  if (event.ctrlKey || event.metaKey) {
    switch (event.key) {
      case 's':
        event.preventDefault()
        saveTemplate()
        break
      case 'p':
        event.preventDefault()
        previewTemplate()
        break
    }
  }
}

// Watch for changes to auto-save drafts
watch([() => template.value.content, () => template.value.name], () => {
  if (draftTimeout.value) {
    clearTimeout(draftTimeout.value)
  }
  
  draftTimeout.value = setTimeout(() => {
    saveDraft()
  }, 2000)
}, { deep: true })

// Watch for preview option changes to auto-update preview
watch(previewOptions, () => {
  if (previewHtml.value) {
    previewTemplate()
  }
}, { deep: true })

// Lifecycle
onMounted(() => {
  loadTemplates()
  
  // Load default app settings
  previewOptions.value.language = window.AppConfig?.locale || 'en'
  previewOptions.value.timezone = window.AppConfig?.timezone || 'UTC'
  
  // Check for unsaved drafts
  const draft = loadDraft()
  if (draft && draft.content.trim()) {
    const shouldLoad = confirm(t('invoices.load_unsaved_draft'))
    if (shouldLoad) {
      template.value = {
        name: draft.name || '',
        description: draft.description || '',
        content: draft.content,
        category: draft.category || 'invoice'
      }
    }
  }
  
  // Add keyboard shortcuts
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  if (draftTimeout.value) {
    clearTimeout(draftTimeout.value)
  }
})
</script>

<style scoped>
.invoice-template-editor {
  min-height: 100vh;
  background-color: #f9fafb;
}

/* Custom scrollbar for preview */
.overflow-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.overflow-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.overflow-auto::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

/* Code highlighting in helper reference */
code {
  background-color: #f3f4f6;
  padding: 2px 4px;
  border-radius: 3px;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.875em;
}

/* Template editor syntax highlighting */
textarea.font-mono {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  line-height: 1.5;
}

/* Animation for preview loading */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.preview-content {
  animation: fadeIn 0.3s ease-in-out;
}
</style>

<!-- PreviewOptions.vue -->
<template>
  <div class="preview-options bg-gray-50 p-4 rounded-lg border">
    <h3 class="text-sm font-medium text-gray-900 mb-3">{{ $t('invoices.preview_options') }}</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
          {{ $t('invoices.language') }}
        </label>
        <select
          v-model="localOptions.language"
          class="w-full text-sm border border-gray-300 rounded px-2 py-1"
        >
          <option value="en">English</option>
          <option value="pl">Polski</option>
          <option value="uk">Українська</option>
          <option value="ru">Русский</option>
        </select>
      </div>
      
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
          {{ $t('invoices.currency') }}
        </label>
        <select
          v-model="localOptions.currency"
          class="w-full text-sm border border-gray-300 rounded px-2 py-1"
        >
          <option value="PLN">PLN (zł)</option>
          <option value="USD">USD ($)</option>
          <option value="EUR">EUR (€)</option>
          <option value="UAH">UAH (₴)</option>
          <option value="RUB">RUB (₽)</option>
        </select>
      </div>
      
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
          {{ $t('invoices.date_format') }}
        </label>
        <select
          v-model="localOptions.dateFormat"
          class="w-full text-sm border border-gray-300 rounded px-2 py-1"
        >
          <option value="Y-m-d">2024-07-06</option>
          <option value="d/m/Y">06/07/2024</option>
          <option value="m/d/Y">07/06/2024</option>
          <option value="d.m.Y">06.07.2024</option>
          <option value="F j, Y">July 6, 2024</option>
        </select>
      </div>
    </div>
    
    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
          {{ $t('invoices.accent_color') }}
        </label>
        <div class="flex space-x-2">
          <input
            v-model="localOptions.accentColor"
            type="color"
            class="w-8 h-8 border border-gray-300 rounded cursor-pointer"
          />
          <input
            v-model="localOptions.accentColor"
            type="text"
            class="flex-1 text-sm border border-gray-300 rounded px-2 py-1"
          />
        </div>
      </div>
      
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
          {{ $t('invoices.secondary_color') }}
        </label>
        <div class="flex space-x-2">
          <input
            v-model="localOptions.secondaryColor"
            type="color"
            class="w-8 h-8 border border-gray-300 rounded cursor-pointer"
          />
          <input
            v-model="localOptions.secondaryColor"
            type="text"
            class="flex-1 text-sm border border-gray-300 rounded px-2 py-1"
          />
        </div>
      </div>
    </div>
    
    <div class="mt-4">
      <h4 class="text-xs font-medium text-gray-700 mb-2">{{ $t('invoices.color_schemes') }}</h4>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="scheme in colorSchemes"
          :key="scheme.name"
          @click="applyColorScheme(scheme)"
          class="flex items-center space-x-2 px-3 py-1 border border-gray-300 rounded text-xs hover:bg-gray-50"
        >
          <div
            class="w-3 h-3 rounded"
            :style="{ backgroundColor: scheme.accent }"
          />
          <span>{{ scheme.name }}</span>
        </button>
      </div>
    </div>
    
    <div class="mt-4 flex space-x-4">
      <label class="flex items-center text-sm">
        <input
          v-model="localOptions.includeLogo"
          type="checkbox"
          class="mr-2"
        />
        {{ $t('invoices.include_logo') }}
      </label>
      
      <label class="flex items-center text-sm">
        <input
          v-model="localOptions.includeSignatures"
          type="checkbox"
          class="mr-2"
        />
        {{ $t('invoices.include_signatures') }}
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const localOptions = ref({ ...props.modelValue })

const colorSchemes = ref([
  { name: 'Blue', accent: '#3B82F6', secondary: '#6B7280' },
  { name: 'Green', accent: '#10B981', secondary: '#6B7280' },
  { name: 'Purple', accent: '#8B5CF6', secondary: '#6B7280' },
  { name: 'Red', accent: '#EF4444', secondary: '#6B7280' },
  { name: 'Orange', accent: '#F97316', secondary: '#6B7280' },
  { name: 'Teal', accent: '#14B8A6', secondary: '#6B7280' },
  { name: 'Gray', accent: '#374151', secondary: '#9CA3AF' }
])

const applyColorScheme = (scheme) => {
  localOptions.value.accentColor = scheme.accent
  localOptions.value.secondaryColor = scheme.secondary
}

watch(localOptions, (newValue) => {
  emit('update:modelValue', { ...newValue })
}, { deep: true })

watch(() => props.modelValue, (newValue) => {
  localOptions.value = { ...newValue }
}, { deep: true })
</script>

<!-- TemplateHelperReference.vue -->
<template>
  <div class="template-helper-reference bg-gray-50 p-4 rounded-md">
    <h3 class="font-medium text-gray-900 mb-2">{{ $t('invoices.available_helpers') }}</h3>
    <div class="text-sm text-gray-600 space-y-1">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <h4 class="font-medium text-gray-800 mb-1">{{ $t('invoices.translations') }}</h4>
          <p><code>{{'{{'}}t "invoices.invoice"{{'}}'}}</code> - {{ $t('invoices.translatable_text') }}</p>
          <p><code>{{'{{'}}t "invoices.from"{{'}}'}}</code> - {{ $t('invoices.from_label') }}</p>
          <p><code>{{'{{'}}t "invoices.total"{{'}}'}}</code> - {{ $t('invoices.total_label') }}</p>
        </div>
        
        <div>
          <h4 class="font-medium text-gray-800 mb-1">{{ $t('invoices.invoice_data') }}</h4>
          <p><code>{{'{{invoice.number}}'}}</code> - {{ $t('invoices.invoice_number') }}</p>
          <p><code>{{'{{invoice.seller.name}}'}}</code> - {{ $t('invoices.seller_name') }}</p>
          <p><code>{{'{{invoice.formattedTotalGross}}'}}</code> - {{ $t('invoices.formatted_total') }}</p>
        </div>
        
        <div>
          <h4 class="font-medium text-gray-800 mb-1">{{ $t('invoices.conditionals') }}</h4>
          <p><code>{{'{{'}}#if invoice.payment{{'}}'}}</code> - {{ $t('invoices.conditional_blocks') }}</p>
          <p><code>{{'{{'}}#each invoice.lines{{'}}'}}</code> - {{ $t('invoices.loop_arrays') }}</p>
        </div>
        
        <div>
          <h4 class="font-medium text-gray-800 mb-1">{{ $t('invoices.images') }}</h4>
          <p><code>{{'{{{logoUrl invoice.seller.logoUrl}}}'}}</code> - {{ $t('invoices.display_logo') }}</p>
          <p><code>{{'{{{signatureUrl signature}}}'}}</code> - {{ $t('invoices.display_signature') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<!-- AdvancedFeaturesReference.vue -->
<template>
  <div class="advanced-features bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $t('invoices.advanced_features') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
      <div>
        <h3 class="font-medium text-gray-900 mb-2">{{ $t('invoices.multilingual') }}</h3>
        <div class="space-y-1 text-gray-600">
          <p><code>{{'{{'}}t "invoices.invoice"{{'}}'}}</code> - {{ $t('invoices.translatable_text') }}</p>
          <p><code>{{'{{'}}t "invoices.from"{{'}}'}}</code> - {{ $t('invoices.from_label') }}</p>
          <p><code>{{'{{'}}t "invoices.to"{{'}}'}}</code> - {{ $t('invoices.to_label') }}</p>
          <p><code>{{'{{'}}t "invoices.total"{{'}}'}}</code> - {{ $t('invoices.total_label') }}</p>
        </div>
      </div>
      
      <div>
        <h3 class="font-medium text-gray-900 mb-2">{{ $t('invoices.images_media') }}</h3>
        <div class="space-y-1 text-gray-600">
          <p><code>{{'{{'}}#if invoice.seller.logoUrl{{'}}'}}</code> - {{ $t('invoices.check_logo_exists') }}</p>
          <p><code>{{'{{{logoUrl invoice.seller.logoUrl width="180px"}}}'}}</code> - {{ $t('invoices.display_logo') }}</p>
          <p><code>{{'{{{signatureUrl signature}}}'}}</code> - {{ $t('invoices.display_signature') }}</p>
        </div>
      </div>
      
      <div>
        <h3 class="font-medium text-gray-900 mb-2">{{ $t('invoices.custom_colors') }}</h3>
        <div class="space-y-1 text-gray-600">
          <p><code>accent-bg</code> - {{ $t('invoices.accent_background') }}</p>
          <p><code>accent-text</code> - {{ $t('invoices.accent_text_color') }}</p>
          <p><code>accent-border</code> - {{ $t('invoices.accent_border') }}</p>
          <p><code>secondary-text</code> - {{ $t('invoices.secondary_text') }}</p>
        </div>
      </div>
      
      <div>
        <h3 class="font-medium text-gray-900 mb-2">{{ $t('invoices.invoice_structure') }}</h3>
        <div class="space-y-1 text-gray-600">
          <p><code>{{'{{invoice.number}}'}}</code> - {{ $t('invoices.invoice_number') }}</p>
          <p><code>{{'{{invoice.seller.name}}'}}</code> - {{ $t('invoices.seller_name') }}</p>
          <p><code>{{'{{invoice.buyer.name}}'}}</code> - {{ $t('invoices.buyer_name') }}</p>
          <p><code>{{'{{invoice.formattedTotalGross}}'}}</code> - {{ $t('invoices.formatted_total') }}</p>
        </div>
      </div>
      
      <div>
        <h3 class="font-medium text-gray-900 mb-2">{{ $t('invoices.line_items') }}</h3>
        <div class="space-y-1 text-gray-600">
          <p><code>{{'{{'}}#each invoice.lines{{'}}'}}</code> - {{ $t('invoices.loop_line_items') }}</p>
          <p><code>{{'{{description}}'}}</code> - {{ $t('invoices.item_description') }}</p>
          <p><code>{{'{{formattedTotalNet}}'}}</code> - {{ $t('invoices.formatted_net_total') }}</p>
          <p><code>{{'{{'}}#each invoice.vatSummary{{'}}'}}</code> - {{ $t('invoices.vat_summary_loop') }}</p>
        </div>
      </div>
      
      <div>
        <h3 class="font-medium text-gray-900 mb-2">{{ $t('invoices.signatures_section') }}</h3>
        <div class="space-y-1 text-gray-600">
          <p><code>{{'{{'}}#if options.includeSignatures{{'}}'}}</code> - {{ $t('invoices.signature_check') }}</p>
          <p><code>{{'{{options.issuerSignature.name}}'}}</code> - {{ $t('invoices.issuer_name') }}</p>
          <p><code>{{'{{options.receiverSignature.name}}'}}</code> - {{ $t('invoices.receiver_name') }}</p>
        </div>
      </div>
    </div>
    
    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
      <h4 class="font-medium text-blue-900 mb-2">{{ $t('invoices.important_notes') }}</h4>
      <ul class="text-sm text-blue-800 space-y-1">
        <li>• {{ $t('invoices.note_calculations') }}</li>
        <li>• {{ $t('invoices.note_media_urls') }}</li>
        <li>• {{ $t('invoices.note_tenant_isolation') }}</li>
        <li>• {{ $t('invoices.note_page_numbering') }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<!-- LoadingSpinner.vue -->
<template>
  <div class="loading-spinner">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
  </div>
</template>

<script setup>
// Simple loading spinner component
</script>

<style scoped>
.loading-spinner {
  display: inline-block;
}
</style>
